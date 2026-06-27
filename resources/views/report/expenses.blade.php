@extends('layout.mainlayout')
@section('title', 'Expense Report')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm mb-4"><div class="card-body">
<form class="row g-3" method="GET">
    <div class="col-md-3"><label>From</label><input type="date" class="form-control" name="start_date" value="{{ $startDate }}"></div>
    <div class="col-md-3"><label>To</label><input type="date" class="form-control" name="end_date" value="{{ $endDate }}"></div>
    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button></div>
</form>
</div></div>
<div class="row mb-4"><div class="col-md-4"><div class="card shadow-sm"><div class="card-body text-center">
    <h6 class="text-muted">Total Expenses</h6><h3>Rp {{ number_format($summary['total_expenses'], 0, ',', '.') }}</h3>
</div></div></div></div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Ref</th><th>Date</th><th>Amount</th><th>Description</th></tr></thead>
    <tbody>
        @foreach($expenses as $e)
        <tr><td><code>{{ $e->ref_no ?? '-' }}</code></td><td>{{ \App\Utils\Util::format_date($e->transaction_date) }}</td>
            <td>Rp {{ number_format($e->final_total, 0, ',', '.') }}</td><td>{{ Str::limit($e->additional_notes, 30) }}</td></tr>
        @endforeach
    </tbody>
</table>
</div></div>
<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
@endsection


