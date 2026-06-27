@extends('layout.mainlayout')
@section('title', 'Memberships')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Memberships</h4>
    <a href="{{ route('memberships.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Register Member</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Member #</th><th>Name</th><th>Mobile</th><th>Points</th><th>Tier</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($members as $m)
        <tr><td><code>{{ $m->membership_no }}</code></td><td><strong>{{ $m->name }}</strong></td><td>{{ $m->mobile }}</td>
            <td>{{ $m->points_balance }}</td>
            <td><span class="badge bg-{{ match($m->tier) {'platinum'=>'dark','gold'=>'warning','silver'=>'secondary','bronze'=>'info'} }}">{{ ucfirst($m->tier) }}</span></td>
            <td class="text-end"><a href="{{ route('memberships.show', $m->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
        </tr>
        @empty <tr><td colspan="6" class="text-center text-muted py-4">No members</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
</div></div>
@endsection


