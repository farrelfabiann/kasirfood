<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'revenue_today' => Order::whereDate('created_at', $today)->where('status', 'paid')->sum('total_amount'),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'available_products' => Product::where('is_available', true)->count(),
        ];

        $recentOrders = Order::with('items')->latest()->take(8)->get();

        $topProducts = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as total_quantity'))
            ->whereHas('order', fn ($query) => $query->where('status', 'paid')->whereDate('created_at', $today))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'topProducts'));
    }
}
