@extends('layout.mainlayout')
@section('title', 'Reports')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0">Reports</h4></div>
<div class="row g-4">
    <div class="col-md-4"><a href="{{ route('reports.sales') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i><h5>Sales Report</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('reports.purchases') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-truck fa-3x text-success mb-3"></i><h5>Purchase Report</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('reports.stock') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-boxes fa-3x text-warning mb-3"></i><h5>Stock Report</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('reports.expenses') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-money-bill-wave fa-3x text-danger mb-3"></i><h5>Expense Report</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('reports.profit-loss') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-chart-line fa-3x text-info mb-3"></i><h5>Profit & Loss</h5></div></a>
    </div>
    <div class="col-md-4"><a href="{{ route('reports.customers') }}" class="card text-decoration-none"><div class="card-body text-center py-4">
        <i class="fas fa-users fa-3x text-secondary mb-3"></i><h5>Customer Report</h5></div></a>
    </div>
</div>
</div></div>
@endsection


