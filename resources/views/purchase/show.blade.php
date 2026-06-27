@extends('layout.mainlayout')
@section('title', 'Purchase Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
    <h5>Purchase: <code>{{ $purchase->ref_no }}</code></h5>
    <p><strong>Supplier:</strong> {{ $purchase->contact->name ?? '-' }}<br>
    <strong>Date:</strong> {{ \App\Utils\Util::format_date($purchase->transaction_date) }}<br>
    <strong>Status:</strong> <span class="badge bg-success">{{ ucfirst($purchase->status) }}</span><br>
    <strong>Total:</strong> Rp {{ number_format($purchase->final_total, 0, ',', '.') }}</p>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Back</a>
</div></div>
<div class="card shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Items</h6></div>
<div class="card-body p-0"><table class="table table-sm mb-0">
    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
    <tbody>
        @foreach($purchase->purchaseLines as $line)
        <tr><td>{{ $line->product->name ?? '-' }}</td><td>{{ $line->quantity }}</td>
            <td>Rp {{ number_format($line->purchase_price, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($line->quantity * $line->purchase_price, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table></div></div>
</div></div>
@endsection


