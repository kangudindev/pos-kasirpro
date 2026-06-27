<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'businesses';

    protected $fillable = [
        'name',
        'owner_id',
        'currency_id',
        'logo',
        'tax_number',
        'tax_label',
        'timezone',
        'fy_start_month',
        'accounting_method',
        'default_profit_percent',
        'sell_price_tax',
        'sku_prefix',
        'pos_settings',
        'enabled_modules',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'pos_settings' => 'array',
        'enabled_modules' => 'array',
        'settings' => 'array',
        'default_profit_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function locations()
    {
        return $this->hasMany(BusinessLocation::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getDefaultLocation()
    {
        return $this->locations()->where('is_default', true)->first();
    }

    public function getSetting($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function getPosSetting($key, $default = null)
    {
        return data_get($this->pos_settings, $key, $default);
    }
}
