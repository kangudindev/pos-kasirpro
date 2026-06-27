@extends('layout.mainlayout')
@section('title', 'Add Contact')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('contacts.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
        <div class="col-md-3"><label class="form-label">Type *</label>
            <select class="form-select" name="type" required>
                <option value="customer">Customer</option>
                <option value="supplier">Supplier</option>
                <option value="both">Both</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Mobile *</label><input type="text" class="form-control" name="mobile" required></div>
        <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
        <div class="col-md-4"><label class="form-label">Tax Number</label><input type="text" class="form-control" name="tax_number"></div>
        <div class="col-md-4"><label class="form-label">Credit Limit</label><input type="number" class="form-control" name="credit_limit" step="0.01"></div>
        <div class="col-md-4"><label class="form-label">City</label><input type="text" class="form-control" name="city"></div>
        <div class="col-md-4"><label class="form-label">State</label><input type="text" class="form-control" name="state"></div>
        <div class="col-md-4"><label class="form-label">Country</label><input type="text" class="form-control" name="country"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


