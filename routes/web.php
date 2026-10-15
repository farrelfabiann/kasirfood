<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;

// Halaman Utama Kasir
Route::get('/', [OrderController::class, 'index'])->name('kasir.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Route Kasir & Checkout
Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::get('/order-history', [OrderController::class, 'history'])->name('orders.history');
Route::post('/cancel-order', [OrderController::class, 'cancelOrder'])->name('cancel.order');
Route::post('/midtrans/notification', [OrderController::class, 'notificationHandler']);

// Route Admin (Khusus kelola produk)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
});