@extends('layout.mainlayout')
@section('title', 'Profit & Loss')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
<form class="row g-3" method="GET">
    <div class="col-md-3"><label>From</label><input type="date" class="form-control" name="start_date" value="{{ $startDate }}"></div>
    <div class="col-md-3"><label>To</label><input type="date" class="form-control" name="end_date" value="{{ $endDate }}"></div>
    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button></div>
</form>
</div></div>
<div class="row g-4">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Total Sales</h6><h3 class="text-success">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Total Purchases</h6><h3 class="text-warning">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Total Expenses</h6><h3 class="text-danger">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Net Profit</h6><h3 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
    </div></div></div>
</div>
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mt-4"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
@endsection


