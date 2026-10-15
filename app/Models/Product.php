<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'additional_options',
    ];

    protected $casts = [
        'additional_options' => 'array',
    ];

    // Relasi ke Model Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}