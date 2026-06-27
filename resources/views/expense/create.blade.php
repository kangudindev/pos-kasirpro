@extends('layout.mainlayout')
@section('title', 'Add Expense')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('expenses.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Amount *</label><input type="number" class="form-control" name="amount" step="0.01" required></div>
        <div class="col-md-4"><label class="form-label">Date *</label><input type="date" class="form-control" name="transaction_date" value="{{ date('Y-m-d') }}" required></div>
        <div class="col-md-4"><label class="form-label">Location</label>
            <select class="form-select" name="location_id">
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


