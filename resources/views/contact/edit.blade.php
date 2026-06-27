@extends('layout.mainlayout')
@section('title', 'Edit Contact')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('contacts.update', $contact->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="{{ $contact->name }}" required></div>
        <div class="col-md-3"><label class="form-label">Mobile</label><input type="text" class="form-control" name="mobile" value="{{ $contact->mobile }}" required></div>
        <div class="col-md-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ $contact->email }}"></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


