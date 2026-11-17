<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if ($status = $request->query('status')) {
            if (in_array($status, ['pending', 'paid', 'cancelled'], true)) {
                $query->where('status', $status);
            }
        }

        if ($type = $request->query('type')) {
            if (in_array($type, ['dine_in', 'online'], true)) {
                $query->where('order_type', $type);
            }
        }

        if ($payment = $request->query('payment')) {
            if (in_array($payment, ['qris', 'edc', 'cash'], true)) {
                $query->where('payment_method', $payment);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'revenue_today' => Order::whereDate('created_at', now()->toDateString())
                ->where('status', 'paid')
                ->sum('total_amount'),
            'orders_today' => Order::whereDate('created_at', now()->toDateString())->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'cash_pending' => Order::where('status', 'pending')
                ->whereIn('payment_method', ['cash', 'edc'])
                ->count(),
        ];

        if ($request->boolean('partial')) {
            return response()->view('admin.orders._list', compact('orders', 'stats'));
        }

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function confirm(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', "Pesanan {$order->order_number} tidak dalam status menunggu pembayaran.");
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', "Pembayaran {$order->order_number} berhasil dikonfirmasi.");
    }
}
