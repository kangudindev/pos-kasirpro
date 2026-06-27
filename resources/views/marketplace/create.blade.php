@extends('layouts.app')

@section('title', 'Add Marketplace Channel')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Add Marketplace Channel</h1>
    <form method="POST" action="{{ route('marketplace.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-select @error('platform') is-invalid @enderror" required>
                        <option value="">Select Platform</option>
                        <option value="shopee">Shopee</option>
                        <option value="tokopedia">Tokopedia</option>
                        <option value="lazada">Lazada</option>
                        <option value="woocommerce">WooCommerce</option>
                    </select>
                    @error('platform')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Channel Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">API Key / Partner Key</label>
                    <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key') }}" required>
                    @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">API Secret / Secret Key</label>
                    <input type="password" name="api_secret" class="form-control @error('api_secret') is-invalid @enderror" required>
                    @error('api_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Shop ID (optional)</label>
                    <input type="text" name="shop_id" class="form-control @error('shop_id') is-invalid @enderror" value="{{ old('shop_id') }}">
                    @error('shop_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('marketplace.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Channel</button>
            </div>
        </div>
    </form>
</div>
@endsection