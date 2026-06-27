<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model
{
    use HasFactory;

    protected $table = 'transaction_payments';

    protected $fillable = [
        'transaction_id',
        'amount',
        'method',
        'payment_type',
        'card_transaction_number',
        'card_number',
        'card_type',
        'card_holder_name',
        'card_month',
        'card_year',
        'card_security',
        'cheque_number',
        'bank_account_number',
        'bank_account_name',
        'payment_link',
        'payment_link_expiry',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'payment_link_expiry' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getMethodLabelAttribute()
    {
        return match ($this->method) {
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'cheque' => 'Cek',
            'bank_transfer' => 'Transfer Bank',
            'other' => 'Lainnya',
            default => $this->method,
        };
    }
}
