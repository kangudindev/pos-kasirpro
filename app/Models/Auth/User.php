<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Business\Business;
use App\Models\Business\BusinessLocation;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'business_id',
        'role_id',
        'first_name',
        'last_name',
        'username',
        'name',
        'email',
        'phone',
        'avatar',
        'language',
        'is_active',
        'user_type',
        'max_sale_discount',
        'is_commission_agent',
        'commission_percent',
        'created_by',
        'dob',
        'gender',
        'bank_details',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_commission_agent' => 'boolean',
        'max_sale_discount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'dob' => 'date',
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

    public function assignedLocations()
    {
        return $this->belongsToMany(BusinessLocation::class, 'user_location_access');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCashiers($query)
    {
        return $query->where('user_type', 'user');
    }

    public function scopeCommissionAgents($query)
    {
        return $query->where('user_type', 'sales_commission_agent');
    }

    // Helpers
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->name;
    }

    public function getGuardName(): string
    {
        return 'web';
    }

    public function permittedLocations()
    {
        if ($this->hasRole('admin') || $this->hasRole('superadmin')) {
            return BusinessLocation::where('business_id', $this->business_id)->get();
        }

        return $this->assignedLocations()->where('business_id', $this->business_id)->get();
    }

    public function canAccessThisLocation($locationId)
    {
        if ($this->hasRole('admin') || $this->hasRole('superadmin')) {
            return true;
        }

        return $this->assignedLocations()->where('business_location_id', $locationId)->exists();
    }
}
