<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction\Transaction;

class SellController extends Controller
{
    public function index(Request $request)
    {
        $businessId = session('current_business_id');

        $query = Transaction::with(['contact', 'createdBy'])
            ->where('business_id', $businessId)
            ->where('type', 'sell');

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $sells = $query->latest()->paginate(20);

        return view('sell.index', compact('sells'));
    }

    public function show($id)
    {
        $sell = Transaction::with(['contact', 'sellLines.product', 'sellLines.variation', 'payments', 'createdBy'])
            ->findOrFail($id);

        return view('sell.show', compact('sell'));
    }

    public function print($id)
    {
        $sell = Transaction::with(['contact', 'sellLines.product', 'sellLines.variation', 'payments', 'createdBy', 'location.business'])
            ->findOrFail($id);

        return view('sell.print', compact('sell'));
    }
}
