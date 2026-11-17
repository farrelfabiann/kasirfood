<?php

namespace App\Support;

use Illuminate\Support\Str;

class MenuIcon
{
    /**
     * Ikon yang boleh dipilih admin untuk kategori.
     *
     * @var array<string, string>
     */
    public const OPTIONS = [
        'promo' => 'Promo',
        'tag' => 'Label',
        'star' => 'Terlaris',
        'fire' => 'Pedas / Camilan',
        'sparkles' => 'Spesial',
        'coffee' => 'Kopi',
        'cup' => 'Minuman',
        'cake' => 'Dessert',
        'utensils' => 'Makanan',
        'bag' => 'Take Away',
        'shopping-bag' => 'Belanja',
        'receipt' => 'Struk',
        'banknotes' => 'Tunai',
        'credit-card' => 'Kartu',
        'clock' => 'Waktu',
        'grid' => 'Umum',
    ];

    /**
     * Daftar ikon untuk pemilih ikon kategori.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::OPTIONS;
    }

    /**
     * Memilih ikon sidebar berdasarkan nama kategori.
     */
    public static function forCategory(?string $name): string
    {
        $name = Str::lower(trim((string) $name));

        return match (true) {
            Str::contains($name, ['kopi', 'coffee', 'latte']) => 'coffee',
            Str::contains($name, ['minum', 'drink', 'jus', 'teh', 'soda', 'air']) => 'cup',
            Str::contains($name, ['dessert', 'kue', 'cake', 'roti', 'pastry', 'manis']) => 'cake',
            Str::contains($name, ['camilan', 'snack', 'gorengan', 'topping']) => 'fire',
            Str::contains($name, ['promo', 'diskon', 'paket', 'hemat']) => 'promo',
            Str::contains($name, ['makan', 'food', 'nasi', 'mie', 'ayam', 'soto', 'bakso', 'burger', 'pasta']) => 'utensils',
            default => 'grid',
        };
    }
}
