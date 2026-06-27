<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact\Contact;

class ContactApiController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'customer');
        $businessId = session('current_business_id');

        if (!$query) {
            return response()->json([]);
        }

        $contacts = Contact::where('business_id', $businessId)
            ->where('type', $type)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($contacts);
    }
}
