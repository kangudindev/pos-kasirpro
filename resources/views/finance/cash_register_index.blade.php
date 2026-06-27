@extends('layout.mainlayout')
@section('title', 'Cash Registers')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Cash Registers</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#openModal"><i class="fas fa-cash-register"></i> Open Register</button>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>User</th><th>Opening</th><th>Closing</th><th>Status</th><th>Closed At</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($registers as $r)
        <tr>
            <td>{{ $r->user_id }}</td>
            <td>Rp {{ number_format($r->opening_amount, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($r->closing_amount ?? 0, 0, ',', '.') }}</td>
            <td><span class="badge bg-{{ $r->status=='open'?'success':'secondary' }}">{{ ucfirst($r->status) }}</span></td>
            <td>{{ $r->closed_at ? \App\Utils\Util::format_datetime($r->closed_at) : '-' }}</td>
            <td class="text-end">
                <a href="{{ route('cash-registers.show', $r->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                @if($r->status=='open')
                <button class="btn btn-sm btn-outline-danger close-register" data-id="{{ $r->id }}"><i class="fas fa-times"></i> Close</button>
                @endif
            </td>
        </tr>
        @empty <tr><td colspan="6" class="text-center text-muted py-4">No registers</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="modal fade" id="openModal"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('cash-registers.open') }}">
    @csrf
    <div class="modal-header"><h5 class="modal-title">Open Cash Register</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">Opening Amount</label>
        <input type="number" class="form-control" name="opening_amount" value="0" required>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-cash-register"></i> Open</button>
    </div>
</form>
</div></div></div>
<form id="closeForm" method="POST" action="{{ route('cash-registers.close') }}">
    @csrf
    <input type="hidden" name="closing_amount" id="close-amount" value="0">
</form>
<script>
$(document).ready(function() {
    $(document).on('click', '.close-register', function() {
        if (confirm('Close this cash register?')) {
            let amount = prompt('Enter closing amount:', '0');
            if (amount !== null) {
                $('#close-amount').val(amount);
                $('#closeForm').submit();
            }
        }
    });
});
</script>
</div></div>
@endsection


