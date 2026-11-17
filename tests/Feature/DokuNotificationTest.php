<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokuNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.doku.client_id' => 'BRN-TEST-0001',
            'services.doku.secret_key' => 'SK-TEST-SECRET',
        ]);
    }

    private function makeOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-20261117-0002',
            'customer_name' => 'Sari',
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

    private function signedNotification(string $body, ?string $signature = null): array
    {
        $clientId = (string) config('services.doku.client_id');
        $requestId = 'test-request-id';
        $timestamp = '2026-11-17T01:00:00Z';
        $digest = base64_encode(hash('sha256', $body, true));

        $payload = implode("\n", [
            'Client-Id:'.$clientId,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:/doku/notification',
            'Digest:'.$digest,
        ]);

        $signature ??= 'HMACSHA256='.base64_encode(
            hash_hmac('sha256', $payload, (string) config('services.doku.secret_key'), true)
        );

        return [
            'HTTP_CLIENT_ID' => $clientId,
            'HTTP_REQUEST_ID' => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    private function notify(Order $order, array $transaction, array $orderStatus = []): \Illuminate\Testing\TestResponse
    {
        $body = json_encode([
            'service' => ['id' => 'QRIS'],
            'order' => array_merge([
                'invoice_number' => $order->order_number,
                'amount' => (int) $order->total_amount,
            ], $orderStatus),
            'transaction' => $transaction,
        ], JSON_UNESCAPED_SLASHES);

        return $this->call('POST', '/doku/notification', [], [], [], $this->signedNotification($body), $body);
    }

    public function test_success_notification_marks_order_paid(): void
    {
        $order = $this->makeOrder();

        $this->notify($order, ['status' => 'SUCCESS', 'date' => '2026-11-17T01:00:00Z'])
            ->assertOk();

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_notification_with_invalid_signature_is_rejected(): void
    {
        $order = $this->makeOrder();

        $body = json_encode([
            'order' => ['invoice_number' => $order->order_number, 'amount' => 25000],
            'transaction' => ['status' => 'SUCCESS'],
        ], JSON_UNESCAPED_SLASHES);

        $this->call('POST', '/doku/notification', [], [], [], $this->signedNotification($body, 'HMACSHA256=invalid'), $body)
            ->assertStatus(401);

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_expired_order_notification_cancels_pending_order(): void
    {
        $order = $this->makeOrder();

        $this->notify(
            $order,
            ['status' => 'PENDING', 'date' => '2026-11-17T01:00:00Z'],
            ['status' => 'ORDER_EXPIRED']
        )->assertOk();

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_notification_amount_mismatch_is_rejected(): void
    {
        $order = $this->makeOrder();

        $body = json_encode([
            'order' => ['invoice_number' => $order->order_number, 'amount' => 1000],
            'transaction' => ['status' => 'SUCCESS'],
        ], JSON_UNESCAPED_SLASHES);

        $this->call('POST', '/doku/notification', [], [], [], $this->signedNotification($body), $body)
            ->assertStatus(422);

        $this->assertSame('pending', $order->fresh()->status);
    }
}
