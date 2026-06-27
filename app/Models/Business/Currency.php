<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'symbol',
        'code',
        'thousand_separator',
        'decimal_separator',
        'exchange_rate',
        'is_default',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_default' => 'boolean',
    ];

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helpers
    public function formatAmount($amount)
    {
        return $this->symbol . ' ' . number_format($amount, 2, $this->decimal_separator, $this->thousand_separator);
    }
}
