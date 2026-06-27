@extends('layout.mainlayout')
@section('title', 'Product Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $product->name }}</h4>
    <div>
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th style="width:150px">SKU</th><td><code>{{ $product->sku }}</code></td></tr>
                    <tr><th>Category</th><td>{{ $product->category->name ?? '-' }}</td></tr>
                    <tr><th>Brand</th><td>{{ $product->brand->name ?? '-' }}</td></tr>
                    <tr><th>Unit</th><td>{{ $product->unit->short_name ?? '-' }}</td></tr>
                    <tr><th>Status</th><td>{!! $product->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">Variations</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>SKU</th><th>Price</th><th>Stock</th></tr></thead>
                    <tbody>
                        @forelse($product->variations as $variation)
                        <tr><td><code>{{ $variation->sub_sku ?? $product->sku }}</code></td><td>Rp {{ number_format($variation->default_sell_price, 0, ',', '.') }}</td><td>-</td></tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center">No variations</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div></div>
@endsection


