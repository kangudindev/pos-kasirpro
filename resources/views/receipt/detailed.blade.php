<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 11px; width: 100mm; margin: 0 auto; color: #222; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 1px solid #333; margin: 6px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 6px; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #333; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; }
        .header { font-size: 20px; font-weight: bold; }
        .address { font-size: 10px; color: #555; margin: 2px 0; }
        .totals td { border-bottom: none; padding: 3px 6px; }
        .grand-total td { font-size: 14px; font-weight: bold; border-top: 2px solid #333; padding: 8px 6px; }
        @media print { body { width: 100mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center">
        <div class="header">{{ $transaction->location->business->name ?? 'POS KasirPro' }}</div>
        <div class="address">{{ $transaction->location->address ?? '' }}</div>
        <div class="address">Telp: {{ $transaction->location->mobile ?? '' }} | Email: {{ $transaction->location->email ?? '' }}</div>
    </div>
    <div class="line"></div>
    <table style="margin-bottom:6px;">
        <tr><td><strong>Invoice:</strong> {{ $transaction->invoice_no }}</td><td class="right"><strong>Tanggal:</strong> {{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y H:i') }}</td></tr>
        <tr><td><strong>Kasir:</strong> {{ $transaction->createdBy->name ?? '' }}</td><td class="right"><strong>Customer:</strong> {{ $transaction->contact->name ?? 'Umum' }}</td></tr>
        @if($transaction->contact && $transaction->contact->mobile)<tr><td colspan="2"><strong>Telp:</strong> {{ $transaction->contact->mobile }}</td></tr>@endif
        @if($transaction->contact && $transaction->contact->address)<tr><td colspan="2"><strong>Alamat:</strong> {{ $transaction->contact->address }}</td></tr>@endif
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th style="width:5%;">#</th><th style="width:40%;">Item</th><th style="width:12%;">Qty</th><th style="width:18%;">Harga</th><th style="width:15%;">Subtotal</th></tr></thead>
        <tbody>
            @foreach($lines as $i => $line)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $line->product->name ?? '' }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="line"></div>
    <table class="totals" style="width:60%; float:right;">
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($transaction->total_before_tax, 0, ',', '.') }}</td></tr>
        @if($transaction->discount_amount > 0)<tr><td>Diskon</td><td class="right">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td></tr>@endif
        @if($transaction->tax_amount > 0)<tr><td>Pajak</td><td class="right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td></tr>@endif
        <tr class="grand-total"><td>TOTAL</td><td class="right">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td></tr>
    </table>
    <div style="clear:both;"></div>
    <table style="margin-top:10px;">
        @foreach($payments as $payment)
        <tr><td>{{ $payment->method_label }}</td><td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
        @endforeach
    </table>
    <div class="center" style="margin-top:16px; border-top:1px solid #333; padding-top:8px;">
        <p>Terima Kasih telah berbelanja</p>
    </div>
</body>
</html>
