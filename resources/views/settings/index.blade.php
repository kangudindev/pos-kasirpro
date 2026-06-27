@extends('layout.mainlayout')
@section('title', 'Settings')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0">Settings</h4></div>
<div class="row g-4">
    <div class="col-md-4"><a href="{{ route('settings.business') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-building fa-3x text-primary mb-3"></i><h5>Business</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('settings.invoice') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-file-invoice fa-3x text-success mb-3"></i><h5>Invoice</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('settings.tax') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-percentage fa-3x text-warning mb-3"></i><h5>Tax Rates</h5></div></a>
    </div>
</div>
</div></div>
@endsection


