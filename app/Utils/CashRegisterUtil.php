<?php

namespace App\Utils;

use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionPayment;
use Illuminate\Support\Facades\DB;

class CashRegisterUtil extends Util
{
    /**
     * Count opened registers
     */
    public function countOpenedRegister($userId, $locationId)
    {
        return DB::table('cash_registers')
            ->where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('status', 'open')
            ->count();
    }

    /**
     * Get current open register
     */
    public function getCurrentCashRegister($userId, $locationId)
    {
        return DB::table('cash_registers')
            ->where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Add sell payments to register
     */
    public function addSellPayments($transactionId, $cashRegisterId = null)
    {
        $payments = TransactionPayment::where('transaction_id', $transactionId)->get();

        if (!$cashRegisterId) {
            $transaction = Transaction::find($transactionId);
            $register = $this->getCurrentCashRegister($transaction->created_by, $transaction->location_id);
            $cashRegisterId = $register ? $register->id : null;
        }

        if (!$cashRegisterId) return false;

        foreach ($payments as $payment) {
            DB::table('cash_register_transactions')->insert([
                'cash_register_id' => $cashRegisterId,
                'transaction_id' => $transactionId,
                'payment_id' => $payment->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Update sell payments
     */
    public function updateSellPayments($transactionId, $oldPayments = [])
    {
        // Remove old payments from register
        foreach ($oldPayments as $paymentId) {
            DB::table('cash_register_transactions')
                ->where('payment_id', $paymentId)
                ->delete();
        }

        // Add new payments
        $this->addSellPayments($transactionId);
    }

    /**
     * Refund sell
     */
    public function refundSell($transactionId, $amount, $method = 'cash')
    {
        $transaction = Transaction::find($transactionId);

        if (!$transaction) return false;

        // Create refund payment (negative)
        TransactionPayment::create([
            'transaction_id' => $transactionId,
            'amount' => -$amount,
            'method' => $method,
            'note' => 'Refund',
        ]);

        // Add to cash register
        $this->addSellPayments($transactionId);

        return true;
    }

    /**
     * Get register details
     */
    public function getRegisterDetails($registerId)
    {
        $register = DB::table('cash_registers')->find($registerId);

        if (!$register) return null;

        $transactions = DB::table('cash_register_transactions')
            ->where('cash_register_id', $registerId)
            ->with('transaction')
            ->get();

        $totalCash = $transactions->where('method', 'cash')->sum('amount');
        $totalCard = $transactions->where('method', 'card')->sum('amount');
        $totalOther = $transactions->where('method', '!=', 'cash')
            ->where('method', '!=', 'card')
            ->sum('amount');

        return [
            'register' => $register,
            'transactions' => $transactions,
            'total_cash' => $totalCash,
            'total_card' => $totalCard,
            'total_other' => $totalOther,
            'total_all' => $totalCash + $totalCard + $totalOther,
        ];
    }
}
