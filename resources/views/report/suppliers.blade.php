@extends('layout.mainlayout')
@section('title', 'Supplier Report')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Total Purchases</th><th>Balance</th></tr></thead>
    <tbody>
        @foreach($suppliers as $s)
        <tr><td><strong>{{ $s->name }}</strong></td><td>{{ $s->email ?? '-' }}</td>
            <td>Rp {{ number_format($s->total_purchases ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($s->balance, 0, ',', '.') }}</td></tr>
        @endforeach
    </tbody>
</table>
</div></div>
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
@endsection


