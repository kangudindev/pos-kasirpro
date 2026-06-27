<?php

namespace App\Utils;

use App\Models\Contact\Contact;
use App\Models\Business\BusinessLocation;

class ContactUtil extends Util
{
    /**
     * Get walk-in customer
     */
    public function getWalkInCustomer($businessId)
    {
        return Contact::firstOrCreate(
            [
                'business_id' => $businessId,
                'name' => 'Walk-in Customer',
                'type' => 'customer',
            ],
            [
                'mobile' => '0000000000',
                'email' => 'walkin@example.com',
                'contact_status' => 'active',
                'is_default' => true,
                'created_by' => 1,
            ]
        );
    }

    /**
     * Get customer group
     */
    public function getCustomerGroup($groupId)
    {
        return DB::table('customer_groups')->find($groupId);
    }

    /**
     * Get contact info with totals
     */
    public function getContactInfo($contactId)
    {
        $contact = Contact::findOrFail($contactId);

        $totalPurchases = $contact->purchaseTransactions()->sum('final_total');
        $totalSales = $contact->sellTransactions()->sum('final_total');

        return [
            'contact' => $contact,
            'total_purchases' => $totalPurchases,
            'total_sales' => $totalSales,
            'balance' => $contact->balance,
        ];
    }

    /**
     * Create new contact
     */
    public function createNewContact($request, $businessId)
    {
        $contact = Contact::create([
            'business_id' => $businessId,
            'type' => $request->input('type', 'customer'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'mobile' => $request->input('mobile'),
            'phone' => $request->input('phone'),
            'tax_number' => $request->input('tax_number'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'zip_code' => $request->input('zip_code'),
            'shipping_address' => $request->input('shipping_address'),
            'credit_limit' => $request->input('credit_limit'),
            'pay_term_number' => $request->input('pay_term_number'),
            'pay_term_type' => $request->input('pay_term_type'),
            'customer_group_id' => $request->input('customer_group_id'),
            'contact_status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return $contact;
    }
}
