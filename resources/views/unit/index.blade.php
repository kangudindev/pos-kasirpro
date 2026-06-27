@extends('layout.mainlayout')
@section('title', 'Units')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Units</h4><a href="{{ route('units.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Unit</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Short</th><th>Base Unit</th><th>Multiplier</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($units as $unit)
        <tr><td><strong>{{ $unit->actual_name }}</strong></td><td><code>{{ $unit->short_name }}</code></td><td>{{ $unit->baseUnit->short_name ?? '-' }}</td><td>{{ $unit->multiplier }}</td>
            <td>{!! $unit->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end"><a href="{{ route('units.edit', $unit->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
        </tr>
        @empty <tr><td colspan="6" class="text-center text-muted py-4">No units</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
</div></div>
@endsection


