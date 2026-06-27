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

        // Low stock products
        $lowStockProducts = Product::where('business_id', $businessId)
            ->where('enable_stock', true)
            ->where('alert_quantity', '>', 0)
            ->count();

        // Recent sales
        $recentSales = Transaction::with('contact')
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->latest()
            ->limit(5)
            ->get();

        // Top selling products
        $topProducts = DB::table('transaction_sell_lines')
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(quantity * unit_price) as total_revenue'))
            ->whereIn('transaction_id', function ($query) use ($businessId) {
                $query->select('id')
                    ->from('transactions')
                    ->where('business_id', $businessId)
                    ->where('type', 'sell');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

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
