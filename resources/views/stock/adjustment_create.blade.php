@extends('layout.mainlayout')
@section('title', 'New Stock Adjustment')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
<form action="{{ route('stock-adjustments.store') }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Location</label>
            <select class="form-select" name="location_id">@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select>
        </div>
        <div class="col-md-4"><label class="form-label">Type</label>
            <select class="form-select" name="adjustment_type">
                <option value="normal">Normal (Add Stock)</option>
                <option value="abnormal">Abnormal (Remove Stock)</option>
            </select>
        </div>
        <div class="col-12">
            <h6>Products</h6>
            <table class="table table-sm">
                <thead><tr><th>Product</th><th>Qty</th><th></th></tr></thead>
                <tbody id="adj-rows">
                    <tr>
                        <td>
                            <select class="form-select" name="products[0][product_id]">
                                @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                            </select>
                            <input type="hidden" name="products[0][variation_id]" value="">
                        </td>
                        <td><input type="number" class="form-control" name="products[0][quantity]" value="1" min="0.01" step="0.01"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-adj-row"><i class="fas fa-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-success" id="add-adj-row"><i class="fas fa-plus"></i> Add Product</button>
        </div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">Back</a></div>
    </div>
</form>
</div></div>
<script>
$(document).ready(function() {
    let idx=1;
    $('#add-adj-row').click(function() {
        let row=$('#adj-rows tr:first').clone();
        row.find('select').attr('name','products['+idx+'][product_id]');
        row.find('input').attr('name','products['+idx+'][quantity]').val(1);
        $('#adj-rows').append(row); idx++;
    });
    $(document).on('click','.remove-adj-row',function(){if($('#adj-rows tr').length>1)$(this).closest('tr').remove();});
});
</script>
</div></div>
@endsection


