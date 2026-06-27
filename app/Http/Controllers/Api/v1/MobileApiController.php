<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth\User;
use App\Models\Product\Product;
use App\Models\Contact\Contact;
use App\Models\Transaction\Transaction;

class AuthApiController extends Controller
{
    /**
     * Login for mobile app
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
                'business' => $user->business,
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load('business');

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}

class ProductApiController extends Controller
{
    /**
     * List products
     */
    public function index(Request $request)
    {
        $businessId = $request->user()->business_id;

        $query = Product::with(['category', 'brand', 'sellableVariations'])
            ->where('business_id', $businessId)
            ->active();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get product details
     */
    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'unit', 'sellableVariations', 'images'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}

class PosApiController extends Controller
{
    /**
     * Create sale from mobile
     */
    public function createSale(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'products' => 'required|array|min:1',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->business_id;
        $locationId = $request->input('location_id', 1);

        // Use TransactionUtil to create sale
        $transactionUtil = app(\App\Utils\TransactionUtil::class);
        $productUtil = app(\App\Utils\ProductUtil::class);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Create transaction
            $transaction = $transactionUtil->createSellTransaction($request, $businessId);

            // Create sell lines
            $finalTotal = $transactionUtil->createOrUpdateSellLines($request, $businessId, $transaction->id);

            // Create payment
            $transactionUtil->createOrUpdatePaymentLines($request, $businessId, $transaction->id);

            // Decrease stock
            $productUtil->updateProductStock($transaction->id, 'sell', 'final');

            // Update payment status
            $transactionUtil->updatePaymentStatus($transaction->id);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'invoice_no' => $transaction->invoice_no,
                    'total' => $finalTotal,
                    'paid' => $request->amount_paid,
                ],
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent transactions
     */
    public function recentTransactions(Request $request)
    {
        $businessId = $request->user()->business_id;

        $transactions = Transaction::with('contact')
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Search product by barcode
     */
    public function searchBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $businessId = $request->user()->business_id;

        $variation = \App\Models\Product\Variation::whereHas('product', function ($q) use ($barcode, $businessId) {
            $q->where('business_id', $businessId)
              ->where('sku', $barcode);
        })->orWhere('sub_sku', $barcode)
          ->first();

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'variation_id' => $variation->id,
                'product_id' => $variation->product_id,
                'name' => $variation->product->name,
                'sku' => $variation->sub_sku ?? $variation->product->sku,
                'price' => $variation->default_sell_price,
                'stock' => \App\Utils\ProductUtil::getCurrentStock($variation->id, $request->input('location_id', 1)),
            ],
        ]);
    }
}

class DashboardApiController extends Controller
{
    /**
     * Get dashboard stats
     */
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
