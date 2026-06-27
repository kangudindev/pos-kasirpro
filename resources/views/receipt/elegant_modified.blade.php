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
        .line { border-top: 1px solid #8B4513; margin: 6px 0; }
        .double-line { border-top: 3px double #8B4513; margin: 8px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px solid #8B4513; padding: 5px 0; color: #8B4513; font-size: 11px; }
        td { padding: 3px 0; }
        .header { font-size: 16px; font-weight: bold; color: #8B4513; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); font-size: 60px; color: rgba(139,69,19,0.05); font-weight: bold; z-index: -1; }
        .total-box { background: #faf0e6; padding: 6px 8px; border-radius: 4px; }
        @media print { body { width: 80mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="watermark">{{ $transaction->location->business->name ?? 'KasirPro' }}</div>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center">
        <div class="header">{{ $transaction->location->business->name ?? 'POS KasirPro' }}</div>
        <p style="font-size:10px; color:#666;">{{ $transaction->location->name ?? '' }} | {{ $transaction->location->address ?? '' }}</p>
    </div>
    <div class="line"></div>
    <table>
        <tr><td>Invoice #{{ $transaction->invoice_no }}</td><td class="right">{{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y H:i') }}</td></tr>
        <tr><td>Kasir: {{ $transaction->createdBy->name ?? '' }}</td><td class="right">{{ $transaction->contact->name ?? 'Umum' }}</td></tr>
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th>Item</th><th class="right">Qty</th><th class="right">@</th><th class="right">Total</th></tr></thead>
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
    <div class="double-line"></div>
    <div class="total-box">
        <table>
            <tr><td>Subtotal</td><td class="right">Rp {{ number_format($transaction->total_before_tax, 0, ',', '.') }}</td></tr>
            @if($transaction->discount_amount > 0)<tr><td>Diskon</td><td class="right">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td></tr>@endif
            @if($transaction->tax_amount > 0)<tr><td>Pajak ({{ $transaction->tax_rate ?? 0 }}%)</td><td class="right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td></tr>@endif
            <tr class="bold"><td>TOTAL</td><td class="right">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td></tr>
        </table>
    </div>
    <div class="center" style="margin-top:10px;"><p style="font-size:10px; color:#666;">Terima Kasih</p></div>
</body>
</html>
