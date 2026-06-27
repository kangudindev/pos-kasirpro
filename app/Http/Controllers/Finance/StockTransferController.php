<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Utils\ProductUtil;
use App\Utils\Util;

class StockTransferController extends Controller
{
    protected $productUtil;

    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    public function index()
    {
        $businessId = session('current_business_id');
        $transfers = DB::table('stock_transfers')
            ->where('business_id', $businessId)
            ->latest()
            ->paginate(20);

        return view('stock.transfer_index', compact('transfers'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $products = \App\Models\Product\Product::where('business_id', $businessId)->active()->get();
        $locations = \App\Models\Business\BusinessLocation::where('business_id', $businessId)->get();

        return view('stock.transfer_create', compact('products', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_location_id' => 'required',
            'to_location_id' => 'required|different:from_location_id',
            'products' => 'required|array|min:1',
        ]);

        $businessId = session('current_business_id');

        DB::beginTransaction();

        try {
            $transfer = DB::table('stock_transfers')->insertGetId([
                'business_id' => $businessId,
                'ref_no' => Util::generateReferenceNumber('stock_transfer', $businessId),
                'from_location_id' => $request->from_location_id,
                'to_location_id' => $request->to_location_id,
                'status' => 'completed',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->products as $item) {
                DB::table('stock_transfer_lines')->insert([
                    'stock_transfer_id' => $transfer,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'quantity' => $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->productUtil->decreaseProductQuantity($item['variation_id'], $request->from_location_id, $item['quantity']);
                $this->productUtil->increaseProductQuantity($item['variation_id'], $request->to_location_id, $item['quantity']);
            }

            DB::commit();

            return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
