@extends('layout.mainlayout')
@section('title', 'Stock Transfers')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Stock Transfers</h4>
    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Transfer</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Ref #</th><th>From</th><th>To</th><th>Date</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($transfers as $t)
        <tr><td><code>{{ $t->ref_no }}</code></td><td>{{ $t->from_location_id }}</td><td>{{ $t->to_location_id }}</td>
            <td>{{ \App\Utils\Util::format_date($t->created_at) }}</td>
            <td><span class="badge bg-success">{{ ucfirst($t->status) }}</span></td>
        </tr>
        @empty <tr><td colspan="5" class="text-center text-muted py-4">No transfers</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
</div></div>
@endsection


