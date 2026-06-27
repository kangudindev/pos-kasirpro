@extends('layout.mainlayout')
@section('title', 'Categories')
@section('content')
<div class="page-wrapper"><div class="content"><div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Categories</h4>
    <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Parent</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td><strong>{{ $cat->name }}</strong></td>
                    <td>{{ $cat->parent->name ?? '-' }}</td>
                    <td>{!! $cat->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('categories.edit', $cat->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No categories</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div></div>
@endsection


