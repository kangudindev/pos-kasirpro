<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceService extends MarketplaceService
{
    protected string $apiUrl;

    public function __construct(array $channel)
    {
        parent::__construct($channel);
        $this->apiUrl = rtrim($channel['store_url'] ?? '', '/');
    }

    public function syncProducts(Collection $products): array
    {
        $results = ['synced' => 0, 'failed' => 0, 'errors' => []];

        foreach ($products as $product) {
            try {
                $response = $this->pushProduct($product);
                if ($response->successful()) {
                    $results['synced']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Product {$product->id}: " . $response->body();
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Product {$product->id}: " . $e->getMessage();
            }
        }

        return $results;
    }

    public function syncOrders(): array
    {
        $results = ['synced' => 0, 'failed' => 0, 'orders' => []];

        try {
            $response = $this->getOrders();
            if ($response->successful()) {
                $orders = $response->json();
                $results['orders'] = $orders;
                $results['synced'] = count($orders);
            }
        } catch (\Exception $e) {
            $results['failed'] = 1;
            $results['errors'] = [$e->getMessage()];
        }

        return $results;
    }

    protected function pushProduct($product): object
    {
        $payload = [
            'name' => $product->name,
            'type' => 'simple',
            'regular_price' => (string) ($product->sell_price ?? 0),
            'stock_quantity' => $product->stock ?? 0,
            'description' => $product->description ?? '',
            'sku' => $product->sku,
        ];

        return $this->request('POST', '/products', $payload);
    }

    protected function getOrders(): object
    {
        return $this->request('GET', '/orders', [
            'after' => now()->subDays(7)->toIso8601String(),
            'per_page' => 100,
        ]);
    }

    protected function request(string $method, string $endpoint, array $data = []): object
    {
        $url = "{$this->apiUrl}/wp-json/wc/v3{$endpoint}";
        $credentials = base64_encode("{$this->channel['api_key']}:{$this->channel['api_secret']}");

        return Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->{strtolower($method)}($url, $data);
    }
}
