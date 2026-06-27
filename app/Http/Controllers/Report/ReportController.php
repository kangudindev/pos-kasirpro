<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\Transaction;
use App\Models\Product\Product;
use App\Models\Contact\Contact;

class ReportController extends Controller
{
    public function index()
    {
        return view('report.index');
    }

    /**
     * Sales Report
     */
    public function sales(Request $request)
    {
        $businessId = session('current_business_id');
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $query = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $sales = $query->get();

        $summary = [
            'total_sales' => $sales->sum('final_total'),
            'total_tax' => $sales->sum('tax_amount'),
            'total_discount' => $sales->sum('discount_amount'),
            'total_shipping' => $sales->sum('shipping_charges'),
            'total_paid' => $sales->sum(fn($s) => $s->payments->sum('amount')),
            'total_due' => $sales->sum('final_total') - $sales->sum(fn($s) => $s->payments->sum('amount')),
            'transaction_count' => $sales->count(),
        ];

        return view('report.sales', compact('sales', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Purchase Report
     */
    public function purchases(Request $request)
    {
        $businessId = session('current_business_id');
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $purchases = Transaction::with('contact')
            ->where('business_id', $businessId)
            ->where('type', 'purchase')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $summary = [
            'total_purchases' => $purchases->sum('final_total'),
            'total_tax' => $purchases->sum('tax_amount'),
            'total_paid' => $purchases->sum(fn($p) => $p->payments->sum('amount')),
            'total_due' => $purchases->sum('final_total') - $purchases->sum(fn($p) => $p->payments->sum('amount')),
            'transaction_count' => $purchases->count(),
        ];

        return view('report.purchases', compact('purchases', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Stock Report
     */
    public function stock(Request $request)
    {
        $businessId = session('current_business_id');

        $products = Product::with(['sellableVariations.locationDetails', 'category', 'brand'])
            ->where('business_id', $businessId)
            ->where('enable_stock', true)
            ->get();

        $stockData = [];
        foreach ($products as $product) {
            $totalStock = 0;
            foreach ($product->sellableVariations as $variation) {
                $totalStock += $variation->locationDetails->sum('qty_available');
            }

            $stockData[] = [
                'product' => $product,
                'total_stock' => $totalStock,
                'is_low' => $totalStock <= $product->alert_quantity && $product->alert_quantity > 0,
            ];
        }

        return view('report.stock', compact('stockData'));
    }

    /**
     * Expense Report
     */
    public function expenses(Request $request)
    {
        $businessId = session('current_business_id');
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $expenses = Transaction::with(['contact'])
            ->where('business_id', $businessId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $summary = [
            'total_expenses' => $expenses->sum('final_total'),
            'transaction_count' => $expenses->count(),
        ];

        return view('report.expenses', compact('expenses', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Profit & Loss Report
     */
    public function profitLoss(Request $request)
    {
        $businessId = session('current_business_id');
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $totalSales = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('final_total');

        $totalPurchases = Transaction::where('business_id', $businessId)
            ->where('type', 'purchase')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('final_total');

        $totalExpenses = Transaction::where('business_id', $businessId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('final_total');

        $grossProfit = $totalSales - $totalPurchases;
        $netProfit = $grossProfit - $totalExpenses;

        return view('report.profit_loss', compact(
            'totalSales', 'totalPurchases', 'totalExpenses',
            'grossProfit', 'netProfit', 'startDate', 'endDate'
        ));
    }

    /**
     * Customer Report
     */
    public function customers()
    {
        $businessId = session('current_business_id');

        $customers = Contact::where('business_id', $businessId)
            ->customers()
            ->withSum('sellTransactions as total_sales', 'final_total')
            ->withSum('purchaseTransactions as total_purchases', 'final_total')
            ->get();

        return view('report.customers', compact('customers'));
    }

    /**
     * Supplier Report
     */
    public function suppliers()
    {
        $businessId = session('current_business_id');

        $suppliers = Contact::where('business_id', $businessId)
            ->suppliers()
            ->withSum('purchaseTransactions as total_purchases', 'final_total')
            ->get();

        return view('report.suppliers', compact('suppliers'));
    }
}
