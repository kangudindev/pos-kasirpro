<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delivery Note {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; width: 100mm; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 1px solid #333; margin: 8px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f0f0f0; padding: 6px; text-align: left; font-size: 11px; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; }
        .header { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #555; }
        @media print { body { width: 100mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <div class="center">
        <div class="header">{{ $transaction->location->business->name ?? 'POS KasirPro' }}</div>
        <div class="subtitle">SURAT JALAN</div>
    </div>
    <div class="line"></div>
    <table>
        <tr><td><strong>No. SJ:</strong> {{ $transaction->invoice_no }}</td><td class="right"><strong>Tanggal:</strong> {{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y') }}</td></tr>
        <tr><td><strong>Kepada:</strong> {{ $transaction->contact->name ?? '-' }}</td><td class="right"><strong>Telp:</strong> {{ $transaction->contact->mobile ?? '-' }}</td></tr>
        <tr><td colspan="2"><strong>Alamat:</strong> {{ $transaction->contact->address ?? '-' }}</td></tr>
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Satuan</th><th>Keterangan</th></tr></thead>
        <tbody>
            @foreach($lines as $i => $line)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $line->product->name ?? '' }}</td>
                <td>{{ $line->quantity }}</td>
                <td>{{ $line->product->unit->short_name ?? 'pcs' }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:30px;">
        <table>
            <tr>
                <td class="center" style="border:none;"><br><br><br>(________________________)<br>Penerima</td>
                <td class="center" style="border:none;"><br><br><br>(________________________)<br>Pengirim</td>
                <td class="center" style="border:none;"><br><br><br>(________________________)<br>Mengetahui</td>
            </tr>
        </table>
    </div>
</body>
</html>
