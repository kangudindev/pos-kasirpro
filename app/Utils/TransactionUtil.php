<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionSellLine;
use App\Models\Transaction\PurchaseLine;
use App\Models\Transaction\TransactionPayment;
use App\Models\Transaction\StockAdjustmentLine;
use App\Models\Contact\Contact;

class TransactionUtil extends Util
{
    protected $productUtil;

    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    /**
     * Create sell transaction
     */
    public function createSellTransaction($request, $businessId)
    {
        $transaction = Transaction::create([
            'business_id' => $businessId,
            'type' => 'sell',
            'sub_type' => $request->input('sub_type'),
            'status' => $request->input('status', 'final'),
            'payment_status' => 'due',
            'contact_id' => $request->input('contact_id', 1),
            'customer_group_id' => $request->input('customer_group_id'),
            'invoice_no' => self::generateReferenceNumber('sell', $businessId),
            'ref_no' => $request->input('ref_no'),
            'transaction_date' => $request->input('transaction_date', now()),
            'total_before_tax' => 0,
            'tax_id' => $request->input('tax_id'),
            'tax_amount' => 0,
            'discount_type' => $request->input('discount_type'),
            'discount_amount' => $request->input('discount_amount', 0),
            'shipping_charges' => $request->input('shipping_charges', 0),
            'additional_notes' => $request->input('additional_notes'),
            'staff_note' => $request->input('staff_note'),
            'final_total' => 0,
            'created_by' => Auth::id(),
            'location_id' => $request->input('location_id'),
            'selling_price_group_id' => $request->input('selling_price_group_id'),
        ]);

        return $transaction;
    }

    /**
     * Update sell transaction
     */
    public function updateSellTransaction($request, $businessId, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->update([
            'contact_id' => $request->input('contact_id'),
            'customer_group_id' => $request->input('customer_group_id'),
            'transaction_date' => $request->input('transaction_date'),
            'discount_type' => $request->input('discount_type'),
            'discount_amount' => $request->input('discount_amount', 0),
            'shipping_charges' => $request->input('shipping_charges', 0),
            'additional_notes' => $request->input('additional_notes'),
            'staff_note' => $request->input('staff_note'),
            'status' => $request->input('status'),
        ]);

        return $transaction;
    }

    /**
     * Create or update sell lines
     */
    public function createOrUpdateSellLines($request, $businessId, $transactionId)
    {
        $products = $request->input('products', []);

        // Delete existing lines
        TransactionSellLine::where('transaction_id', $transactionId)->delete();

        $totalBeforeTax = 0;
        $totalTax = 0;

        foreach ($products as $item) {
            $variation = \App\Models\Product\Variation::find($item['variation_id']);
            if (!$variation) continue;

            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? $variation->default_sell_price;
            $lineTotal = $quantity * $unitPrice;

            $taxAmount = 0;
            if ($item['tax_id'] ?? null) {
                $taxRate = DB::table('tax_rates')->find($item['tax_id']);
                if ($taxRate) {
                    $taxAmount = ($lineTotal * $taxRate->amount) / 100;
                }
            }

            TransactionSellLine::create([
                'transaction_id' => $transactionId,
                'product_id' => $variation->product_id,
                'variation_id' => $item['variation_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_price_inc_tax' => $unitPrice + ($taxAmount / $quantity),
                'item_tax' => $taxAmount,
                'tax_id' => $item['tax_id'] ?? null,
                'discount' => $item['discount'] ?? null,
                'sell_line_note' => $item['note'] ?? null,
            ]);

            $totalBeforeTax += $lineTotal;
            $totalTax += $taxAmount;
        }

        // Update transaction totals
        $discountAmount = Transaction::find($transactionId)->discount_amount ?? 0;
        $shippingCharges = Transaction::find($transactionId)->shipping_charges ?? 0;
        $finalTotal = $totalBeforeTax + $totalTax - $discountAmount + $shippingCharges;

        Transaction::where('id', $transactionId)->update([
            'total_before_tax' => $totalBeforeTax,
            'tax_amount' => $totalTax,
            'final_total' => $finalTotal,
        ]);

        return $finalTotal;
    }

    /**
     * Create or update payment lines
     */
    public function createOrUpdatePaymentLines($request, $businessId, $transactionId)
    {
        $payments = $request->input('payments', []);

        // Delete existing payments
        TransactionPayment::where('transaction_id', $transactionId)->delete();

        $totalPaid = 0;

        foreach ($payments as $payment) {
            if (empty($payment['amount']) || $payment['amount'] <= 0) continue;

            TransactionPayment::create([
                'transaction_id' => $transactionId,
                'amount' => $payment['amount'],
                'method' => $payment['method'] ?? 'cash',
                'payment_type' => $payment['payment_type'] ?? null,
                'card_transaction_number' => $payment['card_transaction_number'] ?? null,
                'card_number' => $payment['card_number'] ?? null,
                'card_type' => $payment['card_type'] ?? null,
                'card_holder_name' => $payment['card_holder_name'] ?? null,
                'cheque_number' => $payment['cheque_number'] ?? null,
                'bank_account_number' => $payment['bank_account_number'] ?? null,
                'note' => $payment['note'] ?? null,
            ]);

            $totalPaid += $payment['amount'];
        }

        return $totalPaid;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $totalPaid = TransactionPayment::where('transaction_id', $transactionId)->sum('amount');

        $paymentStatus = $totalPaid >= $transaction->final_total ? 'paid' : 'due';

        $transaction->update(['payment_status' => $paymentStatus]);

        return $paymentStatus;
    }

    /**
     * Adjust quantity at location
     */
    public function adjustQuantity($variationId, $locationId, $quantityAdjustment)
    {
        return $this->productUtil->updateProductQuantity(
            $variationId,
            $locationId,
            $this->productUtil->getCurrentStock($variationId, $locationId) + $quantityAdjustment
        );
    }

    /**
     * Get receipt details
     */
    public function getReceiptDetails($transactionId, $layoutId = null)
    {
        $transaction = Transaction::with(['contact', 'location', 'createdBy'])
            ->findOrFail($transactionId);

        $lines = TransactionSellLine::with(['product', 'variation'])
            ->where('transaction_id', $transactionId)
            ->get();

        $payments = TransactionPayment::where('transaction_id', $transactionId)->get();

        return [
            'transaction' => $transaction,
            'lines' => $lines,
            'payments' => $payments,
            'business' => session('current_business'),
        ];
    }

    /**
     * Get invoice number
     */
    public function getInvoiceNumber($businessId, $locationId, $schemeId)
    {
        return self::generateReferenceNumber('sell', $businessId);
    }

    /**
     * Create expense
     */
    public function createExpense($request, $businessId)
    {
        $transaction = Transaction::create([
            'business_id' => $businessId,
            'type' => 'expense',
            'status' => 'final',
            'contact_id' => $request->input('contact_id'),
            'ref_no' => self::generateReferenceNumber('expense', $businessId),
            'transaction_date' => $request->input('transaction_date'),
            'total_before_tax' => $request->input('amount'),
            'tax_amount' => $request->input('tax_amount', 0),
            'final_total' => $request->input('amount'),
            'additional_notes' => $request->input('description'),
            'created_by' => Auth::id(),
            'location_id' => $request->input('location_id'),
        ]);

        return $transaction;
    }

    /**
     * Create stock adjustment
     */
    public function createStockAdjustment($request, $businessId)
    {
        $transaction = Transaction::create([
            'business_id' => $businessId,
            'type' => 'stock_adjustment',
            'adjustment_type' => $request->input('adjustment_type', 'normal'),
            'status' => 'final',
            'contact_id' => $request->input('contact_id', 1),
            'ref_no' => self::generateReferenceNumber('stock_adjustment', $businessId),
            'transaction_date' => $request->input('transaction_date', now()),
            'final_total' => 0,
            'created_by' => Auth::id(),
            'location_id' => $request->input('location_id'),
        ]);

        // Create adjustment lines
        $products = $request->input('products', []);
        foreach ($products as $item) {
            StockAdjustmentLine::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'] ?? null,
            ]);

            // Adjust stock
            $adjustment = $request->input('adjustment_type', 'normal');
            $qty = $adjustment == 'normal' ? $item['quantity'] : -$item['quantity'];
            $this->productUtil->increaseProductQuantity(
                $item['variation_id'],
                $request->input('location_id'),
                $qty
            );
        }

        return $transaction;
    }

    /**
     * Update contact balance
     */
    public function updateContactBalance($contactId, $amount, $type = 'add')
    {
        $contact = Contact::findOrFail($contactId);

        if ($type == 'add') {
            $contact->balance += $amount;
        } else {
            $contact->balance -= $amount;
        }

        $contact->save();

        return $contact;
    }

    /**
     * Get total paid for transaction
     */
    public function getTotalPaid($transactionId)
    {
        return TransactionPayment::where('transaction_id', $transactionId)->sum('amount');
    }

    /**
     * Map purchase-sell (FIFO/LIFO)
     */
    public function mapPurchaseSell($transactionId, $transactionType)
    {
        // FIFO/LIFO mapping logic
        // This is a simplified version
        return true;
    }
}
