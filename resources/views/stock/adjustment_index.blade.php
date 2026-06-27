@extends('layout.mainlayout')
@section('title', 'Stock Adjustments')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Stock Adjustments</h4>
    <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Adjustment</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Ref #</th><th>Type</th><th>Date</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($adjustments as $a)
        <tr><td><code>{{ $a->ref_no }}</code></td><td><span class="badge bg-{{ $a->adjustment_type=='normal'?'info':'warning' }}">{{ ucfirst($a->adjustment_type) }}</span></td>
            <td>{{ \App\Utils\Util::format_date($a->created_at) }}</td>
            <td><span class="badge bg-{{ $a->status=='approved'?'success':'secondary' }}">{{ ucfirst($a->status) }}</span></td>
            <td class="text-end"><a href="{{ route('stock-adjustments.show', $a->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
        </tr>
        @empty <tr><td colspan="5" class="text-center text-muted py-4">No adjustments</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
</div></div>
@endsection


