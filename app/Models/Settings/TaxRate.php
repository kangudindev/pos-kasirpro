<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'amount',
        'is_tax_group',
        'created_by',
        'tax_group_id',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'is_tax_group' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Models\Business\Business::class);
    }

    public static function getForBusiness($businessId)
    {
        return static::where('business_id', $businessId)
            ->orWhereNull('business_id')
            ->orderBy('name')
            ->get();
    }

    public function calculateTax($amount)
    {
        return ($amount * $this->amount) / 100;
    }

    public static function getDefaultPPN($businessId = null)
    {
        return static::firstOrCreate(
            ['business_id' => $businessId, 'name' => 'PPN 11%'],
            ['amount' => 11.00, 'created_by' => 1]
        );
    }
}
