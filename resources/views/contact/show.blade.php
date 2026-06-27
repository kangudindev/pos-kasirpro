@extends('layout.mainlayout')
@section('title', 'Contact Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
    <h5>{{ $contact->name }}</h5>
    <p class="mb-1"><strong>Type:</strong> {{ ucfirst($contact->type) }}</p>
    <p class="mb-1"><strong>Email:</strong> {{ $contact->email ?? '-' }}</p>
    <p class="mb-1"><strong>Mobile:</strong> {{ $contact->mobile }}</p>
    <p class="mb-1"><strong>Balance:</strong> Rp {{ number_format($contact->balance, 0, ',', '.') }}</p>
    <a href="{{ route('contacts.edit', $contact->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
    <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Back</a>
</div></div>
</div></div>
@endsection


