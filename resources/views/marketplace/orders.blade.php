@extends('layouts.app')

@section('title', 'Marketplace Orders')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Marketplace Orders</h1>
    @if($orders->isEmpty())
        <div class="alert alert-info">No marketplace orders found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Channel Order ID</th>
                        <th>Status</th>
                        <th>Buyer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Synced At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->channel_order_id }}</td>
                            <td>{{ $order->status ?? '-' }}</td>
                            <td>{{ $order->buyer_name ?? '-' }}</td>
                            <td>{{ $order->buyer_phone ?? '-' }}</td>
                            <td>{{ number_format($order->total_amount ?? 0, 2) }}</td>
                            <td>{{ $order->synced_at ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    @endif
</div>
@endsection