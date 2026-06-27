@extends('layouts.app')

@section('title', 'Marketplace Channels')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Marketplace Channels</h1>
    <a href="{{ route('marketplace.create') }}" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Add Channel</a>
    @if($channels->isEmpty())
        <div class="alert alert-info">No channels configured.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Platform</th>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($channels as $channel)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ ucfirst($channel->platform) }}</td>
                            <td>{{ $channel->name }}</td>
                            <td>{{ $channel->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <form method="POST" action="{{ route('marketplace.destroy', $channel->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this channel?')"><i class="fas fa-trash"></i></button>
                                </form>
                                <a href="{{ route('marketplace.sync-products', $channel->id) }}" class="btn btn-sm btn-success" onclick="event.preventDefault(); document.getElementById('sync-products-{{ $channel->id }}').submit();"><i class="fas fa-sync"></i> Sync Products</a>
                                <form id="sync-products-{{ $channel->id }}" method="POST" action="{{ route('marketplace.sync-products', $channel->id) }}" style="display:none;">@csrf</form>
                                <a href="{{ route('marketplace.sync-orders', $channel->id) }}" class="btn btn-sm btn-info" onclick="event.preventDefault(); document.getElementById('sync-orders-{{ $channel->id }}').submit();"><i class="fas fa-download"></i> Sync Orders</a>
                                <form id="sync-orders-{{ $channel->id }}" method="POST" action="{{ route('marketplace.sync-orders', $channel->id) }}" style="display:none;">@csrf</form>
                                <a href="{{ route('marketplace.orders', $channel->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-list"></i> View Orders</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection