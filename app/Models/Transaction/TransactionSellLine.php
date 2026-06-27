<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionSellLine extends Model
{
    use HasFactory;

    protected $table = 'transaction_sell_lines';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'unit_price_inc_tax',
        'item_tax',
        'tax_id',
        'discount',
        'unit_price_before_discount',
        'sell_line_note',
        'parent_sell_line_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'unit_price_inc_tax' => 'decimal:4',
        'item_tax' => 'decimal:4',
        'unit_price_before_discount' => 'decimal:4',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product\Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(\App\Models\Product\Variation::class);
    }

    public function getLineTotal()
    {
        return $this->quantity * $this->unit_price;
    }
}
