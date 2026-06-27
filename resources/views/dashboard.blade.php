<?php $page = 'index'; ?>
@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="welcome d-lg-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center welcome-text">
                <h3 class="d-flex align-items-center"><img src="{{ URL::asset('/build/img/icons/hi.svg') }}" alt="img">&nbsp;Hi {{ Auth::user()->name ?? 'User' }},</h3>&nbsp;<h6>here's what's happening with your store today.</h6>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-muted">{{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="row sales-cards">
            <div class="col-xl-6 col-sm-12 col-12">
                <div class="card d-flex align-items-center justify-content-between default-cover mb-4">
                    <div>
                        <h6>Today's Sales</h6>
                        <h3>Rp <span class="counters" data-count="{{ $todaySales }}">{{ number_format($todaySales, 0, ',', '.') }}</span></h3>
                        <p class="sales-range"><span class="text-success"><i data-feather="chevron-up" class="feather-16"></i>+{{ $todayTransactions }} transactions</span> today</p>
                    </div>
                    <img src="{{ URL::asset('/build/img/icons/weekly-earning.svg') }}" alt="img">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card color-info bg-primary mb-4">
                    <img src="{{ URL::asset('/build/img/icons/total-sales.svg') }}" alt="img">
                    <h3 class="counters" data-count="{{ $totalProducts }}">{{ $totalProducts }}</h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card color-info bg-secondary mb-4">
                    <img src="{{ URL::asset('/build/img/icons/purchased-earnings.svg') }}" alt="img">
                    <h3 class="counters" data-count="{{ $lowStockProducts }}">{{ $lowStockProducts }}</h3>
                    <p>Low Stock Items</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="dash-widget w-100">
                    <div class="dash-widgetimg">
                        <span><img src="{{ URL::asset('/build/img/icons/dash1.svg') }}" alt="img"></span>
                    </div>
                    <div class="dash-widgetcontent">
                        <h5>Rp <span class="counters" data-count="{{ $monthSales }}">{{ number_format($monthSales, 0, ',', '.') }}</span></h5>
                        <h6>Monthly Sales</h6>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="dash-widget dash1 w-100">
                    <div class="dash-widgetimg">
                        <span><img src="{{ URL::asset('/build/img/icons/dash2.svg') }}" alt="img"></span>
                    </div>
                    <div class="dash-widgetcontent">
                        <h5>Rp <span class="counters" data-count="{{ $todaySales }}">{{ number_format($todaySales, 0, ',', '.') }}</span></h5>
                        <h6>Today's Sales</h6>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="dash-widget dash2 w-100">
                    <div class="dash-widgetimg">
                        <span><img src="{{ URL::asset('/build/img/icons/dash3.svg') }}" alt="img"></span>
                    </div>
                    <div class="dash-widgetcontent">
                        <h5>Rp <span class="counters" data-count="{{ $todayTransactions }}">{{ $todayTransactions }}</span></h5>
                        <h6>Today's Transactions</h6>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="dash-widget dash3 w-100">
                    <div class="dash-widgetimg">
                        <span><img src="{{ URL::asset('/build/img/icons/dash4.svg') }}" alt="img"></span>
                    </div>
                    <div class="dash-widgetcontent">
                        <h5><span class="counters" data-count="{{ $totalCustomers }}">{{ $totalCustomers }}</span></h5>
                        <h6>Customers</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card table-list-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Recent Sales</h5>
                            <a href="{{ route('sells.index') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSales as $s)
                                    <tr>
                                        <td><a href="{{ route('sells.show', $s->id) }}"><strong>{{ $s->invoice_no ?? $s->ref_no }}</strong></a></td>
                                        <td>{{ $s->contact->name ?? 'Walk-in' }}</td>
                                        <td>Rp {{ number_format($s->final_total, 0, ',', '.') }}</td>
                                        <td>
                                            @if($s->payment_status == 'paid')
                                                <span class="badge badge-linesuccess">Paid</span>
                                            @else
                                                <span class="badge badge-linewarning">Due</span>
                                            @endif
                                        </td>
                                        <td>{{ \App\Utils\Util::format_date($s->transaction_date) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No sales yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Top Selling Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Qty Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProducts as $i => $item)
                                    @php $p = \App\Models\Product\Product::find($item->product_id); @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $p->name ?? 'Unknown' }}</strong></td>
                                        <td>{{ $item->total_qty }}</td>
                                        <td>Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
