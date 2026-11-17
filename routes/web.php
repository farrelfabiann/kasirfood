<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Kiosk pemesanan mandiri (publik, tanpa login).
Route::get('/', [OrderController::class, 'index'])->name('kiosk.index');
Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/cancel-order', [OrderController::class, 'cancelOrder'])->name('cancel.order');

// Webhook DOKU (CSRF dikecualikan di bootstrap/app.php).
Route::post('/doku/notification', [OrderController::class, 'dokuNotification']);

// Halaman selesai pembayaran DOKU (redirect dari DOKU Checkout).
Route::get('/payment/finish', [OrderController::class, 'paymentFinish'])->name('payment.finish');
Route::get('/payment/status', [OrderController::class, 'paymentStatus'])->name('payment.status');

// Halaman status pesanan (dipakai sebagai callback_url DOKU).
Route::get('/order/{order_number}', [OrderController::class, 'orderStatus'])->name('order.status');
Route::get('/order/{order_number}/status', [OrderController::class, 'orderStatusJson']);
Route::post('/order/{order_number}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
Route::post('/order/{order_number}/edc/simulate', [OrderController::class, 'simulateEdcPayment'])->name('order.edc.simulate');

Route::get('/dashboard', function () {
    return auth()->user()?->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('kiosk.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Area admin: wajib login dan role admin.
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::patch('/products/{product}/availability', [AdminProductController::class, 'toggleAvailability'])
        ->name('products.availability');

    Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
});
