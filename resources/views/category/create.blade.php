@extends('layout.mainlayout')
@section('title', 'Add Category')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Category</h4>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Parent Category</label>
                    <select class="form-select" name="parent_id">
                        <option value="">-- None (Top Level) --</option>
                        @foreach($parentCategories as $pcat)
                        <option value="{{ $pcat->id }}">{{ $pcat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection


