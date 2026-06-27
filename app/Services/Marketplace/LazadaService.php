<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LazadaService extends MarketplaceService
{
    protected string $apiUrl = 'https://api.lazada.com.my/rest';

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
                $data = $response->json();
                $results['orders'] = $data['data']['orders'] ?? [];
                $results['synced'] = count($results['orders']);
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
            'PrimaryCategory' => $this->mapCategory($product->category_id),
            'Attributes' => [
                'name' => $product->name,
                'description' => $product->description ?? '',
            ],
            'Skus' => [[
                'SellerSku' => $product->sku,
                'quantity' => $product->stock ?? 0,
                'price' => $product->sell_price ?? 0,
            ]],
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->channel['access_token'],
        ])->post("{$this->apiUrl}/product/create", $payload);
    }

    protected function getOrders(): object
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->channel['access_token'],
        ])->get("{$this->apiUrl}/orders/get", [
            'created_after' => now()->subDays(7)->toIso8601String(),
            'limit' => 100,
        ]);
    }

    protected function mapCategory($localCategoryId): int
    {
        return 100001;
    }
}
