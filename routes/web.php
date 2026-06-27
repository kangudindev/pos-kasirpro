<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\BrandController;
use App\Http\Controllers\Inventory\UnitController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Pos\SellPosController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\StockAdjustmentController;
use App\Http\Controllers\Finance\StockTransferController;
use App\Http\Controllers\Finance\CashRegisterController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\BarcodeCenter\BarcodeCenterController;
use App\Http\Controllers\Scale\ScaleController;
use App\Http\Controllers\Marketplace\MarketplaceController;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Public Invoice View
Route::get('/invoice/{token}', [InvoiceController::class, 'viewByToken'])->name('invoices.token');

// Authenticated Routes
Route::middleware(['auth', 'web'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Business Management
    Route::resource('business', BusinessController::class);
    Route::get('/business/{id}/locations', [BusinessController::class, 'locations'])->name('business.locations');
    Route::post('/business/{id}/locations', [BusinessController::class, 'storeLocation'])->name('business.locations.store');

    // Products (Inventory)
    Route::resource('products', ProductController::class);
    Route::get('/products/{id}/stock', [ProductController::class, 'stock'])->name('products.stock');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Brands
    Route::resource('brands', BrandController::class);

    // Units
    Route::resource('units', UnitController::class);

    // Contacts (Customers & Suppliers)
    Route::resource('contacts', ContactController::class);
    Route::get('/customers', [ContactController::class, 'customers'])->name('contacts.customers');
    Route::get('/suppliers', [ContactController::class, 'suppliers'])->name('contacts.suppliers');

    // POS (Point of Sale)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [SellPosController::class, 'index'])->name('index');
        Route::post('/store', [SellPosController::class, 'store'])->name('store');
        Route::get('/recent-transactions', [SellPosController::class, 'getRecentTransactions'])->name('recent');
        Route::get('/product-row/{variationId}', [SellPosController::class, 'getProductRow'])->name('product-row');
        Route::post('/search-barcode', [SellPosController::class, 'searchByBarcode'])->name('search-barcode');
        Route::post('/payment-row', [SellPosController::class, 'getPaymentRow'])->name('payment-row');
    });

    // Purchases
    Route::resource('purchases', PurchaseController::class);

    // Sells (List/View)
    Route::resource('sells', SellController::class)->except(['create', 'store']);

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Stock Management
    Route::resource('stock-adjustments', StockAdjustmentController::class);
    Route::resource('stock-transfers', StockTransferController::class);

    // Cash Register
    Route::prefix('cash-register')->name('cash-registers.')->group(function () {
        Route::get('/', [CashRegisterController::class, 'index'])->name('index');
        Route::post('/open', [CashRegisterController::class, 'open'])->name('open');
        Route::post('/close', [CashRegisterController::class, 'close'])->name('close');
        Route::get('/{id}', [CashRegisterController::class, 'show'])->name('show');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/suppliers', [ReportController::class, 'suppliers'])->name('suppliers');
    });

    // Invoice
    Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/{id}/{template?}', [InvoiceController::class, 'receipt'])->name('invoices.receipt');

    // ============================================================
    // PHASE 7-12: Additional Features
    // ============================================================

    // Membership
    Route::resource('memberships', MembershipController::class);
    Route::post('/memberships/{id}/add-points', [MembershipController::class, 'addPoints'])->name('memberships.add-points');
    Route::post('/memberships/{id}/redeem-points', [MembershipController::class, 'redeemPoints'])->name('memberships.redeem-points');

    // Barcode Center (Superadmin)
    Route::prefix('barcode-center')->name('barcode-center.')->group(function () {
        Route::get('/', [BarcodeCenterController::class, 'index'])->name('index');
        Route::get('/create', [BarcodeCenterController::class, 'create'])->name('create');
        Route::post('/store', [BarcodeCenterController::class, 'store'])->name('store');
        Route::get('/import', [BarcodeCenterController::class, 'import'])->name('import');
        Route::post('/process-import', [BarcodeCenterController::class, 'processImport'])->name('process-import');
        Route::get('/search', [BarcodeCenterController::class, 'search'])->name('search');
        Route::post('/add-to-tenant/{id}', [BarcodeCenterController::class, 'addToTenant'])->name('add-to-tenant');
        Route::post('/store-custom-product', [BarcodeCenterController::class, 'storeCustomProduct'])->name('store-custom');
        Route::get('/pending', [BarcodeCenterController::class, 'pendingProducts'])->name('pending');
        Route::post('/approve/{id}', [BarcodeCenterController::class, 'approveProduct'])->name('approve');
        Route::post('/reject/{id}', [BarcodeCenterController::class, 'rejectProduct'])->name('reject');
        Route::get('/categories', [BarcodeCenterController::class, 'categories'])->name('categories');
        Route::post('/categories', [BarcodeCenterController::class, 'storeCategory'])->name('categories.store');
        Route::get('/brands', [BarcodeCenterController::class, 'brands'])->name('brands');
        Route::post('/brands', [BarcodeCenterController::class, 'storeBrand'])->name('brands.store');
        Route::get('/barcode/{barcode}/{type?}', [BarcodeCenterController::class, 'generateBarcode'])->name('generate');
        Route::post('/print-labels', [BarcodeCenterController::class, 'printLabels'])->name('print-labels');
    });

    // Scale Integration
    Route::resource('scales', ScaleController::class)->except(['edit', 'update']);
    Route::get('/scales/plu/list', [ScaleController::class, 'pluIndex'])->name('scales.plu');
    Route::post('/scales/plu/store', [ScaleController::class, 'pluStore'])->name('scales.plu.store');
    Route::delete('/scales/plu/{id}', [ScaleController::class, 'pluDestroy'])->name('scales.plu.destroy');
    Route::get('/scales/label-templates', [ScaleController::class, 'labelTemplates'])->name('scales.label-templates');
    Route::post('/scales/label-templates', [ScaleController::class, 'storeLabelTemplate'])->name('scales.label-templates.store');
    Route::post('/scales/weight', [ScaleController::class, 'getWeight'])->name('scales.weight');
    Route::post('/scales/send-plu', [ScaleController::class, 'sendPlu'])->name('scales.send-plu');

    // Marketplace Integration
    Route::resource('marketplace', MarketplaceController::class)->except(['edit', 'update']);
    Route::post('/marketplace/{id}/sync-products', [MarketplaceController::class, 'syncProducts'])->name('marketplace.sync-products');
    Route::post('/marketplace/{id}/sync-orders', [MarketplaceController::class, 'syncOrders'])->name('marketplace.sync-orders');
    Route::get('/marketplace/{id}/orders', [MarketplaceController::class, 'orders'])->name('marketplace.orders');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function () { return view('settings.index'); })->name('index');
        Route::get('/business', function () { return view('settings.business'); })->name('business');
        Route::get('/invoice', function () { return view('settings.invoice'); })->name('invoice');
        Route::get('/tax', function () { return view('settings.tax'); })->name('tax');
    });
});

// API Routes (Mobile App)
Route::prefix('api/v1')->name('api.')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\v1\AuthApiController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Api\v1\AuthApiController::class, 'logout'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Api\v1\AuthApiController::class, 'profile']);
        Route::get('/products', [\App\Http\Controllers\Api\v1\ProductApiController::class, 'index']);
        Route::get('/products/{id}', [\App\Http\Controllers\Api\v1\ProductApiController::class, 'show']);
        Route::post('/pos/sale', [\App\Http\Controllers\Api\v1\PosApiController::class, 'createSale']);
        Route::get('/pos/recent', [\App\Http\Controllers\Api\v1\PosApiController::class, 'recentTransactions']);
        Route::post('/pos/search-barcode', [\App\Http\Controllers\Api\v1\PosApiController::class, 'searchBarcode']);
        Route::get('/dashboard', [\App\Http\Controllers\Api\v1\DashboardApiController::class, 'index']);
        
        // Payment API
        Route::post('/payment/create', [\App\Http\Controllers\Api\PaymentController::class, 'createTransaction']);
        Route::post('/payment/status', [\App\Http\Controllers\Api\PaymentController::class, 'checkStatus']);
    });
    
    // Midtrans webhook (no auth)
    Route::post('/payment/webhook', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);
});
