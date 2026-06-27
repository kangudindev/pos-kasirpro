@extends('layout.mainlayout')
@section('title', 'Brands')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Brands</h4>
    <a href="{{ route('brands.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Brand</a>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse($brands as $brand)
                <tr>
                    <td><strong>{{ $brand->name }}</strong></td>
                    <td>{!! $brand->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('brands.edit', $brand->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No brands</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div></div>
@endsection


