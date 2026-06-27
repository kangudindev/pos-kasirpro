@extends('layout.mainlayout')
@section('title', 'Edit Brand')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('brands.update', $brand->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="{{ $brand->name }}" required></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


