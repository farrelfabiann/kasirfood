<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Integrasi DOKU Checkout (JOKUL) non-SNAP.
 *
 * Signature dihitung dari Client-Id, Request-Id, Request-Timestamp,
 * Request-Target, dan Digest body sesuai dokumentasi DOKU.
 */
class DokuCheckout
{
    private ?string $clientId;

    private ?string $secretKey;

    public function __construct()
    {
        $this->clientId = config('services.doku.client_id');
        $this->secretKey = config('services.doku.secret_key');
    }

    public function isConfigured(): bool
    {
        return filled($this->clientId) && filled($this->secretKey);
    }

    /**
     * Membuat sesi pembayaran DOKU dan mengembalikan URL checkout.
     *
     * @return array{url: string, token_id: string|null, expired_at: string|null}
     */
    public function createPayment(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Kredensial DOKU belum dikonfigurasi.');
        }

        $requestId = (string) Str::uuid();
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        // Kembali dari DOKU diarahkan ke halaman status pesanan, bukan beranda.
        $returnUrl = route('order.status', $order->order_number);

        // Hanya tampilkan QRIS di halaman DOKU Checkout.
        $paymentMethods = array_values((array) config('services.doku.payment_methods', ['QRIS']));

        $body = [
            'order' => [
                'amount' => (int) $order->total_amount,
                'invoice_number' => $order->order_number,
                'currency' => 'IDR',
                'callback_url' => $returnUrl,
                'callback_url_result' => $returnUrl,
                'auto_redirect' => true,
                'language' => 'ID',
                'line_items' => $order->items->map(fn ($item) => [
                    'id' => (string) $item->product_id,
                    'name' => Str::limit($item->product_name, 255, ''),
                    'quantity' => (int) $item->quantity,
                    'price' => (int) $item->price,
                ])->values()->all(),
            ],
            'payment' => [
                'payment_due_date' => (int) config('services.doku.payment_due_date', 15),
                'payment_method_types' => $paymentMethods ?: ['QRIS'],
            ],
            'customer' => [
                'name' => Str::limit($order->customer_name, 255, ''),
            ],
        ];

        $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $path = '/checkout/v1/payment';

        $response = Http::withHeaders([
            'Client-Id' => $this->clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => 'HMACSHA256='.$this->signature($requestId, $timestamp, $path, $this->digest($json)),
            'Accept' => 'application/json',
        ])->withBody($json, 'application/json')
            ->timeout(30)
            ->post(rtrim((string) config('services.doku.checkout_url'), '/').$path);

        if ($response->failed()) {
            $responseBody = $response->json() ?? $response->body();
            Log::error('DOKU checkout failed', [
                'status' => $response->status(),
                'response' => $responseBody,
                'order_number' => $order->order_number,
            ]);

            $messages = $response->json('error_messages')
                ?? $response->json('message')
                ?? ['HTTP '.$response->status()];

            throw new RuntimeException('DOKU menolak pembuatan pembayaran: '.implode(' ', (array) $messages));
        }

        $url = $response->json('response.payment.url');

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('DOKU tidak mengembalikan URL pembayaran.');
        }

        return [
            'url' => $url,
            'token_id' => $response->json('response.payment.token_id'),
            'expired_at' => $response->json('response.payment.expired_date'),
        ];
    }

    /**
     * Memverifikasi signature HTTP Notification dari DOKU.
     */
    public function verifyNotification(Request $request): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $clientId = (string) $request->header('Client-Id');
        $requestId = (string) $request->header('Request-Id');
        $timestamp = (string) $request->header('Request-Timestamp');
        $signature = (string) $request->header('Signature');

        if ($clientId === '' || $requestId === '' || $timestamp === '' || $signature === '') {
            return false;
        }

        if (! hash_equals((string) $this->clientId, $clientId)) {
            return false;
        }

        $target = '/'.ltrim($request->path(), '/');
        $digest = $this->digest($request->getContent());
        $expected = 'HMACSHA256='.$this->signature($requestId, $timestamp, $target, $digest);

        return hash_equals($expected, $signature);
    }

    private function signature(string $requestId, string $timestamp, string $target, string $digest): string
    {
        $payload = implode("\n", [
            'Client-Id:'.$this->clientId,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:'.$target,
            'Digest:'.$digest,
        ]);

        return base64_encode(hash_hmac('sha256', $payload, (string) $this->secretKey, true));
    }

    private function digest(string $body): string
    {
        return base64_encode(hash('sha256', $body, true));
    }
}
