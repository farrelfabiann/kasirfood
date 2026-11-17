<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_user_gets_forbidden(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->get('/admin')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Makanan']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 20000,
            'is_available' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Ringkasan Hari Ini')->assertSee('KlikKluk');
        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertSee('Nasi Goreng');
        $this->actingAs($admin)->get('/admin/products/create')->assertOk()->assertSee('Tambah Kategori');
        $this->actingAs($admin)->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('Nasi Goreng');
        $this->actingAs($admin)->get('/admin/categories')->assertOk()->assertSee('Makanan');
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
        $this->actingAs($admin)->get('/admin/orders?partial=1')->assertOk()->assertSee('Menunggu Bayar');
    }

    public function test_admin_can_create_product_with_options(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Minuman']);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Es Kopi Susu',
            'category_id' => $category->id,
            'price' => 18000,
            'description' => 'Kopi susu segar.',
            'is_available' => '1',
            'options_json' => json_encode([
                [
                    'groupName' => 'Ukuran',
                    'type' => 'radio',
                    'options' => [
                        ['name' => 'Regular', 'price' => 0],
                        ['name' => 'Large', 'price' => 5000],
                    ],
                ],
            ]),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Es Kopi Susu')->first();

        $this->assertNotNull($product);
        $this->assertTrue($product->is_available);
        $this->assertSame('Large', $product->additional_options[0]['options'][1]['name']);
    }

    public function test_admin_can_confirm_cash_payment(): void
    {
        $admin = $this->admin();

        $order = Order::create([
            'order_number' => 'ORD-20260101-0001',
            'customer_name' => 'Budi',
            'order_type' => 'online',
            'total_amount' => 25000,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.confirm', $order));

        $response->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_admin_can_confirm_edc_payment(): void
    {
        $admin = $this->admin();

        $order = Order::create([
            'order_number' => 'ORD-20260101-0002',
            'customer_name' => 'Sari',
            'order_type' => 'online',
            'total_amount' => 30000,
            'payment_method' => 'edc',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.confirm', $order));

        $response->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_admin_orders_show_payment_method_badges(): void
    {
        $admin = $this->admin();

        foreach ([
            ['ORD-20260101-0001', 'qris'],
            ['ORD-20260101-0002', 'edc'],
            ['ORD-20260101-0003', 'cash'],
        ] as [$number, $method]) {
            Order::create([
                'order_number' => $number,
                'customer_name' => 'Pelanggan',
                'order_type' => 'online',
                'total_amount' => 20000,
                'payment_method' => $method,
                'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertOk();
        $response->assertSee('DOKU (Online)');
        $response->assertSee('EDC Kiosk (Kartu)');
        $response->assertSee('Tunai (Kasir)');
        $response->assertSee('Konfirmasi Lunas');
    }

    public function test_admin_can_filter_orders_by_payment_method(): void
    {
        $admin = $this->admin();

        Order::create([
            'order_number' => 'ORD-20260101-0001',
            'customer_name' => 'Pelanggan QRIS',
            'order_type' => 'online',
            'total_amount' => 20000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        Order::create([
            'order_number' => 'ORD-20260101-0002',
            'customer_name' => 'Pelanggan EDC',
            'order_type' => 'online',
            'total_amount' => 20000,
            'payment_method' => 'edc',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/orders?payment=edc');

        $response->assertOk();
        $response->assertSee('Pelanggan EDC');
        $response->assertDontSee('Pelanggan QRIS');
    }

    public function test_admin_can_add_category_from_product_form_via_json(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson(route('admin.categories.store'), [
            'name' => 'Minuman Segar',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('category.name', 'Minuman Segar');
        $response->assertJsonPath('category.icon_key', 'cup');

        $this->assertDatabaseHas('categories', ['name' => 'Minuman Segar']);

        $duplicate = $this->actingAs($admin)->postJson(route('admin.categories.store'), [
            'name' => 'Minuman Segar',
        ]);

        $duplicate->assertStatus(422);
        $duplicate->assertJsonValidationErrors('name');
        $this->assertSame(1, Category::where('name', 'Minuman Segar')->count());
    }

    private function fakePng(string $name = 'promo.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    public function test_admin_can_add_category_with_icon_and_uploaded_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Promo Spesial',
            'icon' => 'promo',
            'icon_image' => $this->fakePng(),
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Promo Spesial')->first();

        $this->assertNotNull($category);
        $this->assertSame('promo', $category->icon);
        $this->assertNotNull($category->icon_image);
        Storage::disk('public')->assertExists($category->icon_image);
        $this->assertStringContainsString('/storage/', $category->icon_url);
    }

    public function test_admin_can_replace_and_remove_category_icon_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $oldPath = 'categories/old.png';
        Storage::disk('public')->put($oldPath, 'old');

        $category = Category::create([
            'name' => 'Promo Kilat',
            'icon' => 'promo',
            'icon_image' => $oldPath,
        ]);

        $replace = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Promo Kilat',
            'icon' => 'tag',
            'icon_image' => $this->fakePng('new.png'),
        ]);

        $replace->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('tag', $category->icon);
        $this->assertNotSame($oldPath, $category->icon_image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($category->icon_image);

        $newPath = $category->icon_image;

        $remove = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Promo Kilat',
            'icon' => 'tag',
            'remove_icon_image' => '1',
        ]);

        $remove->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertNull($category->icon_image);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_admin_category_page_shows_delete_button_and_detaches_products(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Makanan']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 20000,
            'is_available' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertOk();
        $response->assertSee('category-delete-'.$category->id);
        $response->assertSee('trash');
        $response->assertSee('Hapus kategori');
    }

    public function test_deleting_category_detaches_products_and_removes_icon(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $category = Category::create(['name' => 'Makanan']);

        Storage::disk('public')->put('categories/icon.png', 'icon');
        $category->update(['icon_image' => 'categories/icon.png']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 20000,
            'is_available' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => null]);
        Storage::disk('public')->assertMissing('categories/icon.png');
    }
}
