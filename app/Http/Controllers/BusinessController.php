<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business\Business;
use App\Models\Business\BusinessLocation;
use App\Utils\BusinessUtil;

class BusinessController extends Controller
{
    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }

    public function index()
    {
        $businesses = Business::with(['currency', 'owner'])
            ->where('owner_id', Auth::id())
            ->get();

        return view('business.index', compact('businesses'));
    }

    public function create()
    {
        return view('business.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $business = $this->businessUtil->createNewBusiness($request, Auth::id());

        return redirect()->route('business.index')
            ->with('success', 'Business created successfully!');
    }

    public function show($id)
    {
        $business = $this->businessUtil->getDetails($id);

        return view('business.show', compact('business'));
    }

    public function edit($id)
    {
        $business = Business::findOrFail($id);

        return view('business.edit', compact('business'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $business = Business::findOrFail($id);
        $business->update($request->only([
            'name', 'tax_number', 'tax_label', 'timezone',
            'accounting_method', 'sell_price_tax', 'sku_prefix',
        ]));

        return redirect()->route('business.index')
            ->with('success', 'Business updated successfully!');
    }

    public function destroy($id)
    {
        $business = Business::findOrFail($id);
        $business->delete();

        return redirect()->route('business.index')
            ->with('success', 'Business deleted successfully!');
    }

    public function locations($businessId)
    {
        $business = Business::with('locations')->findOrFail($businessId);

        return view('business.locations', compact('business'));
    }

    public function storeLocation(Request $request, $businessId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
        ]);

        BusinessLocation::create([
            'business_id' => $businessId,
            'name' => $request->name,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'is_active' => true,
        ]);

        return redirect()->route('business.locations', $businessId)
            ->with('success', 'Location added successfully!');
    }
}
