<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\Transaction;
use App\Utils\TransactionUtil;

class InvoiceController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * View invoice by token (public)
     */
    public function viewByToken($token)
    {
        $transaction = Transaction::with(['contact', 'sellLines.product', 'sellLines.variation', 'payments', 'location.business'])
            ->where('invoice_token', $token)
            ->firstOrFail();

        return view('invoice.view', compact('transaction'));
    }

    /**
     * Print invoice
     */
    public function print($id)
    {
        $receipt = $this->transactionUtil->getReceiptDetails($id);

        return view('invoice.print', $receipt);
    }

    /**
     * Generate PDF invoice
     */
    public function pdf($id)
    {
        $receipt = $this->transactionUtil->getReceiptDetails($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.pdf', $receipt)
            ->setPaper('a4')
            ->setOptions([
                'isHtml5Parser' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("invoice-{$receipt['transaction']->invoice_no}.pdf");
    }

    /**
     * Receipt templates
     */
    public function receipt($id, $template = 'classic')
    {
        $receipt = $this->transactionUtil->getReceiptDetails($id);

        return view("receipt.{$template}", $receipt);
    }
}

class BarcodeLabelController extends Controller
{
    /**
     * Print barcode labels
     */
    public function print(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'template_id' => 'nullable|exists:scale_label_templates,id',
        ]);

        $product = \App\Models\Product\Product::with('sellableVariations')->findOrFail($request->product_id);

        $data = [
            'product' => $product,
            'variation' => $product->sellableVariations->first(),
            'quantity' => $request->quantity,
        ];

        return view('barcode.print', $data);
    }

    /**
     * Generate barcode image
     */
    public function generate($code, $type = 'C128')
    {
        // In production, use a barcode library like picqer/php-barcode-generator
        // For now, return a placeholder
        return response()->json([
            'code' => $code,
            'type' => $type,
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);
    }
}
