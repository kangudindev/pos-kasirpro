@extends('layout.mainlayout')
@section('title', 'Expense Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
    <h5>Expense: <code>{{ $expense->ref_no ?? '-' }}</code></h5>
    <p><strong>Amount:</strong> Rp {{ number_format($expense->final_total, 0, ',', '.') }}<br>
    <strong>Date:</strong> {{ \App\Utils\Util::format_date($expense->transaction_date) }}<br>
    <strong>Description:</strong> {{ $expense->additional_notes ?? '-' }}</p>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back</a>
</div></div>
</div></div>
@endsection


