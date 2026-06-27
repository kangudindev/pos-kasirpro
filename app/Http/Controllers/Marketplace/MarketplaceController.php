<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\ShopeeService;
use App\Services\Marketplace\TokopediaService;
use App\Services\Marketplace\LazadaService;
use App\Services\Marketplace\WooCommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MarketplaceController extends Controller
{
    /**
     * List ecommerce channels
     */
    public function index()
    {
        $businessId = auth()->user()->business_id;
        $channels = DB::table('ecommerce_channels')
            ->where('business_id', $businessId)
            ->get();

        return view('marketplace.index', compact('channels'));
    }

    /**
     * Add channel
     */
    public function create()
    {
        return view('marketplace.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:shopee,tokopedia,lazada,woocommerce',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $businessId = auth()->user()->business_id;

        DB::table('ecommerce_channels')->insert([
            'business_id' => $businessId,
            'platform' => $request->platform,
            'name' => $request->name,
            'api_key' => $request->api_key,
            'api_secret' => $request->api_secret,
            'shop_id' => $request->shop_id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('marketplace.index')
            ->with('success', 'Channel added!');
    }

    public function destroy($id)
    {
        DB::table('ecommerce_channels')->where('id', $id)->delete();

        return redirect()->route('marketplace.index')
            ->with('success', 'Channel deleted!');
    }

    /**
     * Sync products to marketplace
     */
    public function syncProducts($channelId)
    {
        $businessId = auth()->user()->business_id;
        $channel = DB::table('ecommerce_channels')
            ->where('id', $channelId)
            ->where('business_id', $businessId)
            ->first();

        if (!$channel) {
            return redirect()->back()->with('error', 'Channel not found');
        }

        $products = DB::table('products')
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->get();

        $service = $this->getMarketplaceService((array) $channel);
        $result = $service->syncProducts(collect($products));

        $message = "Synced {$result['synced']} products";
        if ($result['failed'] > 0) {
            $message .= ", {$result['failed']} failed";
        }

        DB::table('ecommerce_channels')
            ->where('id', $channelId)
            ->update(['last_sync_at' => now()]);

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Sync orders from marketplace
     */
    public function syncOrders($channelId)
    {
        $businessId = auth()->user()->business_id;
        $channel = DB::table('ecommerce_channels')
            ->where('id', $channelId)
            ->where('business_id', $businessId)
            ->first();

        if (!$channel) {
            return redirect()->back()->with('error', 'Channel not found');
        }

        $service = $this->getMarketplaceService((array) $channel);
        $result = $service->syncOrders();

        foreach ($result['orders'] as $orderData) {
            DB::table('ecommerce_orders')->updateOrInsert(
                [
                    'channel_id' => $channelId,
                    'channel_order_id' => $orderData['order_id'] ?? $orderData['id'] ?? null,
                ],
                [
                    'business_id' => $businessId,
                    'status' => $orderData['status'] ?? null,
                    'buyer_name' => $orderData['buyer_name'] ?? null,
                    'buyer_phone' => $orderData['buyer_phone'] ?? null,
                    'total_amount' => $orderData['total_amount'] ?? 0,
                    'synced_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('ecommerce_channels')
            ->where('id', $channelId)
            ->update(['last_sync_at' => now()]);

        $message = "Synced {$result['synced']} orders";
        if (isset($result['failed']) && $result['failed'] > 0) {
            $message .= ", {$result['failed']} failed";
        }

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * View orders from marketplace
     */
    public function orders($channelId)
    {
        $businessId = auth()->user()->business_id;
        
        $orders = DB::table('ecommerce_orders')
            ->where('channel_id', $channelId)
            ->where('business_id', $businessId)
            ->latest()
            ->paginate(20);

        return view('marketplace.orders', compact('orders'));
    }

    /**
     * Get marketplace service by platform
     */
    protected function getMarketplaceService(array $channel)
    {
        return match($channel['platform']) {
            'shopee' => new ShopeeService($channel),
            'tokopedia' => new TokopediaService($channel),
            'lazada' => new LazadaService($channel),
            'woocommerce' => new WooCommerceService($channel),
            default => throw new \Exception('Unknown marketplace platform'),
        };
    }
}
