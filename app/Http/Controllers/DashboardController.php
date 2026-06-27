<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\Transaction;
use App\Models\Product\Product;
use App\Models\Contact\Contact;

class DashboardController extends Controller
{
    public function index()
    {
        $businessId = session('current_business_id');
        $locationId = session('current_location_id', 1);

        // Today's sales
        $todaySales = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereDate('transaction_date', today())
            ->sum('final_total');

        // This month's sales
        $monthSales = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('final_total');

        // Today's transactions count
        $todayTransactions = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereDate('transaction_date', today())
            ->count();

        // Total products
        $totalProducts = Product::where('business_id', $businessId)
            ->where('is_active', true)
            ->count();

        // Total customers
        $totalCustomers = Contact::where('business_id', $businessId)
            ->where('type', 'customer')
            ->count();

        // Low stock products (count products with stock below alert threshold)
        $lowStockProducts = DB::table('transaction_sell_lines')
            ->select('product_id')
            ->whereIn('transaction_id', function ($query) use ($businessId) {
                $query->select('id')->from('transactions')
                    ->where('business_id', $businessId)->where('type', 'sell');
            })
            ->groupBy('product_id')
            ->havingRaw('SUM(quantity) < (SELECT alert_quantity FROM products WHERE products.id = product_id)')
            ->count();

        // Recent sales
        $recentSales = Transaction::with('contact')
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->latest()
            ->limit(5)
            ->get();

        // Top selling products - eager load products
        $topProductIds = DB::table('transaction_sell_lines')
            ->select('product_id')
            ->whereIn('transaction_id', function ($query) use ($businessId) {
                $query->select('id')
                    ->from('transactions')
                    ->where('business_id', $businessId)
                    ->where('type', 'sell');
            })
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('SUM(quantity * unit_price)'))
            ->limit(5)
            ->pluck('product_id');

        $topProducts = Product::whereIn('id', $topProductIds)
            ->with(['sellableVariations'])
            ->get()
            ->mapWithKeys(function ($product) use ($businessId) {
                $qty = DB::table('transaction_sell_lines')
                    ->where('product_id', $product->id)
                    ->whereIn('transaction_id', function ($q) use ($businessId) {
                        $q->select('id')->from('transactions')
                          ->where('business_id', $businessId)->where('type', 'sell');
                    })
                    ->sum('quantity');

                $revenue = DB::table('transaction_sell_lines')
                    ->where('product_id', $product->id)
                    ->whereIn('transaction_id', function ($q) use ($businessId) {
                        $q->select('id')->from('transactions')
                          ->where('business_id', $businessId)->where('type', 'sell');
                    })
                    ->sum(DB::raw('quantity * unit_price'));

                return [$product->id => ['product_id' => $product->id, 'total_qty' => $qty, 'total_revenue' => $revenue]];
            })
            ->sortByDesc('total_revenue')
            ->values();

        // Sales chart data (last 7 days)
        $chartData = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('transaction_date', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(final_total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard', compact(
            'todaySales', 'monthSales', 'todayTransactions',
            'totalProducts', 'totalCustomers', 'lowStockProducts',
            'recentSales', 'topProducts', 'chartData'
        ));
    }
}
