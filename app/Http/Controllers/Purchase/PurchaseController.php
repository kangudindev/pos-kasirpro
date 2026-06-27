<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Product;
use App\Models\Contact\Contact;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\PurchaseLine;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use App\Utils\Util;

class PurchaseController extends Controller
{
    protected $transactionUtil;
    protected $productUtil;

    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    public function index(Request $request)
    {
        $businessId = session('current_business_id');

        $query = Transaction::with(['contact', 'createdBy'])
            ->where('business_id', $businessId)
            ->where('type', 'purchase');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        $purchases = $query->latest()->paginate(20);

        return view('purchase.index', compact('purchases'));
    }

    public function create()
    {
        $businessId = session('current_business_id');

        $suppliers = Contact::where('business_id', $businessId)->suppliers()->get();
        $products = Product::where('business_id', $businessId)->active()->get();
        $locations = \App\Models\Business\BusinessLocation::where('business_id', $businessId)->get();

        return view('purchase.create', compact('suppliers', 'products', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.variation_id' => 'required|exists:variations,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $businessId = session('current_business_id');
        $locationId = $request->input('location_id', 1);

        DB::beginTransaction();

        try {
            // Create transaction
            $transaction = Transaction::create([
                'business_id' => $businessId,
                'type' => 'purchase',
                'status' => 'received',
                'payment_status' => 'due',
                'contact_id' => $request->contact_id,
                'ref_no' => Util::generateReferenceNumber('purchase', $businessId),
                'transaction_date' => $request->input('transaction_date', now()),
                'total_before_tax' => 0,
                'tax_amount' => 0,
                'discount_amount' => $request->input('discount_amount', 0),
                'shipping_charges' => $request->input('shipping_charges', 0),
                'final_total' => 0,
                'created_by' => Auth::id(),
                'location_id' => $locationId,
            ]);

            // Create purchase lines
            $totalBeforeTax = 0;
            $totalTax = 0;

            foreach ($request->products as $item) {
                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $lineTotal = $quantity * $unitCost;

                $taxAmount = 0;
                if ($item['tax_id'] ?? null) {
                    $taxRate = \Illuminate\Support\Facades\DB::table('tax_rates')->find($item['tax_id']);
                    if ($taxRate) {
                        $taxAmount = ($lineTotal * $taxRate->amount) / 100;
                    }
                }

                PurchaseLine::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'quantity' => $quantity,
                    'quantity_received' => $quantity,
                    'purchase_price' => $unitCost,
                    'purchase_price_inc_tax' => $unitCost + ($taxAmount / $quantity),
                    'item_tax' => $taxAmount,
                    'tax_id' => $item['tax_id'] ?? null,
                ]);

                // Increase stock
                $this->productUtil->increaseProductQuantity(
                    $item['variation_id'],
                    $locationId,
                    $quantity
                );

                $totalBeforeTax += $lineTotal;
                $totalTax += $taxAmount;
            }

            // Update transaction totals
            $discountAmount = $transaction->discount_amount ?? 0;
            $shippingCharges = $transaction->shipping_charges ?? 0;
            $finalTotal = $totalBeforeTax + $totalTax - $discountAmount + $shippingCharges;

            $transaction->update([
                'total_before_tax' => $totalBeforeTax,
                'tax_amount' => $totalTax,
                'final_total' => $finalTotal,
            ]);

            // Create payment if any
            if ($request->input('paid_amount', 0) > 0) {
                \App\Models\Transaction\TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'amount' => $request->paid_amount,
                    'method' => $request->payment_method ?? 'cash',
                ]);

                $this->transactionUtil->updatePaymentStatus($transaction->id);

                // Update contact balance
                $this->transactionUtil->updateContactBalance(
                    $request->contact_id,
                    $request->paid_amount,
                    'subtract'
                );
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', "Purchase created! Ref: {$transaction->ref_no}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchase = Transaction::with(['contact', 'purchaseLines.product', 'payments'])
            ->findOrFail($id);

        return view('purchase.show', compact('purchase'));
    }

    public function edit($id)
    {
        $businessId = session('current_business_id');
        $purchase = Transaction::with('purchaseLines')->findOrFail($id);

        $suppliers = Contact::where('business_id', $businessId)->suppliers()->get();
        $products = Product::where('business_id', $businessId)->active()->get();
        $locations = \App\Models\Business\BusinessLocation::where('business_id', $businessId)->get();

        return view('purchase.edit', compact('purchase', 'suppliers', 'products', 'locations'));
    }

    public function update(Request $request, $id)
    {
        // Similar to store but for update
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->only([
            'contact_id', 'transaction_date', 'discount_amount',
            'shipping_charges', 'additional_notes',
        ]));

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated!');
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Reverse stock changes
        foreach ($transaction->purchaseLines as $line) {
            $this->productUtil->decreaseProductQuantity(
                $line->variation_id,
                $transaction->location_id,
                $line->quantity
            );
        }

        $transaction->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted!');
    }
}
