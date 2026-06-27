<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'business_locations';

    protected $fillable = [
        'business_id',
        'name',
        'landmark',
        'country',
        'state',
        'city',
        'zip_code',
        'mobile',
        'alternate_number',
        'email',
        'is_active',
        'is_default',
        'invoice_layout_id',
        'default_selling_price_group_id',
        'printer_type',
        'feature_products',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'feature_products' => 'array',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function invoiceLayout()
    {
        return $this->belongsTo(\App\Models\Settings\InvoiceLayout::class);
    }

    public function sellingPriceGroup()
    {
        return $this->belongsTo(\App\Models\Settings\SellingPriceGroup::class, 'default_selling_price_group_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getFullName()
    {
        return "{$this->name}, {$this->city}, {$this->state}";
    }
}
