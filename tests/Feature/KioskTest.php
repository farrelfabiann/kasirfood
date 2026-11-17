<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'Makanan']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Spesial',
            'description' => 'Nasi goreng dengan telur.',
            'price' => 20000,
            'image' => null,
            'is_available' => true,
            'additional_options' => [
                [
                    'groupName' => 'Tambahan',
                    'type' => 'checkbox',
                    'options' => [
                        ['name' => 'Telur', 'price' => 5000],
                    ],
                ],
            ],
        ], $attributes));
    }

    public function test_kiosk_page_renders_products_and_categories(): void
    {
        $this->makeProduct();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Nasi Goreng Spesial');
        $response->assertSee('Makanan');
        $response->assertSee('KlikKluk');
    }

    public function test_kiosk_renders_empty_catalog_when_no_products_exist(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('kiosk', fn (array $kiosk) => $kiosk['products'] === [] && $kiosk['categories'] === []);
    }

    public function test_kiosk_hides_categories_without_products(): void
    {
        Category::create(['name' => 'Kategori Kosong']);
        $this->makeProduct();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('kiosk', fn (array $kiosk) => collect($kiosk['categories'])->pluck('name')->all() === ['Makanan']);
    }

    public function test_kiosk_exposes_category_icon_key_and_image(): void
    {
        $category = Category::create(['name' => 'Promo Spesial', 'icon' => 'promo']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Paket Hemat',
            'price' => 15000,
            'is_available' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Promo Spesial');

        $response->assertViewHas('kiosk', function (array $kiosk) {
            $category = collect($kiosk['categories'])->firstWhere('name', 'Promo Spesial');

            return $category !== null
                && $category['icon'] === 'promo'
                && $category['icon_url'] === null;
        });
    }

    public function test_cash_checkout_creates_pending_order_and_recalculates_totals(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/checkout', [
            'customer_name' => 'Budi',
            'order_type' => 'take_away',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'note' => 'Tanpa sambal',
                    'options' => [
                        ['group' => 'Tambahan', 'name' => 'Telur', 'price' => 1],
                    ],
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payment', 'cash');
        $response->assertJsonPath('order.status', 'pending');
        $response->assertJsonPath('order.total_amount', 50000);

        $order = Order::with('items')->first();

        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame('cash', $order->payment_method);
        $this->assertSame('online', $order->order_type);
        $this->assertSame(50000, $order->total_amount);
        $this->assertSame('ORD-'.now()->format('Ymd').'-0001', $order->order_number);
        $this->assertSame('0001', $order->queue_number);

        $item = $order->items->first();
        $this->assertSame('Nasi Goreng Spesial', $item->product_name);
        $this->assertSame(25000, $item->price);
        $this->assertSame('Tanpa sambal', $item->note);
        $this->assertSame(5000, $item->options[0]['price']);
    }

    public function test_unavailable_product_cannot_be_ordered(): void
    {
        $product = $this->makeProduct(['is_available' => false]);

        $response = $this->postJson('/checkout', [
            'customer_name' => 'Budi',
            'order_type' => 'dine_in',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_unknown_option_is_rejected(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/checkout', [
            'customer_name' => 'Budi',
            'order_type' => 'dine_in',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'options' => [
                        ['group' => 'Tambahan', 'name' => 'Keju Palsu'],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_qris_checkout_is_rejected_when_doku_not_configured(): void
    {
        $product = $this->makeProduct();

        config([
            'services.doku.client_id' => null,
            'services.doku.secret_key' => null,
        ]);

        $response = $this->postJson('/checkout', [
            'customer_name' => 'Budi',
            'order_type' => 'dine_in',
            'payment_method' => 'qris',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    private function makeOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-20261117-0001',
            'customer_name' => 'Budi',
            'order_type' => 'dine_in',
            'table_number' => null,
            'total_amount' => 25000,
            'payment_method' => 'qris',
            'payment_reference' => 'doku-token',
            'payment_url' => 'https://api.doku.com/checkout/abc',
            'paid_at' => null,
            'status' => 'pending',
        ], $attributes));
    }

    public function test_order_status_page_shows_pending_order_with_continue_payment(): void
    {
        $order = $this->makeOrder();

        $response = $this->get('/order/'.$order->order_number);

        $response->assertOk();
        $response->assertSee('Pembayaran Belum Selesai');
        $response->assertSee('Lanjutkan Pembayaran QRIS');
        $response->assertSee('Batalkan Pesanan');
        $response->assertSee($order->payment_url, false);
    }

    public function test_order_status_page_shows_queue_number_when_paid(): void
    {
        $order = $this->makeOrder([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->get('/order/'.$order->order_number);

        $response->assertOk();
        $response->assertSee('Pembayaran Lunas');
        $response->assertSee($order->queue_number);
        $response->assertDontSee('Lanjutkan Pembayaran QRIS');
    }

    public function test_order_status_json_reports_latest_status(): void
    {
        $order = $this->makeOrder(['status' => 'paid', 'paid_at' => now()]);

        $response = $this->getJson('/order/'.$order->order_number.'/status');

        $response->assertOk();
        $response->assertJsonPath('status', 'paid');
        $response->assertJsonPath('paid', true);
        $response->assertJsonPath('queue_number', $order->queue_number);
    }

    public function test_cancel_order_marks_pending_order_cancelled(): void
    {
        $order = $this->makeOrder();

        $response = $this->withSession(['kiosk_order_id' => $order->id])
            ->postJson('/order/'.$order->order_number.'/cancel');

        $response->assertOk();
        $response->assertJsonPath('status', 'cancelled');
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancel_order_is_rejected_for_paid_order(): void
    {
        $order = $this->makeOrder(['status' => 'paid', 'paid_at' => now()]);

        $response = $this->withSession(['kiosk_order_id' => $order->id])
            ->postJson('/order/'.$order->order_number.'/cancel');

        $response->assertStatus(422);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_edc_checkout_creates_pending_order_without_doku_payment(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/checkout', [
            'customer_name' => 'Sari',
            'order_type' => 'dine_in',
            'payment_method' => 'edc',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payment', 'edc');
        $response->assertJsonPath('order.status', 'pending');
        $response->assertJsonPath('order.payment_method', 'edc');
        $response->assertJsonPath('order.payment_method_label', 'EDC Kiosk (Kartu)');
        $response->assertJsonPath('order.queue_number', '0001');
        $response->assertJsonPath('order.edc_simulate_url', route('order.edc.simulate', 'ORD-'.now()->format('Ymd').'-0001'));

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertSame('edc', $order->payment_method);
        $this->assertSame('pending', $order->status);
        $this->assertNull($order->payment_url);
    }

    public function test_edc_simulation_marks_order_paid_for_kiosk_session(): void
    {
        $order = $this->makeOrder([
            'payment_method' => 'edc',
            'payment_reference' => null,
            'payment_url' => null,
        ]);

        $response = $this->withSession(['kiosk_order_id' => $order->id])
            ->postJson('/order/'.$order->order_number.'/edc/simulate');

        $response->assertOk();
        $response->assertJsonPath('status', 'paid');
        $response->assertJsonPath('order.status', 'paid');
        $response->assertJsonPath('order.queue_number', $order->queue_number);

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_edc_simulation_is_rejected_for_cash_order(): void
    {
        $order = $this->makeOrder([
            'payment_method' => 'cash',
            'payment_reference' => null,
            'payment_url' => null,
        ]);

        $response = $this->withSession(['kiosk_order_id' => $order->id])
            ->postJson('/order/'.$order->order_number.'/edc/simulate');

        $response->assertStatus(422);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_edc_simulation_is_rejected_for_other_session(): void
    {
        $order = $this->makeOrder([
            'payment_method' => 'edc',
            'payment_reference' => null,
            'payment_url' => null,
        ]);

        $response = $this->postJson('/order/'.$order->order_number.'/edc/simulate');

        $response->assertStatus(403);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cash_order_status_page_shows_cashier_instruction(): void
    {
        $order = $this->makeOrder([
            'payment_method' => 'cash',
            'payment_reference' => null,
            'payment_url' => null,
        ]);

        $response = $this->get('/order/'.$order->order_number);

        $response->assertOk();
        $response->assertSee('Menunggu Pembayaran di Kasir');
        $response->assertSee('Silakan menuju kasir dan tunjukkan Nomor Antrean Anda untuk melakukan pembayaran tunai.');
        $response->assertSee('Tunai (Kasir)');
        $response->assertDontSee('Lanjutkan Pembayaran QRIS');
    }
}
