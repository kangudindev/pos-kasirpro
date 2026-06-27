@extends('layout.mainlayout')
@section('title', 'Add Product')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Product</h4>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Name *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">SKU</label>
                    <input type="text" class="form-control" name="sku">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type *</label>
                    <select class="form-select" name="type" required>
                        <option value="single">Single</option>
                        <option value="variable">Variable</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">-- Select --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Brand</label>
                    <select class="form-select" name="brand_id">
                        <option value="">-- Select --</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit *</label>
                    <select class="form-select" name="unit_id" required>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cost Price</label>
                    <input type="number" class="form-control" name="cost_price" step="0.01">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Selling Price *</label>
                    <input type="number" class="form-control" name="selling_price" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantity Alert</label>
                    <input type="number" class="form-control" name="alert_quantity">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection


