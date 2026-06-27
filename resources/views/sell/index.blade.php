@extends('layout.mainlayout')
@section('title', 'Sales')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Sales</h4>
    <a href="{{ route('pos.index') }}" class="btn btn-success"><i class="fas fa-cash-register"></i> New POS Sale</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Payment</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($sells as $s)
        <tr>
            <td><code>{{ $s->invoice_no ?? $s->ref_no }}</code></td>
            <td>{{ $s->contact->name ?? '-' }}</td>
            <td>{{ \App\Utils\Util::format_date($s->transaction_date) }}</td>
            <td>Rp {{ number_format($s->final_total, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($s->payments->sum('amount'), 0, ',', '.') }}</td>
            <td>Rp {{ number_format($s->final_total - $s->payments->sum('amount'), 0, ',', '.') }}</td>
            <td>{!! $s->payment_status=='paid'?'<span class="badge bg-success">Paid</span>':'<span class="badge bg-warning">Due</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('sells.show', $s->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                <a href="{{ route('invoices.print', $s->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-print"></i></a>
            </td>
        </tr>
        @empty <tr><td colspan="8" class="text-center text-muted py-4">No sales</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $sells->links() }}</div>
</div></div>
@endsection


