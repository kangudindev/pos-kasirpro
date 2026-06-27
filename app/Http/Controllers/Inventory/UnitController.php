<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Unit;

class UnitController extends Controller
{
    public function index()
    {
        $businessId = session('current_business_id');
        $units = Unit::where('business_id', $businessId)->latest()->paginate(20);

        return view('unit.index', compact('units'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $baseUnits = Unit::where('business_id', $businessId)->whereNull('base_unit_id')->get();

        return view('unit.create', compact('baseUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'actual_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
        ]);

        Unit::create([
            'business_id' => session('current_business_id'),
            'actual_name' => $request->actual_name,
            'short_name' => $request->short_name,
            'allow_decimal' => $request->has('allow_decimal'),
            'base_unit_id' => $request->base_unit_id,
            'multiplier' => $request->multiplier ?? 1,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit created!');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $businessId = session('current_business_id');
        $baseUnits = Unit::where('business_id', $businessId)->whereNull('base_unit_id')->where('id', '!=', $id)->get();

        return view('unit.edit', compact('unit', 'baseUnits'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'actual_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update([
            'actual_name' => $request->actual_name,
            'short_name' => $request->short_name,
            'allow_decimal' => $request->has('allow_decimal'),
            'base_unit_id' => $request->base_unit_id,
            'multiplier' => $request->multiplier ?? 1,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated!');
    }

    public function destroy($id)
    {
        Unit::findOrFail($id)->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted!');
    }
}
