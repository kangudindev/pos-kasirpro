<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Business\Business;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'sku',
        'barcode_type',
        'item_code',
        'type',
        'selling_type',
        'unit_id',
        'sub_unit_ids',
        'brand_id',
        'category_id',
        'sub_category_id',
        'tax_id',
        'tax_type',
        'enable_stock',
        'not_for_selling',
        'alert_quantity',
        'image',
        'product_description',
        'warranty_id',
        'is_active',
        'is_inactive',
        'created_by',
        'custom_field_1',
        'custom_field_2',
        'custom_field_3',
        'custom_field_4',
        'custom_field_5',
    ];

    protected $casts = [
        'enable_stock' => 'boolean',
        'not_for_selling' => 'boolean',
        'is_active' => 'boolean',
        'is_inactive' => 'boolean',
        'alert_quantity' => 'decimal:4',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function tax()
    {
        return $this->belongsTo(\App\Models\Settings\TaxRate::class, 'tax_id');
    }

    public function warranty()
    {
        return $this->belongsTo(\App\Models\Settings\Warranty::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function sellableVariations()
    {
        return $this->hasMany(Variation::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function locations()
    {
        return $this->belongsToMany(\App\Models\Business\BusinessLocation::class, 'product_locations');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_inactive', false);
    }

    public function scopeForSale($query)
    {
        return $query->active()->where('not_for_selling', false);
    }

    public function scopeForLocation($query, $locationId)
    {
        return $query->whereHas('locations', function ($q) use ($locationId) {
            $q->where('business_locations.id', $locationId);
        });
    }

    // Helpers
    public function isVariable()
    {
        return $this->type === 'variable';
    }

    public function isSingle()
    {
        return $this->type === 'single';
    }

    public function getStockAtLocation($locationId)
    {
        $variations = $this->sellableVariations;
        $totalStock = 0;

        foreach ($variations as $variation) {
            $stock = $variation->getStockAtLocation($locationId);
            $totalStock += $stock;
        }

        return $totalStock;
    }

    public function getSkuAttribute()
    {
        return $this->attributes['sku'] ?? $this->generateSku();
    }

    protected function generateSku()
    {
        $prefix = $this->business->sku_prefix ?? 'PRD';
        $nextId = self::where('business_id', $this->business_id)->max('id') + 1;
        return $prefix . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
