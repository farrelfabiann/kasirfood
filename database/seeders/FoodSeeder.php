<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori
        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);

        // 2. Buat Produk Makanan Contoh
        Product::create([
            'category_id' => $makanan->id,
            'name' => 'Nasi Goreng Spesial',
            'description' => 'Nasi goreng dengan telur, ayam suwir, dan kerupuk.',
            'price' => 20000,
            'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?q=80&w=600',
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $makanan->id,
            'name' => 'Mie Goreng Seafood',
            'description' => 'Mie goreng dengan udang dan bakso ikan.',
            'price' => 25000,
            'image' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?q=80&w=600',
            'is_available' => true,
        ]);

        // 3. Buat Produk Minuman Contoh
        Product::create([
            'category_id' => $minuman->id,
            'name' => 'Es Teh Manis',
            'description' => 'Es teh manis segar.',
            'price' => 5000,
            'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?q=80&w=600',
            'is_available' => true,
        ]);
    }
}