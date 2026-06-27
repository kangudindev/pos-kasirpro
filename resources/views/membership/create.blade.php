@extends('layout.mainlayout')
@section('title', 'Register Member')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('memberships.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Customer *</label>
            <select class="form-select" name="contact_id" required>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Tier</label>
            <select class="form-select" name="tier"><option value="bronze">Bronze</option><option value="silver">Silver</option><option value="gold">Gold</option><option value="platinum">Platinum</option></select>
        </div>
        <div class="col-md-3"><label class="form-label">Expires</label><input type="date" class="form-control" name="expires_at"></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Register</button>
        <a href="{{ route('memberships.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


