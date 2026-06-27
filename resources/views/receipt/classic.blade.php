<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $transaction->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; }
        td { padding: 2px 0; }
        @media print {
            body { width: 80mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-2">
        <button onclick="window.print()" class="btn btn-sm btn-primary">Print Struk</button>
    </div>

    <div class="center">
        <h4>{{ $transaction->location->business->name ?? 'POS KasirPro' }}</h4>
        <p>{{ $transaction->location->name ?? '' }}</p>
        <p>{{ $transaction->location->address ?? '' }}</p>
        <p>Telp: {{ $transaction->location->mobile ?? '' }}</p>
    </div>

    <div class="line"></div>

    <table>
        <tr>
            <td>No</td>
            <td>: {{ $transaction->invoice_no }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \App\Utils\Util::format_datetime($transaction->transaction_date, 'd/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $transaction->createdBy->name ?? '' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Harga</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td>{{ substr($line->product->name ?? '', 0, 15) }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($line->quantity * $line->unit_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">Rp {{ number_format($transaction->total_before_tax, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->tax_amount > 0)
        <tr>
            <td>Pajak</td>
            <td class="right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->discount_amount > 0)
        <tr>
            <td>Diskon</td>
            <td class="right">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="bold">
            <td>TOTAL</td>
            <td class="right">Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        @foreach($payments as $payment)
        <tr>
            <td>{{ $payment->method_label }}</td>
            <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        @if($payments->sum('amount') > $transaction->final_total)
        <tr>
            <td>Kembali</td>
            <td class="right">Rp {{ number_format($payments->sum('amount') - $transaction->final_total, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    <div class="center">
        <p>*** TERIMA KASIH ***</p>
        <p>Barang yang sudah dibeli</p>
        <p>tidak dapat dikembalikan</p>
    </div>
</body>
</html>
