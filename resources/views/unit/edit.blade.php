@extends('layout.mainlayout')
@section('title', 'Edit Unit')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('units.update', $unit->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="actual_name" value="{{ $unit->actual_name }}" required></div>
        <div class="col-md-3"><label class="form-label">Short</label><input type="text" class="form-control" name="short_name" value="{{ $unit->short_name }}" required></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


