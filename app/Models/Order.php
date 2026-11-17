<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'order_type',
        'table_number',
        'total_amount',
        'payment_method',
        'payment_reference',
        'payment_url',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getOrderTypeLabelAttribute(): string
    {
        return $this->order_type === 'dine_in' ? 'Dine In' : 'Take Away';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai (Kasir)',
            'edc' => 'EDC Kiosk (Kartu)',
            default => 'DOKU (Online)',
        };
    }

    /**
     * Apakah pembayaran diselesaikan langsung di kiosk (tanpa gateway).
     */
    public function getIsManualPaymentAttribute(): bool
    {
        return in_array($this->payment_method, ['cash', 'edc'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => 'Menunggu Pembayaran',
        };
    }

    public function getQueueNumberAttribute(): string
    {
        return Str::afterLast($this->order_number, '-');
    }
}
