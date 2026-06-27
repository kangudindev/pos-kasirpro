<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\Transaction;
use App\Utils\TransactionUtil;
use App\Utils\Util;

class StockAdjustmentController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    public function index()
    {
        $businessId = session('current_business_id');
        $adjustments = Transaction::with(['createdBy'])
            ->where('business_id', $businessId)
            ->where('type', 'stock_adjustment')
            ->latest()
            ->paginate(20);

        return view('stock.adjustment_index', compact('adjustments'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $products = \App\Models\Product\Product::where('business_id', $businessId)->active()->get();
        $locations = \App\Models\Business\BusinessLocation::where('business_id', $businessId)->get();

        return view('stock.adjustment_create', compact('products', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required',
            'adjustment_type' => 'required|in:normal,abnormal',
            'products' => 'required|array|min:1',
        ]);

        $transaction = $this->transactionUtil->createStockAdjustment($request, session('current_business_id'));

        return redirect()->route('stock-adjustments.index')
            ->with('success', "Stock adjustment created! Ref: {$transaction->ref_no}");
    }
}
