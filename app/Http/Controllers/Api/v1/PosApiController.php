<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\Transaction;

class PosApiController extends Controller
{
    public function createSale(Request $request)
    {
        $request->validate([
            'contact_id' => 'required',
            'products' => 'required|array|min:1',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->business_id;

        DB::beginTransaction();

        try {
            $transactionUtil = app(\App\Utils\TransactionUtil::class);
            $productUtil = app(\App\Utils\ProductUtil::class);

            $transaction = $transactionUtil->createSellTransaction($request, $businessId);
            $finalTotal = $transactionUtil->createOrUpdateSellLines($request, $businessId, $transaction->id);
            $transactionUtil->createOrUpdatePaymentLines($request, $businessId, $transaction->id);
            $productUtil->updateProductStock($transaction->id, 'sell', 'final');
            $transactionUtil->updatePaymentStatus($transaction->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'invoice_no' => $transaction->invoice_no,
                    'total' => $finalTotal,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function recentTransactions(Request $request)
    {
        $businessId = $request->user()->business_id;

        $transactions = Transaction::with('contact')
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->latest()
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function searchBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $businessId = $request->user()->business_id;

        $variation = Variation::whereHas('product', function ($q) use ($barcode, $businessId) {
            $q->where('business_id', $businessId)->where('sku', $barcode);
        })->orWhere('sub_sku', $barcode)->first();

        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'variation_id' => $variation->id,
                'product_id' => $variation->product_id,
                'name' => $variation->product->name,
                'sku' => $variation->sub_sku ?? $variation->product->sku,
                'price' => $variation->default_sell_price,
                'stock' => \App\Utils\ProductUtil::getCurrentStock($variation->id, $request->input('location_id', 1)),
            ],
        ]);
    }
}
