<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Business\Business;
use App\Enums\ContactType;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contacts';

    protected $fillable = [
        'business_id',
        'type',
        'supplier_business_name',
        'name',
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'tax_number',
        'city',
        'state',
        'country',
        'address',
        'shipping_address',
        'mobile',
        'landline',
        'alternate_number',
        'pay_term_number',
        'pay_term_type',
        'credit_limit',
        'balance',
        'customer_group_id',
        'contact_status',
        'contact_id',
        'created_by',
        'is_default',
        'custom_field_1',
        'custom_field_2',
        'custom_field_3',
        'custom_field_4',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:4',
        'balance' => 'decimal:4',
        'is_default' => 'boolean',
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

    public function customerGroup()
    {
        return $this->belongsTo(\App\Models\Settings\CustomerGroup::class);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction\Transaction::class);
    }

    public function sellTransactions()
    {
        return $this->transactions()->where('type', 'sell');
    }

    public function purchaseTransactions()
    {
        return $this->transactions()->where('type', 'purchase');
    }

    // Scopes
    public function scopeCustomers($query)
    {
        return $query->where('type', ContactType::CUSTOMER)
            ->orWhere('type', ContactType::BOTH);
    }

    public function scopeSuppliers($query)
    {
        return $query->where('type', ContactType::SUPPLIER)
            ->orWhere('type', ContactType::BOTH);
    }

    public function scopeActive($query)
    {
        return $query->where('contact_status', 'active');
    }

    // Helpers
    public function isCustomer()
    {
        return in_array($this->type, [ContactType::CUSTOMER, ContactType::BOTH]);
    }

    public function isSupplier()
    {
        return in_array($this->type, [ContactType::SUPPLIER, ContactType::BOTH]);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name) ?: $this->name;
    }

    public function getTotalPurchases()
    {
        return $this->purchaseTransactions()->sum('final_total');
    }

    public function getTotalSales()
    {
        return $this->sellTransactions()->sum('final_total');
    }
}
