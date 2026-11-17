<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membersihkan kategori/produk duplikat dari seed lama sebelum
     * menambahkan unique index pada nama kategori.
     */
    public function up(): void
    {
        $duplicateCategories = DB::table('categories')
            ->select('name', DB::raw('MIN(id) as keep_id'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateCategories as $duplicate) {
            $duplicateIds = DB::table('categories')
                ->where('name', $duplicate->name)
                ->where('id', '!=', $duplicate->keep_id)
                ->pluck('id');

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            DB::table('products')
                ->whereIn('category_id', $duplicateIds)
                ->update(['category_id' => $duplicate->keep_id]);

            DB::table('categories')->whereIn('id', $duplicateIds)->delete();
        }

        $duplicateProducts = DB::table('products')
            ->select('name', 'category_id', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('category_id')
            ->groupBy('name', 'category_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateProducts as $duplicate) {
            $duplicateIds = DB::table('products')
                ->where('name', $duplicate->name)
                ->where('category_id', $duplicate->category_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->pluck('id');

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            DB::table('order_items')
                ->whereIn('product_id', $duplicateIds)
                ->update(['product_id' => $duplicate->keep_id]);

            DB::table('products')->whereIn('id', $duplicateIds)->delete();
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
