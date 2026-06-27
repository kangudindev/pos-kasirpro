@extends('layout.mainlayout')
@section('title', 'Stock Report')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Product</th><th>Category</th><th>Brand</th><th>Total Stock</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($stockData as $item)
        <tr>
            <td><strong>{{ $item['product']->name }}</strong></td>
            <td>{{ $item['product']->category->name ?? '-' }}</td>
            <td>{{ $item['product']->brand->name ?? '-' }}</td>
            <td>{{ $item['total_stock'] }}</td>
            <td>{!! $item['is_low'] ? '<span class="badge bg-danger">Low Stock</span>' : '<span class="badge bg-success">OK</span>' !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
@endsection


