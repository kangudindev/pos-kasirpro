<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $businessId = session('current_business_id');

        $categories = Category::with('children')
            ->where('business_id', $businessId)
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return view('category.index', compact('categories'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $parentCategories = Category::where('business_id', $businessId)
            ->whereNull('parent_id')
            ->get();

        return view('category.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'business_id' => session('current_business_id'),
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $businessId = session('current_business_id');
        $parentCategories = Category::where('business_id', $businessId)
            ->whereNull('parent_id')
            ->where('id', '!=', $id)
            ->get();

        return view('category.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'slug' => \Illuminate\Support\Str::slug($request->name),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
