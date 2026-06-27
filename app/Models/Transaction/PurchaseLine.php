<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseLine extends Model
{
    use HasFactory;

    protected $table = 'purchase_lines';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'variation_id',
        'quantity',
        'quantity_received',
        'quantity_sold',
        'quantity_adjusted',
        'quantity_returned',
        'purchase_price',
        'purchase_price_inc_tax',
        'item_tax',
        'tax_id',
        'is_completed',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'quantity_sold' => 'decimal:4',
        'quantity_adjusted' => 'decimal:4',
        'quantity_returned' => 'decimal:4',
        'purchase_price' => 'decimal:4',
        'purchase_price_inc_tax' => 'decimal:4',
        'item_tax' => 'decimal:4',
        'is_completed' => 'boolean',
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

    public function getQuantityRemaining()
    {
        return $this->quantity
            - $this->quantity_sold
            - $this->quantity_adjusted
            - $this->quantity_returned;
    }
}
