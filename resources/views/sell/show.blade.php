@extends('layout.mainlayout')
@section('title', 'Sale Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
    <h5>Sale: <code>{{ $sell->invoice_no ?? $sell->ref_no }}</code></h5>
    <p><strong>Customer:</strong> {{ $sell->contact->name ?? '-' }}<br>
    <strong>Date:</strong> {{ \App\Utils\Util::format_datetime($sell->transaction_date) }}<br>
    <strong>Payment:</strong> {!! $sell->payment_status=='paid'?'<span class="badge bg-success">Paid</span>':'<span class="badge bg-warning">Due</span>' !!}<br>
    <strong>Total:</strong> Rp {{ number_format($sell->final_total, 0, ',', '.') }}</p>
    <a href="{{ route('sells.index') }}" class="btn btn-outline-secondary">Back</a>
    <a href="{{ route('invoices.print', $sell->id) }}" class="btn btn-primary"><i class="fas fa-print"></i> Print</a>
</div></div>
<div class="card shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Items</h6></div>
<div class="card-body p-0"><table class="table table-sm mb-0">
    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
    <tbody>
        @foreach($sell->sellLines as $line)
        <tr><td>{{ $line->product->name ?? '-' }}</td><td>{{ $line->quantity }}</td>
            <td>Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table></div></div>
</div></div>
@endsection


