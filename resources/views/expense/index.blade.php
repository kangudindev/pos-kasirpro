@extends('layout.mainlayout')
@section('title', 'Expenses')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Expenses</h4>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Expense</a>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Ref #</th><th>Category</th><th>Date</th><th>Amount</th><th>Description</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($expenses as $e)
        <tr>
            <td><code>{{ $e->ref_no ?? '-' }}</code></td>
            <td>{{ $e->contact->name ?? '-' }}</td>
            <td>{{ \App\Utils\Util::format_date($e->transaction_date) }}</td>
            <td>Rp {{ number_format($e->final_total, 0, ',', '.') }}</td>
            <td>{{ Str::limit($e->additional_notes, 30) }}</td>
            <td class="text-end">
                <a href="{{ route('expenses.show', $e->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
            </td>
        </tr>
        @empty <tr><td colspan="6" class="text-center text-muted py-4">No expenses</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $expenses->links() }}</div>
</div></div>
@endsection


