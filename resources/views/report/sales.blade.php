@extends('layout.mainlayout')
@section('title', 'Sales Report')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
<form class="row g-3" method="GET">
    <div class="col-md-3"><label>From</label><input type="date" class="form-control" name="start_date" value="{{ $startDate }}"></div>
    <div class="col-md-3"><label>To</label><input type="date" class="form-control" name="end_date" value="{{ $endDate }}"></div>
    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button></div>
</form>
</div></div>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Total Sales</h6><h3 class="text-primary">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Tax</h6><h3>Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Discount</h6><h3>Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Transactions</h6><h3>{{ $summary['transaction_count'] }}</h3>
    </div></div></div>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th></tr></thead>
    <tbody>
        @foreach($sales as $s)
        <tr><td>{{ $s->contact->name ?? 'Walk-in' }}</td><td>{{ \App\Utils\Util::format_date($s->transaction_date) }}</td>
            <td>Rp {{ number_format($s->final_total, 0, ',', '.') }}</td>
            <td>{!! $s->payment_status=='paid'?'<span class="badge bg-success">Paid</span>':'<span class="badge bg-warning">Due</span>' !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
@endsection


