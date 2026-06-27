@extends('layout.mainlayout')
@section('title', 'Add Purchase')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Purchase</h4>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form action="{{ route('purchases.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Supplier *</label>
            <select class="form-select" name="contact_id" required>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Location</label>
            <select class="form-select" name="location_id">
                @foreach($locations as $l)
                <option value="{{ $l->id }}">{{ $l->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Date</label>
            <input type="date" class="form-control" name="transaction_date" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-12"><hr><h6>Products</h6>
            <table class="table table-sm" id="product-table">
                <thead><tr><th>Product</th><th>Qty</th><th>Unit Cost</th><th>Total</th><th></th></tr></thead>
                <tbody id="product-rows">
                    <tr>
                        <td>
                            <select class="form-select product-select" name="products[0][product_id]" required>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="products[0][variation_id]" class="variation-id" value="">
                        </td>
                        <td><input type="number" class="form-control qty" name="products[0][quantity]" value="1" min="0.01" step="0.01"></td>
                        <td><input type="number" class="form-control cost" name="products[0][unit_cost]" value="0" step="0.01"></td>
                        <td class="line-total">Rp 0</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-success" id="add-row"><i class="fas fa-plus"></i> Add Product</button>
        </div>
        <div class="col-12 mt-3"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Purchase</button></div>
    </div>
</form>
</div></div>
<script>
$(document).ready(function() {
    let rowIndex = 1;
    $('#add-row').click(function() {
        let row = $('#product-rows tr:first').clone();
        row.find('input,select').each(function() {
            let name = $(this).attr('name');
            if (name) $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
            $(this).val('');
        });
        row.find('.qty').val(1);
        row.find('.cost').val(0);
        row.find('.line-total').text('Rp 0');
        $('#product-rows').append(row);
        rowIndex++;
    });
    $(document).on('click', '.remove-row', function() { if ($('#product-rows tr').length > 1) $(this).closest('tr').remove(); });
    $(document).on('input', '.qty, .cost', function() {
        let tr = $(this).closest('tr');
        let qty = parseFloat(tr.find('.qty').val()) || 0;
        let cost = parseFloat(tr.find('.cost').val()) || 0;
        tr.find('.line-total').text('Rp ' + (qty * cost).toLocaleString());
    });
});
</script>
</div></div>
@endsection


