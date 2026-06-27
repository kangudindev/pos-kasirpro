<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;
    protected string $apiUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $this->clientKey = config('services.midtrans.client_key', env('MIDTRANS_CLIENT_KEY'));
        $this->isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $this->apiUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    public function createTransaction(string $orderId, float $amount, array $customer = []): array
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $customer['name'] ?? 'Customer',
                'email' => $customer['email'] ?? '',
                'phone' => $customer['phone'] ?? '',
            ],
            'enabled_payments' => [
                'credit_card',
                'bca_va',
                'bni_va',
                'bri_va',
                'mandiri_bill',
                'permata_va',
                'gopay',
                'shopeepay',
                'other_qris',
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->apiUrl}/transactions", $payload);

        if ($response->failed()) {
            Log::error('Midtrans create transaction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return ['success' => false, 'error' => $response->body()];
        }

        $data = $response->json();

        return [
            'success' => true,
            'token' => $data['token'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
            'order_id' => $orderId,
        ];
    }

    public function getStatus(string $orderId): array
    {
        $baseUrl = $this->isProduction 
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $response = Http::withBasicAuth($this->serverKey, '')
            ->get("{$baseUrl}/{$orderId}/status");

        if ($response->failed()) {
            return ['success' => false, 'error' => $response->body()];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    public function cancel(string $orderId): bool
    {
        $baseUrl = $this->isProduction 
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $response = Http::withBasicAuth($this->serverKey, '')
            ->post("{$baseUrl}/{$orderId}/cancel");

        return $response->successful();
    }

    public function validateSignature(array $data): bool
    {
        $orderId = $data['order_id'] ?? '';
        $statusCode = $data['status_code'] ?? '';
        $grossAmount = $data['gross_amount'] ?? '';
        $serverKey = $this->serverKey;

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return $signature === ($data['signature_key'] ?? '');
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }
}
