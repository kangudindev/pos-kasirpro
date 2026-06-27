<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokopediaService extends MarketplaceService
{
    protected string $apiUrl = 'https://fs.tokopedia.net';

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
        $shopId = $this->channel['shop_id'];
        $url = "/inventory/v1/fs/{$this->channel['api_key']}/product/create";

        $payload = [
            'products' => [[
                'name' => $product->name,
                'price' => $product->sell_price ?? 0,
                'stock' => $product->stock ?? 0,
                'description' => $product->description ?? '',
            ]],
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->channel['access_token'],
        ])->post("{$this->apiUrl}{$url}?shop_id={$shopId}", $payload);
    }

    protected function getOrders(): object
    {
        $shopId = $this->channel['shop_id'];
        $url = "/order/v1/fs/{$this->channel['api_key']}/orders";

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->channel['access_token'],
        ])->get("{$this->apiUrl}{$url}", [
            'shop_id' => $shopId,
            'from_date' => now()->subDays(7)->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
        ]);
    }
}
