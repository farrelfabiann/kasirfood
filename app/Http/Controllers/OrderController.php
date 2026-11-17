<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\DokuCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    public function __construct(private DokuCheckout $doku)
    {
    }
    /**
     * Kiosk pemesanan mandiri untuk pelanggan.
     */
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::withCount('products')
            ->has('products')
            ->orderBy('name')
            ->get();

        $kiosk = [
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon_key,
                'icon_url' => $category->icon_url,
                'product_count' => $category->products_count,
            ])->values()->all(),
            'products' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (int) $product->price,
                'image_url' => $product->image_url,
                'is_available' => (bool) $product->is_available,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'options' => $this->displayOptions($product->additional_options),
            ])->values()->all(),
            'payment' => [
                'qris_enabled' => $this->doku->isConfigured(),
                'provider' => 'DOKU',
                'checkout_url' => route('checkout'),
            ],
            'app' => [
                'name' => config('app.frontend_name', 'KlikKluk'),
            ],
        ];

        return view('kasir', compact('kiosk'));
    }

    /**
     * Membuat pesanan. Total selalu dihitung ulang di server.
     */
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:80'],
            'order_type' => ['required', Rule::in(['dine_in', 'take_away', 'online'])],
            'payment_method' => ['required', Rule::in(['qris', 'cash', 'edc'])],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.note' => ['nullable', 'string', 'max:120'],
            'items.*.options' => ['nullable', 'array', 'max:20'],
            'items.*.options.*.group' => ['required', 'string', 'max:80'],
            'items.*.options.*.name' => ['required', 'string', 'max:80'],
            'items.*.options.*.price' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['payment_method'] === 'qris' && ! $this->doku->isConfigured()) {
            return response()->json([
                'message' => 'Pembayaran QRIS belum dikonfigurasi. Silakan pilih kartu EDC atau bayar tunai di kasir.',
            ], 422);
        }

        $orderType = $data['order_type'] === 'dine_in' ? 'dine_in' : 'online';
        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $lineItems = [];
        $totalAmount = 0;

        foreach ($data['items'] as $line) {
            $product = $products->get($line['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu menu tidak ditemukan. Silakan muat ulang halaman.',
                ]);
            }

            if (! $product->is_available) {
                throw ValidationException::withMessages([
                    'items' => "Menu {$product->name} sedang habis. Silakan pilih menu lain.",
                ]);
            }

            $options = $this->normalizeSelectedOptions($product, $line['options'] ?? []);
            $optionTotal = collect($options)->sum('price');
            $unitPrice = (int) $product->price + (int) $optionTotal;
            $quantity = (int) $line['quantity'];
            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;

            $lineItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'options' => $options,
                'note' => isset($line['note']) && trim($line['note']) !== '' ? trim($line['note']) : null,
            ];
        }

        if ($totalAmount < 1) {
            throw ValidationException::withMessages([
                'items' => 'Total pesanan tidak valid.',
            ]);
        }

        $order = DB::transaction(function () use ($data, $orderType, $lineItems, $totalAmount) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $data['customer_name'],
                'order_type' => $orderType,
                'table_number' => null,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
                'payment_reference' => null,
            ]);

            foreach ($lineItems as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    'price' => $line['unit_price'],
                    'options' => $line['options'] ?: null,
                    'note' => $line['note'],
                ]);
            }

            return $order->load('items');
        });

        if ($data['payment_method'] === 'cash') {
            return response()->json([
                'status' => 'success',
                'payment' => 'cash',
                'message' => 'Pesanan dibuat. Silakan bayar di kasir.',
                'order' => $this->orderPayload($order),
            ], 201);
        }

        if ($data['payment_method'] === 'edc') {
            // EDC kiosk: pesanan disimpan pending, pembayaran dikonfirmasi
            // lewat terminal EDC (atau simulasi tap pada mode demo).
            $this->rememberKioskOrder($order);

            return response()->json([
                'status' => 'success',
                'payment' => 'edc',
                'message' => 'Pesanan dibuat. Silakan tap kartu pada mesin EDC.',
                'order' => $this->orderPayload($order->load('items')),
            ], 201);
        }

        try {
            $payment = $this->doku->createPayment($order->load('items'));

            $order->update([
                'payment_reference' => $payment['token_id'],
                'payment_url' => $payment['url'],
            ]);

            // Sesi di-set sebelum redirect agar halaman selesai pembayaran
            // (setelah redirect dari DOKU) tahu pesanan yang sedang dibayar.
            $this->rememberKioskOrder($order);

            return response()->json([
                'status' => 'success',
                'payment' => 'qris',
                'payment_url' => $payment['url'],
                'order' => $this->orderPayload($order->fresh('items')),
            ], 201);
        } catch (Throwable $e) {
            Log::error('DOKU checkout failed', [
                'order_number' => $order->order_number,
                'message' => $e->getMessage(),
            ]);

            $order->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Gagal membuat pembayaran QRIS. Silakan coba lagi atau pilih bayar tunai di kasir.',
            ], 502);
        }
    }

    /**
     * Webhook DOKU Checkout. Endpoint ini dikecualikan dari CSRF di bootstrap/app.php.
     * Signature diverifikasi dengan Secret Key sebelum data diproses.
     */
    public function dokuNotification(Request $request): JsonResponse
    {
        if (! $this->doku->verifyNotification($request)) {
            Log::warning('DOKU notification dengan signature tidak valid');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        try {
            $payload = json_decode($request->getContent(), true) ?? [];
            $invoiceNumber = (string) ($payload['order']['invoice_number'] ?? '');
            $status = strtoupper((string) ($payload['transaction']['status'] ?? ''));
            $orderStatus = strtoupper((string) ($payload['order']['status'] ?? ''));
            $amount = (int) round((float) ($payload['order']['amount'] ?? 0));

            $order = Order::where('order_number', $invoiceNumber)->first();

            if (! $order) {
                Log::warning('DOKU notification untuk pesanan tidak dikenal', ['invoice' => $invoiceNumber]);

                return response()->json(['message' => 'Order not found']);
            }

            if ($amount !== (int) $order->total_amount) {
                Log::warning('DOKU notification jumlah tidak cocok', [
                    'invoice' => $invoiceNumber,
                    'expected' => (int) $order->total_amount,
                    'received' => $amount,
                ]);

                return response()->json(['message' => 'Amount mismatch'], 422);
            }

            if ($status === 'SUCCESS') {
                if ($order->status !== 'paid') {
                    $order->update(['status' => 'paid', 'paid_at' => now()]);
                }
            } elseif ($orderStatus === 'ORDER_EXPIRED' || in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED'], true)) {
                if ($order->status !== 'paid') {
                    $order->update(['status' => 'cancelled']);
                }
            }

            return response()->json(['message' => 'SUCCESS']);
        } catch (Throwable $e) {
            Log::error('DOKU notification error', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Notification error'], 500);
        }
    }

    /**
     * Halaman hasil setelah pelanggan kembali dari halaman pembayaran DOKU.
     * Status pasti menunggu webhook; halaman ini memantau status secara periodik.
     */
    public function paymentFinish(Request $request)
    {
        $order = null;

        $orderId = $request->session()->get('kiosk_order_id');

        if ($orderId) {
            $order = Order::with('items')->find($orderId);
        }

        return view('payment.finish', ['order' => $order]);
    }

    /**
     * Status pesanan aktif untuk polling halaman selesai pembayaran.
     */
    public function paymentStatus(Request $request): JsonResponse
    {
        $order = Order::find($request->session()->get('kiosk_order_id'));

        if (! $order) {
            return response()->json(['status' => 'unknown'], 404);
        }

        return response()->json([
            'status' => $order->status,
            'status_label' => $order->status_label,
            'queue_number' => $order->queue_number,
            'order_number' => $order->order_number,
            'total_amount' => (int) $order->total_amount,
        ]);
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        $lastNumber = Order::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function displayOptions(?array $groups): array
    {
        return collect($groups ?? [])
            ->filter(fn ($group) => is_array($group))
            ->map(function (array $group) {
                $groupName = trim((string) ($group['groupName'] ?? ''));
                $type = ($group['type'] ?? 'radio') === 'checkbox' ? 'checkbox' : 'radio';
                $options = collect($group['options'] ?? [])
                    ->filter(fn ($option) => is_array($option) && isset($option['name']))
                    ->map(fn ($option) => [
                        'name' => (string) $option['name'],
                        'price' => (int) ($option['price'] ?? 0),
                    ])
                    ->values()
                    ->all();

                return [
                    'groupName' => $groupName !== '' ? $groupName : 'Opsi',
                    'type' => $type,
                    'options' => $options,
                ];
            })
            ->filter(fn ($group) => count($group['options']) > 0)
            ->values()
            ->all();
    }

    /**
     * Memvalidasi pilihan opsi terhadap konfigurasi produk di database.
     * Harga dari browser tidak pernah dipercaya.
     */
    private function normalizeSelectedOptions(Product $product, array $selected): array
    {
        $catalog = [];

        foreach ($product->additional_options ?? [] as $group) {
            if (! is_array($group)) {
                continue;
            }

            $groupName = trim((string) ($group['groupName'] ?? ''));
            if ($groupName === '') {
                continue;
            }

            $type = ($group['type'] ?? 'radio') === 'checkbox' ? 'checkbox' : 'radio';
            $options = [];

            foreach ($group['options'] ?? [] as $option) {
                if (! is_array($option)) {
                    continue;
                }

                $name = trim((string) ($option['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $options[$name] = (int) ($option['price'] ?? 0);
            }

            $catalog[$groupName] = [
                'type' => $type,
                'options' => $options,
            ];
        }

        $normalized = [];
        $selectedPerGroup = [];

        foreach ($selected as $choice) {
            $groupName = trim((string) ($choice['group'] ?? ''));
            $optionName = trim((string) ($choice['name'] ?? ''));

            if (! isset($catalog[$groupName])) {
                throw ValidationException::withMessages([
                    'items' => "Opsi {$groupName} tidak tersedia untuk {$product->name}.",
                ]);
            }

            if (! array_key_exists($optionName, $catalog[$groupName]['options'])) {
                throw ValidationException::withMessages([
                    'items' => "Pilihan {$optionName} tidak tersedia pada {$product->name}.",
                ]);
            }

            $selectedPerGroup[$groupName][] = $optionName;
            $normalized[] = [
                'group' => $groupName,
                'name' => $optionName,
                'price' => $catalog[$groupName]['options'][$optionName],
            ];
        }

        foreach ($selectedPerGroup as $groupName => $names) {
            if (count($names) !== count(array_unique($names))) {
                throw ValidationException::withMessages([
                    'items' => "Pilihan {$groupName} tidak boleh duplikat.",
                ]);
            }

            if ($catalog[$groupName]['type'] === 'radio' && count($names) > 1) {
                throw ValidationException::withMessages([
                    'items' => "Pilihan {$groupName} hanya boleh satu.",
                ]);
            }
        }

        return $normalized;
    }

    /**
     * Halaman status pesanan. Dipakai sebagai callback_url DOKU.
     * Jika pelanggan kembali dari DOKU (tombol "Kembali ke Merchant"),
     * halaman ini menampilkan status pesanan.
     */
    public function orderStatus(string $order_number)
    {
        $order = Order::with('items')->where('order_number', $order_number)->first();

        if (! $order) {
            abort(404);
        }

        return view('order.status', ['order' => $order]);
    }

    /**
     * Status pesanan dalam bentuk JSON untuk polling halaman status pesanan.
     */
    public function orderStatusJson(string $order_number): JsonResponse
    {
        $order = Order::where('order_number', $order_number)->first();

        if (! $order) {
            return response()->json(['status' => 'unknown'], 404);
        }

        return response()->json([
            'status' => $order->status,
            'status_label' => $order->status_label,
            'queue_number' => $order->queue_number,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'payment_method_label' => $order->payment_method_label,
            'total_amount' => (int) $order->total_amount,
            'paid' => $order->status === 'paid',
        ]);
    }

    /**
     * Membatalkan pesanan yang masih menunggu pembayaran.
     * Hanya pesanan pada sesi kiosk yang sama yang boleh dibatalkan.
     */
    public function cancelOrder(Request $request, ?string $order_number = null): JsonResponse
    {
        // Nomor pesanan bisa datang dari parameter rute (/order/{order_number}/cancel)
        // atau dari body untuk pemanggilan lama (/cancel-order).
        $data = $request->validate([
            'order_number' => ['nullable', 'string', 'max:40'],
        ]);

        $orderNumber = $order_number ?: ($data['order_number'] ?? null);

        if (! $orderNumber) {
            return response()->json(['message' => 'Nomor pesanan wajib diisi.'], 422);
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if (! $this->sessionOwnsOrder($request, $order)) {
            return response()->json(['message' => 'Pesanan bukan milik sesi ini.'], 403);
        }

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Pesanan sudah lunas dan tidak bisa dibatalkan.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'status' => 'cancelled',
            'message' => 'Pesanan dibatalkan.',
        ]);
    }

    /**
     * Simulasi tap kartu pada EDC kiosk (mode demo tanpa perangkat fisik).
     * Hanya berlaku untuk pesanan EDC pada sesi kiosk yang sama.
     */
    public function simulateEdcPayment(Request $request, string $order_number): JsonResponse
    {
        $order = Order::with('items')->where('order_number', $order_number)->first();

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if (! $this->sessionOwnsOrder($request, $order)) {
            return response()->json(['message' => 'Pesanan bukan milik sesi ini.'], 403);
        }

        if ($order->payment_method !== 'edc') {
            return response()->json(['message' => 'Pesanan ini bukan pembayaran EDC.'], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Pesanan sudah dibatalkan.'], 422);
        }

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return response()->json([
            'status' => 'paid',
            'message' => 'Pembayaran EDC berhasil.',
            'order' => $this->orderPayload($order->fresh('items')),
        ]);
    }

    private function sessionOwnsOrder(Request $request, Order $order): bool
    {
        $sessionOrderId = (int) $request->session()->get('kiosk_order_id');
        $sessionOrderIds = array_map('intval', (array) $request->session()->get('kiosk_order_ids', []));

        return $sessionOrderId === (int) $order->id
            || in_array((int) $order->id, $sessionOrderIds, true);
    }

    private function rememberKioskOrder(Order $order): void
    {
        session(['kiosk_order_id' => $order->id]);

        $kioskOrderIds = (array) session('kiosk_order_ids', []);
        $kioskOrderIds[] = $order->id;
        session(['kiosk_order_ids' => array_values(array_unique(array_map('intval', $kioskOrderIds)))]);
    }

    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'queue_number' => $order->queue_number,
            'customer_name' => $order->customer_name,
            'order_type' => $order->order_type,
            'order_type_label' => $order->order_type_label,
            'payment_method' => $order->payment_method,
            'payment_method_label' => $order->payment_method_label,
            'edc_simulate_url' => $order->payment_method === 'edc'
                ? route('order.edc.simulate', $order->order_number)
                : null,
            'cancel_url' => route('order.cancel', $order->order_number),
            'total_amount' => (int) $order->total_amount,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
                'options' => $item->options ?? [],
                'note' => $item->note,
            ])->values()->all(),
        ];
    }
}
