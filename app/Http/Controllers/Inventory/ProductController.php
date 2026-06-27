<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product\Product;
use App\Models\Product\ProductVariation;
use App\Models\Product\Variation;
use App\Models\Product\Category;
use App\Models\Product\Brand;
use App\Models\Product\Unit;
use App\Models\Product\VariationLocationDetail;
use App\Utils\ProductUtil;
use App\Utils\Util;

class ProductController extends Controller
{
    protected $productUtil;

    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    public function index(Request $request)
    {
        $businessId = session('current_business_id');

        $query = Product::with(['category', 'brand', 'unit', 'sellableVariations.locationDetails'])
            ->where('business_id', $businessId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->latest()->paginate(20);

        $categories = Category::where('business_id', $businessId)->get();
        $brands = Brand::where('business_id', $businessId)->get();

        return view('product.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $businessId = session('current_business_id');

        $categories = Category::where('business_id', $businessId)->get();
        $brands = Brand::where('business_id', $businessId)->get();
        $units = Unit::where('business_id', $businessId)->get();

        return view('product.create', compact('categories', 'brands', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'type' => 'required|in:single,variable',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $businessId = session('current_business_id');

        $product = Product::create([
            'business_id' => $businessId,
            'name' => $request->name,
            'sku' => $request->sku ?? $this->generateSku($businessId),
            'type' => $request->type,
            'unit_id' => $request->unit_id,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'tax_id' => $request->tax_id,
            'tax_type' => $request->tax_type ?? 'exclusive',
            'enable_stock' => $request->has('enable_stock'),
            'alert_quantity' => $request->alert_quantity ?? 0,
            'product_description' => $request->description,
            'image' => $request->hasFile('image') ? Util::uploadFile($request->file('image'), 'products') : null,
            'created_by' => Auth::id(),
        ]);

        // Create variation
        if ($request->type == 'single') {
            $variation = $this->productUtil->createSingleProductVariation($product, $businessId);
            $variation->update([
                'default_sell_price' => $request->selling_price,
                'default_purchase_price' => $request->cost_price ?? 0,
                'sell_price_inc_tax' => $request->selling_price,
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'unit', 'variations', 'images'])
            ->findOrFail($id);

        return view('product.show', compact('product'));
    }

    public function edit($id)
    {
        $businessId = session('current_business_id');

        $product = Product::with(['variations', 'images'])->findOrFail($id);
        $categories = Category::where('business_id', $businessId)->get();
        $brands = Brand::where('business_id', $businessId)->get();
        $units = Unit::where('business_id', $businessId)->get();

        return view('product.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'unit_id' => $request->unit_id,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'tax_id' => $request->tax_id,
            'tax_type' => $request->tax_type ?? 'exclusive',
            'enable_stock' => $request->has('enable_stock'),
            'alert_quantity' => $request->alert_quantity ?? 0,
            'product_description' => $request->description,
        ]);

        // Update variation price
        if ($product->isSingle()) {
            $variation = $product->sellableVariations()->first();
            if ($variation) {
                $variation->update([
                    'default_sell_price' => $request->selling_price ?? $variation->default_sell_price,
                    'default_purchase_price' => $request->cost_price ?? $variation->default_purchase_price,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function stock($id)
    {
        $product = Product::with(['sellableVariations.locationDetails.location'])->findOrFail($id);
        $locations = \App\Models\Business\BusinessLocation::where('business_id', session('current_business_id'))->get();

        return view('product.stock', compact('product', 'locations'));
    }

    protected function generateSku($businessId)
    {
        $prefix = 'PRD';
        $nextId = Product::where('business_id', $businessId)->max('id') + 1;
        return $prefix . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
