<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Tahoma', sans-serif; font-size: 11px; width: 80mm; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .line { border-top: 1px solid #000; margin: 4px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px solid #000; padding: 4px 0; }
        td { padding: 2px 0; }
        .ar { direction: rtl; text-align: right; font-family: 'Tahoma', sans-serif; }
        .en { direction: ltr; text-align: left; }
        .header-ar { font-size: 14px; font-weight: bold; direction: rtl; }
        .header-en { font-size: 12px; font-weight: bold; }
        @media print { body { width: 80mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print mb-2"><button onclick="window.print()" class="btn btn-sm btn-primary">Print</button></div>
    <table>
        <tr>
            <td class="en" style="width:50%;">
                <div class="header-en">{{ $transaction->location->business->name ?? 'POS KasirPro' }}</div>
                <p style="font-size:9px;">{{ $transaction->location->name ?? '' }}</p>
            </td>
            <td class="ar" style="width:50%;">
                <div class="header-ar">{{ $transaction->location->business->name ?? 'كشير برو' }}</div>
                <p style="font-size:9px;">{{ $transaction->location->name ?? '' }}</p>
            </td>
        </tr>
    </table>
    <div class="line"></div>
    <table>
        <tr>
            <td class="en"><strong>Invoice:</strong> {{ $transaction->invoice_no }}</td>
            <td class="ar"><strong>رقم الفاتورة:</strong> {{ $transaction->invoice_no }}</td>
        </tr>
        <tr>
            <td class="en"><strong>Date:</strong> {{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y') }}</td>
            <td class="ar"><strong>التاريخ:</strong> {{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y') }}</td>
        </tr>
        <tr>
            <td class="en"><strong>Cashier:</strong> {{ $transaction->createdBy->name ?? '' }}</td>
            <td class="ar"><strong>الكاشير:</strong> {{ $transaction->createdBy->name ?? '' }}</td>
        </tr>
    </table>
    <div class="line"></div>
    <table>
        <thead><tr><th class="en">Item</th><th class="ar">الصنف</th><th class="right">Qty</th><th class="right">Price</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td class="en">{{ $line->product->name ?? '' }}</td>
                <td class="ar">{{ $line->product->name ?? '' }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="line"></div>
    <table>
        <tr><td class="en">Total</td><td class="ar">المجموع</td><td class="right bold">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td></tr>
    </table>
    <div class="center" style="margin-top:6px;">
        <p>Terima Kasih / شكرا جزيلا</p>
    </div>
</body>
</html>
