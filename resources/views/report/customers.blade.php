@extends('layout.mainlayout')
@section('title', 'Customer Report')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Total Sales</th><th>Balance</th></tr></thead>
    <tbody>
        @foreach($customers as $c)
        <tr><td><strong>{{ $c->name }}</strong></td><td>{{ $c->email ?? '-' }}</td>
            <td>Rp {{ number_format($c->total_sales ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($c->balance, 0, ',', '.') }}</td></tr>
        @endforeach
    </tbody>
</table>
</div></div>
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
@endsection


