<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Business\Business;
use App\Models\Business\BusinessLocation;
use App\Models\Contact\Contact;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use App\Enums\PaymentStatus;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'business_id',
        'type',
        'sub_type',
        'status',
        'payment_status',
        'adjustment_type',
        'contact_id',
        'customer_group_id',
        'invoice_no',
        'ref_no',
        'source',
        'transaction_date',
        'total_before_tax',
        'tax_id',
        'tax_amount',
        'discount_type',
        'discount_amount',
        'shipping_details',
        'shipping_address',
        'shipping_status',
        'delivered_to',
        'shipping_charges',
        'additional_notes',
        'staff_note',
        'final_total',
        'round_off_amount',
        'exchange_rate',
        'created_by',
        'location_id',
        'commission_agent',
        'selling_price_group_id',
        'is_recurring',
        'recur_interval',
        'recur_interval_type',
        'recur_start_date',
        'recur_end_date',
        'invoice_token',
        'custom_field_1',
        'custom_field_2',
        'custom_field_3',
        'custom_field_4',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'total_before_tax' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'shipping_charges' => 'decimal:4',
        'final_total' => 'decimal:4',
        'round_off_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:4',
        'is_recurring' => 'boolean',
        'recur_start_date' => 'date',
        'recur_end_date' => 'date',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tax()
    {
        return $this->belongsTo(\App\Models\Settings\TaxRate::class, 'tax_id');
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public function sellLines()
    {
        return $this->hasMany(TransactionSellLine::class);
    }

    public function purchaseLines()
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function stockAdjustmentLines()
    {
        return $this->hasMany(StockAdjustmentLine::class);
    }

    // Scopes
    public function scopeSell($query)
    {
        return $query->where('type', TransactionType::SELL);
    }

    public function scopePurchase($query)
    {
        return $query->where('type', TransactionType::PURCHASE);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::EXPENSE);
    }

    public function scopeStockAdjustment($query)
    {
        return $query->where('type', TransactionType::STOCK_ADJUSTMENT);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', PaymentStatus::PAID);
    }

    public function scopeDue($query)
    {
        return $query->where('payment_status', PaymentStatus::DUE);
    }

    public function scopeForLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeForDateRange($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    // Helpers
    public function isSell()
    {
        return $this->type === TransactionType::SELL;
    }

    public function isPurchase()
    {
        return $this->type === TransactionType::PURCHASE;
    }

    public function getTotalPaid()
    {
        return $this->payments->sum('amount');
    }

    public function getDueAmount()
    {
        return $this->final_total - $this->getTotalPaid();
    }

    public function isFullyPaid()
    {
        return $this->getDueAmount() <= 0;
    }
}
