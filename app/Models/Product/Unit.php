<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'actual_name', 'short_name', 'allow_decimal',
        'base_unit_id', 'multiplier', 'created_by', 'is_active',
    ];

    protected $casts = [
        'allow_decimal' => 'boolean',
        'multiplier' => 'decimal:3',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function subUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
