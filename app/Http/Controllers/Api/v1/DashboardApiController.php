<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Product;
use App\Models\Transaction\Transaction;

class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $businessId = $request->user()->business_id;

        $todaySales = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereDate('transaction_date', today())
            ->sum('final_total');

        $monthSales = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereMonth('transaction_date', now()->month)
            ->sum('final_total');

        $todayTransactions = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereDate('transaction_date', today())
            ->count();

        $totalProducts = Product::where('business_id', $businessId)
            ->where('is_active', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today_sales' => $todaySales,
                'month_sales' => $monthSales,
                'today_transactions' => $todayTransactions,
                'total_products' => $totalProducts,
            ],
        ]);
    }
}
