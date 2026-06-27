<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Product;
use App\Models\Product\Variation;
use App\Models\Contact\Contact;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionSellLine;
use App\Models\Transaction\TransactionPayment;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use App\Utils\CashRegisterUtil;

class SellPosController extends Controller
{
    protected $transactionUtil;
    protected $productUtil;
    protected $cashRegisterUtil;

    public function __construct(
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil,
        CashRegisterUtil $cashRegisterUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    /**
     * POS Screen
     */
    public function index()
    {
        $businessId = session('current_business_id');
        $locationId = session('current_location_id');

        $categories = \App\Models\Product\Category::where('business_id', $businessId)->get();
        $brands = \App\Models\Product\Brand::where('business_id', $businessId)->get();
        $customers = Contact::where('business_id', $businessId)->customers()->get();

        $products = Product::with(['sellableVariations', 'category'])
            ->where('business_id', $businessId)
            ->active()
            ->forSale()
            ->select('id', 'name', 'sku', 'category_id', 'brand_id', 'unit_id', 'image', 'is_active')
            ->paginate(50);

        return view('pos.create', compact('categories', 'brands', 'customers', 'products'));
    }

    /**
     * Store POS Transaction
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'products' => 'required|array|min:1',
            'products.*.variation_id' => 'required|exists:variations,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        $businessId = session('current_business_id');
        $locationId = session('current_location_id', 1);

        // Check cash register
        $register = $this->cashRegisterUtil->getCurrentCashRegister(Auth::id(), $locationId);
        if (!$register) {
            return back()->with('error', 'Please open a cash register first!');
        }

        DB::beginTransaction();

        try {
            // 1. Create transaction
            $transaction = $this->transactionUtil->createSellTransaction($request, $businessId);

            // 2. Create sell lines
            $request->merge(['location_id' => $locationId]);
            $finalTotal = $this->transactionUtil->createOrUpdateSellLines($request, $businessId, $transaction->id);

            // 3. Create payments
            $this->transactionUtil->createOrUpdatePaymentLines($request, $businessId, $transaction->id);

            // 4. Decrease stock
            $this->productUtil->updateProductStock($transaction->id, 'sell', 'final');

            // 5. Add to cash register
            $this->cashRegisterUtil->addSellPayments($transaction->id);

            // 6. Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id);

            // 7. Update contact balance
            $totalPaid = $this->transactionUtil->getTotalPaid($transaction->id);
            $due = $finalTotal - $totalPaid;
            if ($due > 0) {
                $this->transactionUtil->updateContactBalance($request->contact_id, $due, 'add');
            }

            DB::commit();

            // Return receipt data
            if ($request->input('print_receipt')) {
                $receipt = $this->transactionUtil->getReceiptDetails($transaction->id);
                return view('pos.receipt', $receipt);
            }

            return redirect()->route('pos.index')
                ->with('success', "Sale completed! Invoice: {$transaction->invoice_no}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions()
    {
        $businessId = session('current_business_id');
        $locationId = session('current_location_id', 1);

        $transactions = Transaction::with(['contact'])
            ->where('business_id', $businessId)
            ->where('location_id', $locationId)
            ->where('type', 'sell')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($transactions);
    }

    /**
     * Get product details for POS
     */
    public function getProductRow($variationId)
    {
        $variation = Variation::with(['product', 'productVariation'])
            ->findOrFail($variationId);

        $locationId = session('current_location_id', 1);
        $stock = $this->productUtil->getCurrentStock($variationId, $locationId);

        return response()->json([
            'variation_id' => $variation->id,
            'product_id' => $variation->product_id,
            'product_name' => $variation->product->name,
            'variation_name' => $variation->full_name,
            'sku' => $variation->sub_sku ?? $variation->product->sku,
            'unit_price' => $variation->default_sell_price,
            'stock' => $stock,
            'tax_id' => $variation->product->tax_id,
        ]);
    }

    /**
     * Search products by barcode
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $businessId = session('current_business_id');

        $variation = Variation::whereHas('product', function ($q) use ($barcode, $businessId) {
            $q->where('business_id', $businessId)
              ->where('sku', $barcode);
        })->orWhere('sub_sku', $barcode)
          ->first();

        if (!$variation) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return $this->getProductRow($variation->id);
    }

    /**
     * Get payment row for modal
     */
    public function getPaymentRow(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $transaction = Transaction::findOrFail($transactionId);

        return response()->json([
            'final_total' => $transaction->final_total,
            'total_paid' => $transaction->payments->sum('amount'),
            'due' => $transaction->final_total - $transaction->payments->sum('amount'),
        ]);
    }
}
