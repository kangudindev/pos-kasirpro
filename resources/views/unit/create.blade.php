@extends('layout.mainlayout')
@section('title', 'Add Unit')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('units.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name *</label><input type="text" class="form-control" name="actual_name" required></div>
        <div class="col-md-3"><label class="form-label">Short Name *</label><input type="text" class="form-control" name="short_name" required></div>
        <div class="col-md-3"><label class="form-label">Base Unit</label>
            <select class="form-select" name="base_unit_id">
                <option value="">-- None --</option>
                @foreach($baseUnits as $bu)
                <option value="{{ $bu->id }}">{{ $bu->short_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Multiplier</label><input type="number" class="form-control" name="multiplier" value="1" step="0.001"></div>
        <div class="col-md-3">
            <div class="form-check mt-4"><input type="checkbox" class="form-check-input" name="allow_decimal" id="allow_decimal">
            <label class="form-check-label" for="allow_decimal">Allow Decimal</label></div>
        </div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
</div></div>
@endsection


