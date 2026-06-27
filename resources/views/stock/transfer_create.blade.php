@extends('layout.mainlayout')
@section('title', 'New Stock Transfer')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('stock-transfers.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-4"><label>From</label><select class="form-select" name="from_location_id">@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label>To</label><select class="form-select" name="to_location_id">@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Transfer</button>
        <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


