<?php

namespace App\Utils;

use App\Models\Business\Business;
use App\Models\Business\BusinessLocation;
use App\Models\Auth\User;

class BusinessUtil extends Util
{
    /**
     * Create new business with default resources
     */
    public function createNewBusiness($request, $ownerId)
    {
        $business = Business::create([
            'name' => $request->input('name'),
            'owner_id' => $ownerId,
            'currency_id' => $request->input('currency_id', 1),
            'timezone' => $request->input('timezone', 'Asia/Jakarta'),
            'accounting_method' => 'fifo',
            'sell_price_tax' => 'excludes',
            'is_active' => true,
        ]);

        // Create default location
        BusinessLocation::create([
            'business_id' => $business->id,
            'name' => 'Main Location',
            'country' => 'Indonesia',
            'state' => 'DKI Jakarta',
            'city' => 'Jakarta',
            'zip_code' => '10000',
            'is_active' => true,
            'is_default' => true,
        ]);

        // Assign owner to business
        User::where('id', $ownerId)->update(['business_id' => $business->id]);

        return $business;
    }

    /**
     * Get business details
     */
    public function getDetails($businessId)
    {
        return Business::with(['currency', 'locations', 'owner'])
            ->find($businessId);
    }

    /**
     * Get all currencies
     */
    public function allCurrencies()
    {
        return DB::table('currencies')->get();
    }

    /**
     * Get all timezones
     */
    public function allTimeZones()
    {
        return DateTimeZone::listIdentifiers();
    }

    /**
     * Get accounting methods
     */
    public function allAccountingMethods()
    {
        return [
            'fifo' => 'FIFO (First In First Out)',
            'lifo' => 'LIFO (Last In First Out)',
            'avco' => 'AVCO (Average Cost)',
        ];
    }
}
