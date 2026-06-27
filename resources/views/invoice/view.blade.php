<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; }
        .logo { max-height: 80px; }
        .table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('invoices.pdf', $transaction->id) }}" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
    </div>

    <div class="invoice-box">
        <div class="row mb-4">
            <div class="col-6">
                <h3>{{ $transaction->location->business->name ?? 'POS KasirPro' }}</h3>
                <p class="mb-0">{{ $transaction->location->name ?? '' }}</p>
                <p class="mb-0">{{ $transaction->location->address ?? '' }}</p>
                <p class="mb-0">{{ $transaction->location->city ?? '' }}, {{ $transaction->location->state ?? '' }}</p>
                <p>Telp: {{ $transaction->location->mobile ?? '' }}</p>
            </div>
            <div class="col-6 text-end">
                <h4>INVOICE</h4>
                <table class="table table-sm table-borderless">
                    <tr><td>No.</td><td><strong>{{ $transaction->invoice_no }}</strong></td></tr>
                    <tr><td>Tanggal</td><td>{{ \App\Utils\Util::format_datetime($transaction->transaction_date) }}</td></tr>
                    <tr><td>Kasir</td><td>{{ $transaction->createdBy->name ?? '' }}</td></tr>
                </table>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
            <div class="col-6">
                <h6>Bill To:</h6>
                <strong>{{ $transaction->contact->name ?? 'Walk-in Customer' }}</strong>
                <p class="mb-0">{{ $transaction->contact->address ?? '' }}</p>
                <p>{{ $transaction->contact->mobile ?? '' }}</p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $line->product->name ?? '' }}
                        @if($line->variation && !$line->variation->productVariation?->is_dummy)
                            <br><small class="text-muted">{{ $line->variation->full_name }}</small>
                        @endif
                    </td>
                    <td class="text-end">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $line->quantity }}</td>
                    <td class="text-end">Rp {{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                    <td class="text-end"><strong>Rp {{ number_format($transaction->total_before_tax, 0, ',', '.') }}</strong></td>
                </tr>
                @if($transaction->tax_amount > 0)
                <tr>
                    <td colspan="4" class="text-end">Pajak</td>
                    <td class="text-end">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($transaction->discount_amount > 0)
                <tr>
                    <td colspan="4" class="text-end text-danger">Diskon</td>
                    <td class="text-end text-danger">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($transaction->shipping_charges > 0)
                <tr>
                    <td colspan="4" class="text-end">Pengiriman</td>
                    <td class="text-end">Rp {{ number_format($transaction->shipping_charges, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="table-primary">
                    <td colspan="4" class="text-end"><strong>TOTAL</strong></td>
                    <td class="text-end"><strong>Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="row">
            <div class="col-6">
                <h6>Pembayaran:</h6>
                @foreach($payments as $payment)
                <p class="mb-0">{{ $payment->method_label }}: Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                @endforeach
            </div>
            <div class="col-6 text-end">
                <h6>Status: 
                    <span class="badge {{ $transaction->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                        {{ $transaction->payment_status == 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                    </span>
                </h6>
            </div>
        </div>

        <hr>
        <p class="text-center text-muted">Terima kasih atas kunjungan Anda!</p>
    </div>
</body>
</html>
