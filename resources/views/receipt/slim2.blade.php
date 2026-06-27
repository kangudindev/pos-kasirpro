<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 9px; width: 48mm; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 1px solid #000; margin: 2px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; }
        td { padding: 1px 0; font-size: 9px; }
        .header { font-size: 11px; font-weight: bold; }
        @media print { body { width: 48mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center"><div class="header">{{ $transaction->location->business->name ?? '' }}</div></div>
    <div class="line"></div>
    <table>
        <tr><td>#{{ $transaction->invoice_no }}</td><td class="right">{{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m H:i') }}</td></tr>
    </table>
    <div class="line"></div>
    @foreach($lines as $line)
    <table><tr><td>{{ substr($line->product->name ?? '', 0, 10) }}</td><td class="right">{{ $line->quantity }}x{{ number_format($line->unit_price, 0, ',', '.') }}</td><td class="right">{{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td></tr></table>
    @endforeach
    <div class="line"></div>
    <table><tr class="bold"><td>TOTAL</td><td class="right">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td></tr></table>
</body>
</html>
