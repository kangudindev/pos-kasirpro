<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Models\Product\Variation;

class ProductApiController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        $businessId = session('current_business_id');

        if (!$query) {
            return response()->json([]);
        }

        $products = Product::with(['sellableVariations', 'category'])
            ->where('business_id', $businessId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->active()
            ->limit(20)
            ->get();

        return response()->json($products);
    }
}
