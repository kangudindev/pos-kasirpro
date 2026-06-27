<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationLocationDetail extends Model
{
    use HasFactory;

    protected $table = 'variation_location_details';

    protected $fillable = [
        'variation_id',
        'location_id',
        'qty_available',
    ];

    protected $casts = [
        'qty_available' => 'decimal:4',
    ];

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Business\BusinessLocation::class, 'location_id');
    }
}
