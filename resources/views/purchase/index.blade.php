@extends('layout.mainlayout')
@section('title', 'Purchases')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Purchases</h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Purchase</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Ref #</th><th>Supplier</th><th>Date</th><th>Status</th><th>Total</th><th>Paid</th><th>Due</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($purchases as $p)
        <tr>
            <td><code>{{ $p->ref_no }}</code></td>
            <td>{{ $p->contact->name ?? '-' }}</td>
            <td>{{ \App\Utils\Util::format_date($p->transaction_date) }}</td>
            <td><span class="badge bg-{{ $p->status=='received'?'success':($p->status=='ordered'?'warning':'secondary') }}">{{ ucfirst($p->status) }}</span></td>
            <td>Rp {{ number_format($p->final_total, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($p->payments->sum('amount'), 0, ',', '.') }}</td>
            <td>Rp {{ number_format($p->final_total - $p->payments->sum('amount'), 0, ',', '.') }}</td>
            <td class="text-end">
                <a href="{{ route('purchases.show', $p->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
            </td>
        </tr>
        @empty <tr><td colspan="8" class="text-center text-muted py-4">No purchases</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $purchases->links() }}</div>
</div></div>
@endsection


