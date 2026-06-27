<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Business\Business;
use App\Models\Business\BusinessLocation;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionPayment;

class Util
{
    /**
     * Format number with thousand separator
     */
    public static function num_f($number, $symbol = '', $decimal = 2)
    {
        $business = session('current_business');
        $thousand = $business->currency->thousand_separator ?? '.';
        $decimal_sep = $business->currency->decimal_separator ?? ',';

        return $symbol . number_format($number, $decimal, $decimal_sep, $thousand);
    }

    /**
     * Unformat number (remove thousand separator)
     */
    public static function num_uf($number)
    {
        $business = session('current_business');
        $thousand = $business->currency->thousand_separator ?? '.';

        return (float) str_replace($thousand, '', $number);
    }

    /**
     * Calculate percentage of a number
     */
    public static function calc_percentage($number, $percent, $addition = true)
    {
        $result = ($number * $percent) / 100;

        return $addition ? $number + $result : $number - $result;
    }

    /**
     * Calculate reverse percentage (for tax extraction)
     */
    public static function calc_percentage_base($number, $percent)
    {
        if ($percent == 0) return $number;

        return ($number * 100) / (100 + $percent);
    }

    /**
     * Calculate profit margin percentage
     */
    public static function get_percent($base, $number)
    {
        if ($base == 0) return 0;

        return (($number - $base) / $base) * 100;
    }

    /**
     * Get payment method types
     */
    public static function payment_types()
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'cheque' => 'Cheque',
            'bank_transfer' => 'Bank Transfer',
            'other' => 'Other',
            'custom_pay_1' => 'Custom Payment 1',
            'custom_pay_2' => 'Custom Payment 2',
            'custom_pay_3' => 'Custom Payment 3',
            'custom_pay_4' => 'Custom Payment 4',
            'custom_pay_5' => 'Custom Payment 5',
        ];
    }

    /**
     * Get order statuses
     */
    public static function orderStatuses()
    {
        return [
            'received' => 'Received',
            'pending' => 'Pending',
            'ordered' => 'Ordered',
        ];
    }

    /**
     * Get shipping statuses
     */
    public static function shipping_statuses()
    {
        return [
            'ordered' => 'Ordered',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNumber($type, $businessId = null)
    {
        $businessId = $businessId ?? session('current_business_id');

        $counter = DB::table('reference_counters')
            ->where('business_id', $businessId)
            ->where('ref_type', $type)
            ->lockForUpdate()
            ->first();

        if (!$counter) {
            DB::table('reference_counters')->insert([
                'business_id' => $businessId,
                'ref_type' => $type,
                'ref_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count = 1;
        } else {
            $count = $counter->ref_count + 1;
            DB::table('reference_counters')
                ->where('id', $counter->id)
                ->update([
                    'ref_count' => $count,
                    'updated_at' => now(),
                ]);
        }

        $prefix = self::getRefPrefix($type);
        $year = date('Y');

        return $prefix . '-' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get reference prefix
     */
    protected static function getRefPrefix($type)
    {
        return match ($type) {
            'purchase' => 'PT',
            'sell' => 'SL',
            'sell_return' => 'SLR',
            'purchase_return' => 'PTR',
            'expense' => 'EX',
            'stock_adjustment' => 'SA',
            'stock_transfer' => 'ST',
            default => 'REF',
        };
    }

    /**
     * Format date
     */
    public static function format_date($date, $format = 'd M Y')
    {
        return $date ? date($format, strtotime($date)) : '';
    }

    /**
     * Format datetime
     */
    public static function format_datetime($datetime, $format = 'd M Y H:i A')
    {
        return $datetime ? date($format, strtotime($datetime)) : '';
    }

    /**
     * Upload file
     */
    public static function uploadFile($file, $path = 'uploads')
    {
        if (!$file) return null;

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $filename);

        return $path . '/' . $filename;
    }

    /**
     * Activity log
     */
    public static function activityLog($description, $subject = null, $properties = [])
    {
        activity()
            ->performedOn($subject)
            ->causedBy(Auth::user())
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Get contact due amount
     */
    public static function getContactDue($contactId)
    {
        $totalPurchases = Transaction::where('contact_id', $contactId)
            ->where('type', 'purchase')
            ->sum('final_total');

        $totalSells = Transaction::where('contact_id', $contactId)
            ->where('type', 'sell')
            ->sum('final_total');

        $totalPaid = TransactionPayment::whereIn('transaction_id', function ($query) use ($contactId) {
                $query->select('id')
                    ->from('transactions')
                    ->where('contact_id', $contactId);
            })
            ->sum('amount');

        return ($totalPurchases + $totalSells) - $totalPaid;
    }

    /**
     * Check if module is enabled
     */
    public static function isModuleEnabled($moduleName)
    {
        $business = session('current_business');
        if (!$business) return false;

        $enabledModules = $business->enabled_modules ?? [];
        return in_array($moduleName, $enabledModules);
    }

    /**
     * Get sub units for a unit
     */
    public static function getSubUnits($unitId)
    {
        return DB::table('units')
            ->where('base_unit_id', $unitId)
            ->get();
    }

    /**
     * Get multiplier between two units
     */
    public static function getMultiplierOf2Units($fromUnitId, $toUnitId)
    {
        $fromUnit = DB::table('units')->find($fromUnitId);
        $toUnit = DB::table('units')->find($toUnitId);

        if (!$fromUnit || !$toUnit) return 1;

        return $toUnit->multiplier / $fromUnit->multiplier;
    }

    /**
     * Get current business from session
     */
    public static function getBusiness()
    {
        return session('current_business');
    }

    /**
     * Get current business ID
     */
    public static function getBusinessId()
    {
        return session('current_business_id');
    }

    /**
     * Get default location
     */
    public static function getDefaultLocation()
    {
        $businessId = self::getBusinessId();
        return BusinessLocation::where('business_id', $businessId)
            ->where('is_default', true)
            ->first();
    }
}
