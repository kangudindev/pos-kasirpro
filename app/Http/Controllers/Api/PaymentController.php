<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected MidtransService $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function createTransaction(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
        ]);

        $transaction = DB::table('transactions')
            ->where('id', $request->transaction_id)
            ->where('business_id', auth()->user()->business_id)
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($transaction->payment_status === 'paid') {
            return response()->json(['error' => 'Transaction already paid'], 400);
        }

        $customer = [];
        if ($transaction->contact_id) {
            $contact = DB::table('contacts')->find($transaction->contact_id);
            $customer = [
                'name' => $contact->name ?? '',
                'email' => $contact->email ?? '',
                'phone' => $contact->mobile ?? '',
            ];
        }

        $orderId = 'TXN-' . $transaction->id . '-' . time();

        $result = $this->midtrans->createTransaction($orderId, $transaction->final_total, $customer);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 500);
        }

        DB::table('transaction_payments')->insert([
            'transaction_id' => $transaction->id,
            'amount' => $transaction->final_total,
            'method' => 'midtrans',
            'payment_ref' => $orderId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transactions')
            ->where('id', $transaction->id)
            ->update([
                'payment_ref' => $orderId,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'token' => $result['token'],
            'redirect_url' => $result['redirect_url'],
            'client_key' => $this->midtrans->getClientKey(),
            'is_production' => $this->midtrans->isProduction(),
        ]);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        if (!$this->midtrans->validateSignature($data)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $orderId = $data['order_id'] ?? null;
        $transactionStatus = $data['transaction_status'] ?? null;
        $paymentType = $data['payment_type'] ?? null;

        $payment = DB::table('transaction_payments')
            ->where('payment_ref', $orderId)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $transaction = DB::table('transactions')->where('id', $payment->transaction_id)->first();
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            DB::table('transaction_payments')
                ->where('id', $payment->id)
                ->update([
                    'paid_on' => now(),
                    'method' => $paymentType,
                    'note' => json_encode($data),
                    'updated_at' => now(),
                ]);

            DB::table('transactions')
                ->where('id', $transaction->id)
                ->update([
                    'payment_status' => 'paid',
                    'updated_at' => now(),
                ]);

            $totalPaid = DB::table('transaction_payments')
                ->where('transaction_id', $transaction->id)
                ->whereNotNull('paid_on')
                ->sum('amount');

            if ($totalPaid >= $transaction->final_total) {
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['status' => 'final']);
            }
        } elseif ($transactionStatus === 'pending') {
            // Payment pending, no action needed
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            DB::table('transactions')
                ->where('id', $transaction->id)
                ->update([
                    'payment_status' => 'failed',
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function checkStatus(Request $request)
    {
        $request->validate(['order_id' => 'required']);

        $result = $this->midtrans->getStatus($request->order_id);

        return response()->json($result);
    }
}
