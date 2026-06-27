<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Utils\CashRegisterUtil;

class CashRegisterController extends Controller
{
    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    public function index()
    {
        $businessId = session('current_business_id');
        $locationId = session('current_location_id', 1);

        $registers = DB::table('cash_registers')
            ->where('business_id', $businessId)
            ->where('location_id', $locationId)
            ->latest()
            ->paginate(20);

        return view('finance.cash_register_index', compact('registers'));
    }

    public function open(Request $request)
    {
        $request->validate(['opening_amount' => 'required|numeric|min:0']);

        $businessId = session('current_business_id');
        $locationId = session('current_location_id', 1);

        $openCount = $this->cashRegisterUtil->countOpenedRegister(Auth::id(), $locationId);
        if ($openCount > 0) {
            return back()->with('error', 'You already have an open register!');
        }

        DB::table('cash_registers')->insert([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'user_id' => Auth::id(),
            'opening_amount' => $request->opening_amount,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('cash-registers.index')->with('success', 'Cash register opened!');
    }

    public function close(Request $request)
    {
        $request->validate(['closing_amount' => 'required|numeric|min:0']);

        $register = $this->cashRegisterUtil->getCurrentCashRegister(Auth::id(), session('current_location_id', 1));

        if (!$register) {
            return back()->with('error', 'No open register found!');
        }

        DB::table('cash_registers')
            ->where('id', $register->id)
            ->update([
                'closing_amount' => $request->closing_amount,
                'status' => 'close',
                'closed_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('cash-registers.index')->with('success', 'Cash register closed!');
    }

    public function show($id)
    {
        $details = $this->cashRegisterUtil->getRegisterDetails($id);
        return view('finance.cash_register_show', $details);
    }
}
