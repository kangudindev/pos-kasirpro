<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Packing Slip {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 11px; width: 80mm; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-bottom: 1px solid #ccc; margin: 4px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 2px solid #333; padding: 4px 0; font-size: 10px; }
        td { padding: 3px 0; border-bottom: 1px solid #eee; }
        .header { font-size: 14px; font-weight: bold; }
        @media print { body { width: 80mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center">
        <div class="header">PACKING SLIP</div>
        <p style="font-size:10px;">{{ $transaction->location->business->name ?? '' }}</p>
    </div>
    <div class="line"></div>
    <table>
        <tr><td>Order #{{ $transaction->invoice_no }}</td><td class="right">{{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y') }}</td></tr>
        <tr><td>Customer: {{ $transaction->contact->name ?? 'Walk-in' }}</td></tr>
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th>Item</th><th class="right">Qty</th><th class="right">Packed</th></tr></thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td>{{ $line->product->name ?? '' }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:20px;">
        <p>Packed by: (________________________)</p>
        <p style="margin-top:5px;">Checked by: (________________________)</p>
    </div>
    <div class="center" style="margin-top:12px; font-size:10px; color:#999;">
        <p>Terima Kasih</p>
    </div>
</body>
</html>
