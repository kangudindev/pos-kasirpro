@extends('layout.mainlayout')

@section('title', 'POS - Point of Sale')

@section('content')
<div class="page-wrapper"><div class="content"><div class="row g-0" style="height: calc(100vh - 80px);">
    <!-- Left: Products -->
    <div class="col-lg-8 bg-white p-3 overflow-auto">
        <!-- Search Bar -->
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="product-search" class="form-control" placeholder="Scan barcode atau cari produk..." autofocus>
                <button class="btn btn-outline-secondary" type="button" id="btn-search">
                    <i class="fas fa-barcode"></i> Scan
                </button>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="mb-3">
            <div class="btn-group flex-wrap" role="group">
                <button type="button" class="btn btn-outline-primary active category-btn" data-category="all">
                    Semua
                </button>
                @foreach($categories as $category)
                <button type="button" class="btn btn-outline-primary category-btn" data-category="{{ $category->id }}">
                    {{ $category->name }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid -->
        <div class="row g-3" id="product-grid">
            @foreach($products as $product)
            @php
                $variation = $product->sellableVariations->first();
                $stock = $variation ? \App\Utils\ProductUtil::getCurrentStock($variation->id, session('current_location_id', 1)) : 0;
            @endphp
            <div class="col-sm-6 col-md-4 col-lg-3 product-card" data-category="{{ $product->category_id }}" data-name="{{ strtolower($product->name) }}" data-sku="{{ strtolower($product->sku) }}">
                <div class="card h-100 product-item" style="cursor: pointer;" data-variation-id="{{ $variation->id ?? '' }}" data-stock="{{ $stock }}">
                    @if($product->image)
                        <img src="{{ asset($product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 120px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                            <i class="fas fa-box fa-3x text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1 text-truncate">{{ $product->name }}</h6>
                        <small class="text-muted">{{ $product->category->name ?? '' }}</small>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold text-primary">Rp {{ number_format($variation->default_sell_price ?? 0, 0, ',', '.') }}</span>
                            <small class="text-muted">{{ $stock }} stok</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Right: Cart -->
    <div class="col-lg-4 bg-light p-3 d-flex flex-column">
        <!-- Customer Selection -->
        <div class="mb-3">
            <label class="form-label fw-bold">Pelanggan</label>
            <select class="form-select" id="customer-select">
                <option value="1">Walk-in Customer</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Cart Items -->
        <div class="flex-grow-1 overflow-auto mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Keranjang</h6>
                <button class="btn btn-sm btn-outline-danger" id="btn-clear-cart">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
            <div id="cart-items" class="bg-white rounded p-3 shadow-sm" style="min-height: 200px;">
                <p class="text-muted text-center" id="empty-cart">Keranjang kosong</p>
            </div>
        </div>

        <!-- Totals -->
        <div class="bg-white rounded p-3 shadow-sm mb-3">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span id="subtotal">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Pajak (10%)</span>
                <span id="tax">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Diskon</span>
                <input type="number" id="discount" class="form-control form-control-sm text-end" style="width: 100px;" value="0">
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
                <span>Total</span>
                <span id="grand-total" class="text-primary">Rp 0</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-grid gap-2">
            <button class="btn btn-success btn-lg" id="btn-payment" disabled>
                <i class="fas fa-credit-card"></i> Bayar
            </button>
            <div class="row g-2">
                <div class="col">
                    <button class="btn btn-outline-secondary w-100" id="btn-hold">
                        <i class="fas fa-pause"></i> Tahan
                    </button>
                </div>
                <div class="col">
                    <button class="btn btn-outline-danger w-100" id="btn-void">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h3 class="text-primary" id="modal-total">Rp 0</h3>
                </div>

                <form id="payment-form">
                    @csrf
                    <input type="hidden" name="contact_id" id="payment-contact-id" value="1">
                    <input type="hidden" name="transaction_date" value="{{ now()->format('Y-m-d H:i:s') }}">
                    <input type="hidden" name="location_id" value="{{ session('current_location_id', 1) }}">

                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select class="form-select" name="payments[0][method]">
                            <option value="cash">Tunai</option>
                            <option value="card">Kartu</option>
                            <option value="bank_transfer">Transfer Bank</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Bayar</label>
                        <input type="number" class="form-control form-control-lg" name="payments[0][amount]" id="payment-amount" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kembalian</label>
                        <input type="text" class="form-control form-control-lg" id="change-amount" readonly value="Rp 0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-confirm-payment">
                    <i class="fas fa-check"></i> Konfirmasi Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

</div></div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let cartIndex = 0;

// Product search
    $('#product-search').on('keyup', function() {
        const query = $(this).val().toLowerCase();
        $('.product-card').each(function() {
            const name = $(this).data('name');
            const sku = $(this).data('sku');
            const match = name.includes(query) || sku.includes(query);
            $(this).toggle(match);
        });
    });

    // Barcode scanner - Enter key triggers search
    $('#product-search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                searchByBarcode(barcode);
            }
        }
    });

    // Scan button click
    $('#btn-search').click(function() {
        const barcode = $('#product-search').val().trim();
        if (barcode) {
            searchByBarcode(barcode);
        }
    });

    // Search product by barcode via API
    function searchByBarcode(barcode) {
        $.post('/pos/search-barcode', { barcode: barcode }, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                addToCart(data);
                $('#product-search').val('').focus();
            }
        }).fail(function(xhr) {
            if (xhr.status === 404) {
                alert('Produk tidak ditemukan');
            } else {
                alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }

    // Category filter
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        const category = $(this).data('category');
        if (category === 'all') {
            $('.product-card').show();
        } else {
            $('.product-card').hide();
            $(`.product-card[data-category="${category}"]`).show();
        }
    });

    // Add to cart
    $('.product-item').click(function() {
        const variationId = $(this).data('variation-id');
        const stock = $(this).data('stock');

        if (!variationId) return;
        if (stock <= 0) {
            alert('Stok habis!');
            return;
        }

        // Get product details via AJAX
        $.get(`/pos/product-row/${variationId}`, function(data) {
            addToCart(data);
        });
    });

    // Add to cart function
    function addToCart(product) {
        const existing = cart.find(item => item.variation_id === product.variation_id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({
                variation_id: product.variation_id,
                product_id: product.product_id,
                name: product.product_name,
                sku: product.sku,
                unit_price: product.unit_price,
                quantity: 1,
                tax_id: product.tax_id
            });
        }
        updateCart();
    }

    // Update cart display
    function updateCart() {
        let html = '';
        let subtotal = 0;

        if (cart.length === 0) {
            html = '<p class="text-muted text-center" id="empty-cart">Keranjang kosong</p>';
        } else {
            cart.forEach((item, index) => {
                const total = item.quantity * item.unit_price;
                subtotal += total;
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${item.name}</h6>
                            <small class="text-muted">${item.sku} @ Rp ${item.unit_price.toLocaleString()}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary qty-minus" data-index="${index}">-</button>
                            <input type="number" class="form-control form-control-sm text-center mx-1 qty-input" data-index="${index}" value="${item.quantity}" style="width: 50px;">
                            <button class="btn btn-sm btn-outline-secondary qty-plus" data-index="${index}">+</button>
                            <button class="btn btn-sm btn-outline-danger ms-2 remove-item" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        $('#cart-items').html(html);
        const tax = subtotal * 0.1;
        const discount = parseFloat($('#discount').val()) || 0;
        const grandTotal = subtotal + tax - discount;

        $('#subtotal').text('Rp ' + subtotal.toLocaleString());
        $('#tax').text('Rp ' + tax.toLocaleString());
        $('#grand-total').text('Rp ' + grandTotal.toLocaleString());
        $('#modal-total').text('Rp ' + grandTotal.toLocaleString());
        $('#payment-amount').val(grandTotal);
        $('#btn-payment').prop('disabled', cart.length === 0);
    }

    // Quantity controls
    $(document).on('click', '.qty-minus', function() {
        const index = $(this).data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
        } else {
            cart.splice(index, 1);
        }
        updateCart();
    });

    $(document).on('click', '.qty-plus', function() {
        const index = $(this).data('index');
        cart[index].quantity++;
        updateCart();
    });

    $(document).on('change', '.qty-input', function() {
        const index = $(this).data('index');
        cart[index].quantity = parseInt($(this).val()) || 1;
        updateCart();
    });

    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCart();
    });

    // Clear cart
    $('#btn-clear-cart').click(function() {
        cart = [];
        updateCart();
    });

    // Discount
    $('#discount').on('input', function() {
        updateCart();
    });

    // Payment modal
    $('#btn-payment').click(function() {
        $('#paymentModal').modal('show');
        calculateChange();
    });

    $('#payment-amount').on('input', function() {
        calculateChange();
    });

    function calculateChange() {
        const total = parseFloat($('#modal-total').text().replace(/[^0-9]/g, ''));
        const paid = parseFloat($('#payment-amount').val()) || 0;
        const change = paid - total;
        $('#change-amount').val('Rp ' + Math.max(0, change).toLocaleString());
    }

    // Confirm payment
    $('#btn-confirm-payment').click(function() {
        const products = cart.map(item => ({
            variation_id: item.variation_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            tax_id: item.tax_id
        }));

        const formData = $('#payment-form').serialize() + '&' + $.param({products: products});

        $.post('/pos/store', formData, function(response) {
            window.location.href = '/pos';
        }).fail(function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Transaction failed'));
        });
    });
});
</script>
@endpush


