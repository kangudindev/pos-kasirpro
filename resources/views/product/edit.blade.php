@extends('layout.mainlayout')
@section('title', 'Edit Product')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Product: {{ $product->name }}</h4>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Name</label>
                    <input type="text" class="form-control" name="name" value="{{ $product->name }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">-- Select --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($product->category_id==$cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Brand</label>
                    <select class="form-select" name="brand_id">
                        <option value="">-- Select --</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected($product->brand_id==$brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <select class="form-select" name="unit_id" required>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected($product->unit_id==$unit->id)>{{ $unit->short_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="is_active">
                        <option value="1" @selected($product->is_active)>Active</option>
                        <option value="0" @selected(!$product->is_active)>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection


