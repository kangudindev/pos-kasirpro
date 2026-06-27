<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; font-size: 12px; width: 80mm; margin: 0 auto; color: #333; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 2px solid #8B4513; margin: 8px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px solid #8B4513; padding: 6px 0; color: #8B4513; font-size: 11px; }
        td { padding: 4px 0; }
        .header { font-size: 18px; font-weight: bold; color: #8B4513; }
        .subheader { font-size: 10px; color: #666; }
        .total-box { background: #faf0e6; padding: 8px; border-radius: 4px; margin: 8px 0; }
        @media print { body { width: 80mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center">
        <div class="header">{{ $transaction->location->business->name ?? 'POS KasirPro' }}</div>
        <p class="subheader">{{ $transaction->location->name ?? '' }} | {{ $transaction->location->address ?? '' }}</p>
        <p class="subheader">Telp: {{ $transaction->location->mobile ?? '' }}</p>
    </div>
    <div class="line"></div>
    <table>
        <tr><td>Invoice</td><td class="right">#{{ $transaction->invoice_no }}</td></tr>
        <tr><td>Tanggal</td><td class="right">{{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd F Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="right">{{ $transaction->createdBy->name ?? '' }}</td></tr>
        @if($transaction->contact)<tr><td>Customer</td><td class="right">{{ $transaction->contact->name ?? '' }}</td></tr>@endif
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th>Item</th><th class="right">Qty</th><th class="right">Price</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td>{{ $line->product->name ?? '' }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="line"></div>
    <div class="total-box">
        <table>
            <tr><td>Subtotal</td><td class="right">Rp {{ number_format($transaction->total_before_tax, 0, ',', '.') }}</td></tr>
            @if($transaction->discount_amount > 0)<tr><td>Diskon</td><td class="right">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td></tr>@endif
            @if($transaction->tax_amount > 0)<tr><td>Pajak</td><td class="right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td></tr>@endif
            <tr class="bold"><td>TOTAL</td><td class="right">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td></tr>
        </table>
    </div>
    <table>
        @foreach($payments as $payment)
        <tr><td>{{ $payment->method_label }}</td><td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
        @endforeach
    </table>
    <div class="center" style="margin-top:12px;">
        <p>Terima Kasih atas kunjungan Anda</p>
        <p style="font-size:10px; color:#999;">Barang yang sudah dibeli tidak dapat dikembalikan</p>
    </div>
</body>
</html>
