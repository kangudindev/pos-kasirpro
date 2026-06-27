@extends('layout.mainlayout')
@section('title', 'Register Detail')
@section('content')
<div class="page-wrapper"><div class="content"><div class="card shadow-sm"><div class="card-body">
    <h5>Cash Register #{{ $register->id }}</h5>
    <p><strong>Status:</strong> {{ ucfirst($register->status) }}<br>
    <strong>Opening:</strong> Rp {{ number_format($register->opening_amount, 0, ',', '.') }}<br>
    <strong>Closing:</strong> Rp {{ number_format($register->closing_amount ?? 0, 0, ',', '.') }}<br>
    <strong>Total Cash:</strong> Rp {{ number_format($total_cash ?? 0, 0, ',', '.') }}<br>
    <strong>Total Card:</strong> Rp {{ number_format($total_card ?? 0, 0, ',', '.') }}</p>
    <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-secondary">Back</a>
</div></div>
</div></div>
@endsection


