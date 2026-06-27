<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 11px; width: 80mm; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 4px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px solid #000; padding: 4px 0; font-size: 10px; }
        td { padding: 2px 0; }
        .tax-row td { font-size: 10px; color: #555; }
        @media print { body { width: 80mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center"><h4>{{ $transaction->location->business->name ?? 'POS KasirPro' }}</h4></div>
    <div class="line"></div>
    <table>
        <tr><td>#{{ $transaction->invoice_no }}</td><td class="right">{{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y') }}</td></tr>
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th>Item</th><th class="right">Qty</th><th class="right">Harga</th><th class="right">Disc</th><th class="right">Tax</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td>{{ substr($line->product->name ?? '', 0, 10) }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="right">{{ $line->discount_percent ?? 0 }}%</td>
                <td class="right">{{ $line->tax_percent ?? 0 }}%</td>
                <td class="right">{{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
            </tr>
            <tr class="tax-row"><td colspan="6">Tax: {{ number_format($line->tax_amount ?? 0, 0, ',', '.') }} | Disc: {{ number_format($line->discount_amount ?? 0, 0, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="line"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($transaction->total_before_tax, 0, ',', '.') }}</td></tr>
        @if($transaction->discount_amount > 0)<tr><td>Diskon</td><td class="right">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td></tr>@endif
        @if($transaction->tax_amount > 0)
        <tr><td>PPN {{ $transaction->tax_rate ?? 0 }}%</td><td class="right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="bold"><td>TOTAL</td><td class="right">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td></tr>
    </table>
</body>
</html>
