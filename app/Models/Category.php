<?php

namespace App\Models;

use App\Support\MenuIcon;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'icon_image',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Ikon yang dipakai kiosk: pilihan admin, atau tebakan dari nama.
     */
    public function getIconKeyAttribute(): string
    {
        return $this->icon ?: MenuIcon::forCategory($this->name);
    }

    /**
     * URL gambar ikon yang diunggah admin (null jika tidak ada).
     *
     * Memakai asset() agar mengikuti base URL request (mis. saat aplikasi
     * dijalankan di subfolder XAMPP), bukan APP_URL mentah.
     */
    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_image ? asset('storage/'.$this->icon_image) : null;
    }
}
