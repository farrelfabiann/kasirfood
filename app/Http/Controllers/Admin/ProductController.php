<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // 1. Tampilkan Semua Produk
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        $categories = Category::all();
        return view('admin.products.index', compact('products', 'categories'));
    }

    // Pada method store:
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        }

        // Olah input options dari form admin
        $additionalOptions = [];
        if ($request->has('options_json') && !empty($request->options_json)) {
            $additionalOptions = json_decode($request->options_json, true) ?? [];
        }

        Product::create([
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'price'              => $request->price,
            'description'        => $request->description,
            'image'              => $imagePath ?? 'https://via.placeholder.com/150',
            'is_available'       => $request->has('is_available'),
            'additional_options' => $additionalOptions,
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
    }

    // Pada method update:
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && str_contains($product->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('products', 'public');
            $product->image = '/storage/' . $path;
        }

        $additionalOptions = [];
        if ($request->has('options_json') && !empty($request->options_json)) {
            $additionalOptions = json_decode($request->options_json, true) ?? [];
        }

        $product->update([
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'price'              => $request->price,
            'description'        => $request->description,
            'is_available'       => $request->has('is_available'),
            'additional_options' => $additionalOptions,
        ]);

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    // 4. Hapus Produk
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && str_contains($product->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }

        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus!');
    }
}