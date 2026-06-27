<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_variations';

    protected $fillable = [
        'business_id',
        'product_id',
        'name',
        'is_dummy',
    ];

    protected $casts = [
        'is_dummy' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variations()
    {
        return $this->hasMany(Variation::class);
    }
}
