<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Models\Product\Variation;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $businessId = $request->user()->business_id;

        $query = Product::with(['category', 'brand', 'sellableVariations'])
            ->where('business_id', $businessId)
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        $products = $query->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'unit', 'sellableVariations', 'images'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $product]);
    }
}
