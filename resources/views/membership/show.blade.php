@extends('layout.mainlayout')
@section('title', 'Member Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
    <h5>Member: <code>{{ $member->membership_no }}</code></h5>
    <p><strong>Name:</strong> {{ $member->name }}<br><strong>Mobile:</strong> {{ $member->mobile }}<br>
    <strong>Points:</strong> {{ $member->points_balance }}<br><strong>Tier:</strong> {{ ucfirst($member->tier) }}<br>
    <strong>Total Spent:</strong> Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
    <a href="{{ route('memberships.index') }}" class="btn btn-outline-secondary">Back</a>
</div></div>
<div class="card shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Points History</h6></div>
<div class="card-body p-0"><table class="table table-sm mb-0">
    <thead><tr><th>Date</th><th>Points</th><th>Type</th><th>Description</th></tr></thead>
    <tbody>
        @forelse($pointHistory as $h)
        <tr><td>{{ \App\Utils\Util::format_date($h->created_at) }}</td><td>{{ $h->points }}</td>
            <td><span class="badge bg-{{ $h->type=='earn'?'success':'warning' }}">{{ ucfirst($h->type) }}</span></td>
            <td>{{ $h->description ?? '-' }}</td></tr>
        @empty <tr><td colspan="4" class="text-center text-muted">No points history</td></tr>
        @endforelse
    </tbody>
</table></div></div>
</div></div>
@endsection


