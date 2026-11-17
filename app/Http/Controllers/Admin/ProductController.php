<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->query('category'));
        }

        if ($request->query('availability') === 'available') {
            $query->where('is_available', true);
        } elseif ($request->query('availability') === 'unavailable') {
            $query->where('is_available', false);
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->merge(['category_id' => $request->input('category_id') ?: null]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'options_json' => ['nullable', 'string', 'max:20000'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = '/storage/'.$request->file('image')->store('products', 'public');
        }

        Product::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'price' => (int) $validated['price'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_available' => $request->boolean('is_available'),
            'additional_options' => $this->parseOptions($validated['options_json'] ?? null),
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->merge(['category_id' => $request->input('category_id') ?: null]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'options_json' => ['nullable', 'string', 'max:20000'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'price' => (int) $validated['price'],
            'description' => $validated['description'] ?? null,
            'is_available' => $request->boolean('is_available'),
            'additional_options' => $this->parseOptions($validated['options_json'] ?? null),
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($product->image);
            $payload['image'] = '/storage/'.$request->file('image')->store('products', 'public');
        }

        $product->update($payload);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Menu dipindahkan ke daftar terhapus. Riwayat pesanan tetap aman.');
    }

    public function toggleAvailability(Product $product)
    {
        $product->update(['is_available' => ! $product->is_available]);

        return back()->with('success', "Status {$product->name} diubah menjadi ".($product->is_available ? 'tersedia' : 'habis').'.');
    }

    private function deleteStoredImage(?string $image): void
    {
        if ($image && str_starts_with($image, '/storage/')) {
            Storage::disk('public')->delete(Str::after($image, '/storage/'));
        }
    }

    /**
     * Memvalidasi dan menormalkan JSON builder opsi tambahan.
     */
    private function parseOptions(?string $json): array
    {
        if (! $json || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw ValidationException::withMessages([
                'options_json' => 'Format opsi tambahan tidak valid.',
            ]);
        }

        if (count($decoded) > 10) {
            throw ValidationException::withMessages([
                'options_json' => 'Maksimal 10 grup opsi tambahan.',
            ]);
        }

        $groups = [];
        $groupNames = [];

        foreach ($decoded as $group) {
            if (! is_array($group)) {
                throw ValidationException::withMessages([
                    'options_json' => 'Format grup opsi tidak valid.',
                ]);
            }

            $groupName = trim((string) ($group['groupName'] ?? ''));

            if ($groupName === '') {
                throw ValidationException::withMessages([
                    'options_json' => 'Nama grup opsi wajib diisi.',
                ]);
            }

            if (mb_strlen($groupName) > 80) {
                throw ValidationException::withMessages([
                    'options_json' => 'Nama grup opsi maksimal 80 karakter.',
                ]);
            }

            $normalizedGroupName = mb_strtolower($groupName);
            if (in_array($normalizedGroupName, $groupNames, true)) {
                throw ValidationException::withMessages([
                    'options_json' => "Nama grup opsi \"{$groupName}\" duplikat.",
                ]);
            }
            $groupNames[] = $normalizedGroupName;

            $type = ($group['type'] ?? 'radio') === 'checkbox' ? 'checkbox' : 'radio';
            $options = [];
            $optionNames = [];

            foreach (($group['options'] ?? []) as $option) {
                if (! is_array($option)) {
                    continue;
                }

                $optionName = trim((string) ($option['name'] ?? ''));
                if ($optionName === '') {
                    continue;
                }

                if (mb_strlen($optionName) > 80) {
                    throw ValidationException::withMessages([
                        'options_json' => 'Nama pilihan opsi maksimal 80 karakter.',
                    ]);
                }

                $normalizedOptionName = mb_strtolower($optionName);
                if (in_array($normalizedOptionName, $optionNames, true)) {
                    throw ValidationException::withMessages([
                        'options_json' => "Pilihan \"{$optionName}\" duplikat pada grup {$groupName}.",
                    ]);
                }
                $optionNames[] = $normalizedOptionName;

                $price = (int) ($option['price'] ?? 0);
                if ($price < 0 || $price > 10000000) {
                    throw ValidationException::withMessages([
                        'options_json' => 'Harga opsi harus antara 0 dan 10.000.000.',
                    ]);
                }

                $options[] = [
                    'name' => $optionName,
                    'price' => $price,
                ];
            }

            if ($options === []) {
                throw ValidationException::withMessages([
                    'options_json' => "Grup opsi \"{$groupName}\" minimal memiliki satu pilihan.",
                ]);
            }

            if (count($options) > 20) {
                throw ValidationException::withMessages([
                    'options_json' => "Grup opsi \"{$groupName}\" maksimal 20 pilihan.",
                ]);
            }

            $groups[] = [
                'groupName' => $groupName,
                'type' => $type,
                'options' => $options,
            ];
        }

        return $groups;
    }
}
