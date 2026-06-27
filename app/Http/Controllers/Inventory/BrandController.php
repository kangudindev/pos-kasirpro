<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $businessId = session('current_business_id');
        $brands = Brand::where('business_id', $businessId)->latest()->paginate(20);

        return view('brand.index', compact('brands'));
    }

    public function create()
    {
        return view('brand.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Brand::create([
            'business_id' => session('current_business_id'),
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->route('brands.index')->with('success', 'Brand created!');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('brand.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $brand = Brand::findOrFail($id);
        $brand->update(['name' => $request->name, 'slug' => \Illuminate\Support\Str::slug($request->name)]);

        return redirect()->route('brands.index')->with('success', 'Brand updated!');
    }

    public function destroy($id)
    {
        Brand::findOrFail($id)->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted!');
    }
}
