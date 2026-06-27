<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    protected $fillable = [
        'business_id', 'name', 'amount', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
