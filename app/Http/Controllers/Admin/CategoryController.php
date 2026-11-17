<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\MenuIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $iconOptions = MenuIcon::options();

        return view('admin.categories.index', compact('categories', 'iconOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $category = Category::create([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? MenuIcon::forCategory($validated['name']),
            'icon_image' => $this->storeIconImage($request),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori berhasil ditambahkan.',
                'category' => $this->categoryPayload($category),
            ], 201);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate($this->rules($category));

        $payload = [
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? $category->icon,
        ];

        if ($request->hasFile('icon_image')) {
            $this->deleteIconImage($category);
            $payload['icon_image'] = $this->storeIconImage($request);
        } elseif ($request->boolean('remove_icon_image')) {
            $this->deleteIconImage($category);
            $payload['icon_image'] = null;
        }

        $category->update($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori berhasil diperbarui.',
                'category' => $this->categoryPayload($category->fresh()),
            ]);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $productCount = $category->products()->count();

        $category->products()->update(['category_id' => null]);

        $this->deleteIconImage($category);
        $category->delete();

        $message = $productCount > 0
            ? "Kategori berhasil dihapus. {$productCount} menu dipindahkan ke Tanpa kategori."
            : 'Kategori berhasil dihapus.';

        return redirect()
            ->route('admin.categories.index')
            ->with('success', $message);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Category $category = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categories', 'name')->ignore($category?->id),
            ],
            'icon' => ['nullable', 'string', Rule::in(array_keys(MenuIcon::options()))],
            'icon_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:1024'],
            'remove_icon_image' => ['nullable', 'boolean'],
        ];
    }

    private function storeIconImage(Request $request): ?string
    {
        return $request->hasFile('icon_image')
            ? $request->file('icon_image')->store('categories', 'public')
            : null;
    }

    private function deleteIconImage(Category $category): void
    {
        if ($category->icon_image) {
            Storage::disk('public')->delete($category->icon_image);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryPayload(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'icon' => $category->icon,
            'icon_key' => $category->icon_key,
            'icon_url' => $category->icon_url,
        ];
    }
}
