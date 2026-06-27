@extends('layout.mainlayout')
@section('title', 'Product Stock')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Stock: {{ $product->name }}</h4>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Location</th><th>SKU</th><th>Quantity</th></tr>
                </thead>
                <tbody>
                    @forelse($product->sellableVariations as $var)
                        @foreach($var->locationDetails as $detail)
                        <tr>
                            <td>{{ $detail->location->name ?? '-' }}</td>
                            <td><code>{{ $var->sub_sku ?? $product->sku }}</code></td>
                            <td>{{ $detail->qty_available }}</td>
                        </tr>
                        @endforeach
                        @if($var->locationDetails->isEmpty())
                        <tr><td colspan="3" class="text-muted text-center">No stock data</td></tr>
                        @endif
                    @empty
                    <tr><td colspan="3" class="text-muted text-center">No variations</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div></div>
@endsection


