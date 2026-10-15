<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->enum('order_type', ['dine_in', 'online']); // Tipe pesanan
            $table->string('table_number')->nullable(); // Nomor meja jika dine-in
            $table->integer('total_amount');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending'); // Status bayar[cite: 1]
            $table->string('snap_token')->nullable(); // Token dari Midtrans[cite: 1]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
