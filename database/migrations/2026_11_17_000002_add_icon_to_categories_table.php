<?php

use App\Support\MenuIcon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon', 40)->nullable()->after('name');
            $table->string('icon_image')->nullable()->after('icon');
        });

        // Isi ikon kategori lama berdasarkan namanya; admin bisa mengubahnya.
        DB::table('categories')->select('id', 'name')->orderBy('id')->each(function ($category) {
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['icon' => MenuIcon::forCategory($category->name)]);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'icon_image']);
        });
    }
};
