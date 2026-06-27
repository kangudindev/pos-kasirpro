<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeService extends MarketplaceService
{
    protected string $apiUrl = 'https://partner.shopeemobile.com/api/v2';

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
                $results['orders'] = $data['response']['order_list'] ?? [];
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
        $partnerId = $this->channel['api_key'];
        $shopId = $this->channel['shop_id'];
        $path = '/product/add_item';
        $timestamp = time();
        $sign = $this->generateSign($partnerId, $path, $timestamp);

        $payload = [
            'item_name' => $product->name,
            'category_id' => $this->mapCategory($product->category_id),
            'price' => $product->sell_price ?? 0,
            'stock' => $product->stock ?? 0,
            'description' => $product->description ?? '',
        ];

        return Http::post("{$this->apiUrl}{$path}", array_merge($payload, [
            'partner_id' => $partnerId,
            'shop_id' => $shopId,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ]));
    }

    protected function getOrders(): object
    {
        $partnerId = $this->channel['api_key'];
        $shopId = $this->channel['shop_id'];
        $path = '/order/get_order_list';
        $timestamp = time();
        $sign = $this->generateSign($partnerId, $path, $timestamp);

        return Http::get("{$this->apiUrl}{$path}", [
            'partner_id' => $partnerId,
            'shop_id' => $shopId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'time_range_field' => 'create_time',
            'time_from' => strtotime('-7 days'),
            'time_to' => time(),
            'page_size' => 100,
        ]);
    }

    protected function generateSign(int $partnerId, string $path, int $timestamp): string
    {
        $secret = $this->channel['api_secret'];
        $baseString = $partnerId . $path . $timestamp;
        return hash_hmac('sha256', $baseString, $secret);
    }

    protected function mapCategory($localCategoryId): int
    {
        return 100001;
    }
}
