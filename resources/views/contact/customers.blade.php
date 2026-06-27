@extends('layout.mainlayout')
@section('title', 'Customers')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Customers</h4>
    <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> All Contacts</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Balance</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($customers as $c)
        <tr><td><strong>{{ $c->name }}</strong></td><td>{{ $c->email ?? '-' }}</td><td>{{ $c->mobile }}</td>
            <td>Rp {{ number_format($c->balance, 0, ',', '.') }}</td>
            <td class="text-end"><a href="{{ route('contacts.show', $c->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
        </tr>
        @empty <tr><td colspan="5" class="text-center text-muted py-4">No customers</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
</div></div>
@endsection


