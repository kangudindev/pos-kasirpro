<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'variations';

    protected $fillable = [
        'name',
        'product_id',
        'sub_sku',
        'product_variation_id',
        'default_purchase_price',
        'dpp_inc_tax',
        'profit_percent',
        'default_sell_price',
        'sell_price_inc_tax',
        'is_active',
    ];

    protected $casts = [
        'default_purchase_price' => 'decimal:4',
        'dpp_inc_tax' => 'decimal:4',
        'profit_percent' => 'decimal:2',
        'default_sell_price' => 'decimal:4',
        'sell_price_inc_tax' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function locationDetails()
    {
        return $this->hasMany(VariationLocationDetail::class);
    }

    public function groupPrices()
    {
        return $this->hasMany(VariationGroupPrice::class);
    }

    // Helpers
    public function getFullNameAttribute()
    {
        $parts = [];
        if ($this->productVariation && !$this->productVariation->is_dummy) {
            $parts[] = $this->productVariation->name;
        }
        $parts[] = $this->name;
        return implode(' - ', $parts);
    }

    public function getStockAtLocation($locationId)
    {
        $detail = $this->locationDetails()->where('location_id', $locationId)->first();
        return $detail ? $detail->qty_available : 0;
    }

    public function getSkuAttribute()
    {
        return $this->attributes['sub_sku'] ?? $this->product->sku;
    }
}
