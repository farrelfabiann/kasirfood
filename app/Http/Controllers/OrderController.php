<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    // 1. Menampilkan halaman kasir dengan data produk
    public function index()
    {
        $products = Product::where('is_available', true)->get();
        return view('kasir', compact('products'));
    }

    // 2. Checkout & Pembuatan Snap Token Midtrans / Cash
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'order_type'     => 'required|string',
            'total_amount'   => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        $status = ($request->payment_method === 'cash') ? 'paid' : 'pending';

        try {
            // Buat record order di database
            $order = Order::create([
                'order_number'  => 'ORD-' . time() . '-' . rand(100, 999),
                'customer_name' => $request->customer_name,
                'order_type'    => $request->order_type,
                'table_number'  => $request->table_number ?? null,
                'total_amount'  => (int) $request->total_amount,
                'status'        => $status,
            ]);

            // Jika pembayaran TUNAI (CASH), langsung sukses tanpa Snap Token
            if ($request->payment_method === 'cash') {
                return response()->json([
                    'status'  => 'success',
                    'payment' => 'cash',
                    'message' => 'Pembayaran Tunai Berhasil Diproses!',
                    'order'   => $order
                ]);
            }

            // Jika pembayaran QRIS / MIDTRANS
            $serverKey = config('services.midtrans.server_key');
            if (empty($serverKey)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'MIDTRANS_SERVER_KEY belum diisi di file .env atau config/services.php!'
                ], 500);
            }

            Config::$serverKey = $serverKey;
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id'     => $order->order_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);

            return response()->json([
                'status'     => 'success',
                'payment'    => 'qris',
                'snap_token' => $snapToken,
                'order'      => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Checkout Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. API Riwayat Transaksi & Omset Harian
    public function history()
    {
        $today = Carbon::today();

        // Total omset hari ini (khusus status paid)
        $todayIncome = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Jumlah transaksi lunas & pending hari ini
        $paidCount = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->count();

        $pendingCount = Order::whereDate('created_at', $today)
            ->where('status', 'pending')
            ->count();

        // 50 transaksi terbaru
        $orders = Order::orderBy('created_at', 'desc')->take(50)->get();

        return response()->json([
            'today_income'  => $todayIncome,
            'paid_count'    => $paidCount,
            'pending_count' => $pendingCount,
            'orders'        => $orders
        ]);
    }

    // 4. Pembatalan Transaksi Langsung ke Server Midtrans & Database
    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->order_number)->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        $midtransStatus = 'not_found';

        try {
            $response = Transaction::expire($order->order_number);
            $midtransStatus = 'expired';
        } catch (\Exception $e) {
            try {
                $response = Transaction::cancel($order->order_number);
                $midtransStatus = 'cancelled';
            } catch (\Exception $ex) {
                Log::warning("Midtrans Cancel/Expire Warning for {$order->order_number}: " . $ex->getMessage());
            }
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'status'          => 'success',
            'message'         => 'Transaksi dibatalkan',
            'midtrans_status' => $midtransStatus
        ]);
    }

    // 5. Webhook Callback Notification dari Midtrans
    public function notificationHandler(Request $request)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        try {
            $notif = new Notification();
            $transactionStatus = $notif->transaction_status;
            $orderId = $notif->order_id;

            $order = Order::where('order_number', $orderId)->first();
            if ($order) {
                if (in_array($transactionStatus, ['settlement', 'capture'])) {
                    $order->update(['status' => 'paid']);
                } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $order->update(['status' => 'cancelled']);
                }
            }

            return response()->json(['message' => 'Notification processed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}