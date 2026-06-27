@extends('layout.mainlayout')
@section('title', 'Contacts')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Contacts</h4>
    <div>
        <a href="{{ route('contacts.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Contact</a>
        <a href="{{ route('contacts.customers') }}" class="btn btn-outline-info"><i class="fas fa-user"></i> Customers</a>
        <a href="{{ route('contacts.suppliers') }}" class="btn btn-outline-warning"><i class="fas fa-truck"></i> Suppliers</a>
    </div>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Email</th><th>Mobile</th><th>Balance</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($contacts as $c)
        <tr>
            <td><strong>{{ $c->name }}</strong></td>
            <td><span class="badge bg-{{ $c->type=='customer'?'info':($c->type=='supplier'?'warning':'secondary') }}">{{ ucfirst($c->type) }}</span></td>
            <td>{{ $c->email ?? '-' }}</td><td>{{ $c->mobile }}</td>
            <td>Rp {{ number_format($c->balance, 0, ',', '.') }}</td>
            <td>{!! $c->contact_status=='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('contacts.show', $c->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                <a href="{{ route('contacts.edit', $c->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
            </td>
        </tr>
        @empty <tr><td colspan="7" class="text-center text-muted py-4">No contacts</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $contacts->links() }}</div>
</div></div>
@endsection


