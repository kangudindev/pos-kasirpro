<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Auth\User;
use App\Models\Business\Business;
use App\Models\Business\BusinessLocation;
use App\Models\Business\Currency;

class KasirProSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User Management
            'users.view', 'users.create', 'users.edit', 'users.delete',

            // Product Management
            'products.view', 'products.create', 'products.edit', 'products.delete',

            // Brand Management
            'brands.view', 'brands.create', 'brands.edit', 'brands.delete',

            // Category Management
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',

            // Unit Management
            'units.view', 'units.create', 'units.edit', 'units.delete',

            // Tax Rate Management
            'tax_rates.view', 'tax_rates.create', 'tax_rates.edit', 'tax_rates.delete',

            // Contact Management
            'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',

            // Purchase Management
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',

            // Sell/POS Management
            'sells.view', 'sells.create', 'sells.edit', 'sells.delete',

            // Expense Management
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',

            // Stock Management
            'stock_adjustments.view', 'stock_adjustments.create', 'stock_adjustments.edit', 'stock_adjustments.delete',
            'stock_transfers.view', 'stock_transfers.create', 'stock_transfers.edit', 'stock_transfers.delete',

            // Reports
            'reports.sales', 'reports.purchases', 'reports.contacts', 'reports.stock',
            'reports.tax', 'reports.trending_product', 'reports.register',
            'reports.sales_representative', 'reports.expense', 'reports.profit_loss',
            
            // Payment Permissions
            'payments.create', 'payments.view', 'payments.process',

            // Settings
            'settings.business', 'settings.invoice', 'settings.barcode', 'settings.printer',

            // Dashboard
            'dashboard.data',

            // All Locations
            'access_all_locations',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $warehouseStaff = Role::firstOrCreate(['name' => 'warehouse_staff', 'guard_name' => 'web']);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        // Assign permissions to roles
        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'products.view', 'products.create', 'products.edit',
            'brands.view', 'brands.create', 'brands.edit',
            'categories.view', 'categories.create', 'categories.edit',
            'units.view', 'contacts.view', 'contacts.create', 'contacts.edit',
            'purchases.view', 'purchases.create', 'purchases.edit',
            'sells.view', 'sells.create', 'sells.edit',
            'expenses.view', 'expenses.create', 'expenses.edit',
            'stock_adjustments.view', 'stock_adjustments.create',
            'stock_transfers.view', 'stock_transfers.create',
            'reports.sales', 'reports.purchases', 'reports.contacts',
            'reports.stock', 'reports.expense',
            'dashboard.data',
        ]);

        $cashier->syncPermissions([
            'products.view',
            'contacts.view', 'contacts.create',
            'sells.view', 'sells.create',
            'dashboard.data',
        ]);

        $warehouseStaff->syncPermissions([
            'products.view', 'products.edit',
            'stock_adjustments.view', 'stock_adjustments.create',
            'stock_transfers.view', 'stock_transfers.create',
            'reports.stock',
        ]);

        $accountant->syncPermissions([
            'expenses.view', 'expenses.create', 'expenses.edit',
            'reports.sales', 'reports.purchases', 'reports.expense',
            'reports.profit_loss',
            'dashboard.data',
        ]);

        // Create Default Currency (IDR)
        $currency = Currency::firstOrCreate(
            ['code' => 'IDR'],
            [
                'name' => 'Indonesian Rupiah',
                'symbol' => 'Rp',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                'exchange_rate' => 1,
                'is_default' => true,
            ]
        );

        // Create Demo Business
        $demoBusiness = Business::firstOrCreate(
            ['name' => 'Demo Supermarket'],
            [
                'owner_id' => 1,
                'currency_id' => $currency->id,
                'timezone' => 'Asia/Jakarta',
                'accounting_method' => 'fifo',
                'sell_price_tax' => 'excludes',
                'is_active' => true,
            ]
        );

        // Create Demo Location
        BusinessLocation::firstOrCreate(
            ['name' => 'Toko Pusat'],
            [
                'business_id' => $demoBusiness->id,
                'country' => 'Indonesia',
                'state' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'zip_code' => '12190',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        // Create Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@kasirpro.com'],
            [
                'business_id' => $demoBusiness->id,
                'first_name' => 'Admin',
                'last_name' => 'KasirPro',
                'name' => 'Admin KasirPro',
                'phone' => '08123456789',
                'is_active' => true,
                'password' => bcrypt('12345678'),
            ]
        );
        $adminUser->assignRole('admin');

        // Create Cashier User
        $cashierUser = User::firstOrCreate(
            ['email' => 'kasir@kasirpro.com'],
            [
                'business_id' => $demoBusiness->id,
                'first_name' => 'Kasir',
                'last_name' => 'Utama',
                'name' => 'Kasir Utama',
                'phone' => '08123456780',
                'is_active' => true,
                'password' => bcrypt('12345678'),
            ]
        );
        $cashierUser->assignRole('cashier');

        // Create Demo Categories
        $categories = ['Makanan', 'Minuman', 'Snack', 'Elektronik', 'Rumah Tangga', 'Perlengkapan', 'Lainnya'];
        foreach ($categories as $cat) {
            \App\Models\Product\Category::firstOrCreate([
                'business_id' => $demoBusiness->id,
                'name' => $cat,
            ]);
        }

        // Create Demo Brands
        $brands = ['Indomie', 'Nestle', 'Aqua', 'Mayora', 'Unilever', 'Samsung', 'LG'];
        foreach ($brands as $brand) {
            \App\Models\Product\Brand::firstOrCreate([
                'business_id' => $demoBusiness->id,
                'name' => $brand,
            ]);
        }

        // Create Demo Units
        $units = ['Pcs', 'Kg', 'Gram', 'Lembar', 'Pack', 'Dus', 'Bungkus'];
        foreach ($units as $unit) {
            \App\Models\Product\Unit::firstOrCreate([
                'business_id' => $demoBusiness->id,
                'short_name' => $unit,
                'name' => $unit,
            ]);
        }

        // Create Demo Products (50+)
        $products = [
            ['sku' => 'PRD-001', 'name' => 'Indomie Goreng', 'category_id' => 1, 'brand_id' => 1, 'unit_id' => 5, 'purchase_price' => 2000, 'sell_price' => 2500],
            ['sku' => 'PRD-002', 'name' => 'Indomie Kuah', 'category_id' => 1, 'brand_id' => 1, 'unit_id' => 5, 'purchase_price' => 2000, 'sell_price' => 2500],
            ['sku' => 'PRD-003', 'name' => 'Aqua 600ml', 'category_id' => 2, 'brand_id' => 3, 'unit_id' => 1, 'purchase_price' => 3000, 'sell_price' => 4000],
            ['sku' => 'PRD-004', 'name' => 'Aqua Galon', 'category_id' => 2, 'brand_id' => 3, 'unit_id' => 1, 'purchase_price' => 15000, 'sell_price' => 18000],
            ['sku' => 'PRD-005', 'name' => 'Teh Botol Sosro', 'category_id' => 2, 'brand_id' => 4, 'unit_id' => 1, 'purchase_price' => 3500, 'sell_price' => 4500],
            ['sku' => 'PRD-006', 'name' => 'Kopi ABC Susu', 'category_id' => 2, 'brand_id' => 4, 'unit_id' => 1, 'purchase_price' => 1800, 'sell_price' => 2500],
            ['sku' => 'PRD-007', 'name' => 'Pocari Sweat', 'category_id' => 2, 'brand_id' => 3, 'unit_id' => 1, 'purchase_price' => 7000, 'sell_price' => 8000],
            ['sku' => 'PRD-008', 'name' => 'Chiki Ball', 'category_id' => 3, 'brand_id' => 4, 'unit_id' => 1, 'purchase_price' => 1000, 'sell_price' => 1500],
            ['sku' => 'PRD-009', 'name' => 'Chitato', 'category_id' => 3, 'brand_id' => 4, 'unit_id' => 1, 'purchase_price' => 1200, 'sell_price' => 1800],
            ['sku' => 'PRD-010', 'name' => 'Oreo', 'category_id' => 3, 'brand_id' => 2, 'unit_id' => 1, 'purchase_price' => 8000, 'sell_price' => 10000],
            ['sku' => 'PRD-011', 'name' => 'Lifebuoy Sabun', 'category_id' => 6, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 4500, 'sell_price' => 6000],
            ['sku' => 'PRD-012', 'name' => 'Sunsilk Shampo', 'category_id' => 6, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 12000, 'sell_price' => 15000],
            ['sku' => 'PRD-013', 'name' => 'Pepsodent', 'category_id' => 6, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 8000, 'sell_price' => 10000],
            ['sku' => 'PRD-014', 'name' => 'Telur 1kg', 'category_id' => 1, 'brand_id' => null, 'unit_id' => 2, 'purchase_price' => 25000, 'sell_price' => 30000],
            ['sku' => 'PRD-015', 'name' => 'Beras 5kg', 'category_id' => 1, 'brand_id' => null, 'unit_id' => 2, 'purchase_price' => 50000, 'sell_price' => 60000],
            ['sku' => 'PRD-016', 'name' => 'Gula Pasir 1kg', 'category_id' => 1, 'brand_id' => null, 'unit_id' => 2, 'purchase_price' => 12000, 'sell_price' => 15000],
            ['sku' => 'PRD-017', 'name' => 'Minyak Goreng 1L', 'category_id' => 1, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 15000, 'sell_price' => 18000],
            ['sku' => 'PRD-018', 'name' => 'Kecap Bango', 'category_id' => 1, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 10000, 'sell_price' => 12000],
            ['sku' => 'PRD-019', 'name' => 'Saus ABC', 'category_id' => 1, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 8000, 'sell_price' => 10000],
            ['sku' => 'PRD-020', 'name' => 'Kopi Kapal Api', 'category_id' => 1, 'brand_id' => 4, 'unit_id' => 5, 'purchase_price' => 15000, 'sell_price' => 18000],
            ['sku' => 'PRD-021', 'name' => 'Sabun Cuci Surf', 'category_id' => 6, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 12000, 'sell_price' => 15000],
            ['sku' => 'PRD-022', 'name' => 'Sabun Cuci Daia', 'category_id' => 6, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 11000, 'sell_price' => 14000],
            ['sku' => 'PRD-023', 'name' => 'Pencuci Piring Sunlight', 'category_id' => 6, 'brand_id' => 5, 'unit_id' => 1, 'purchase_price' => 8000, 'sell_price' => 10000],
            ['sku' => 'PRD-024', 'name' => 'Sapu', 'category_id' => 5, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 15000, 'sell_price' => 20000],
            ['sku' => 'PRD-025', 'name' => 'Sapu Lidi', 'category_id' => 5, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 10000, 'sell_price' => 12000],
            ['sku' => 'PRD-026', 'name' => 'Pel', 'category_id' => 5, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 8000, 'sell_price' => 10000],
            ['sku' => 'PRD-027', 'name' => 'Kemoceng', 'category_id' => 5, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 7000],
            ['sku' => 'PRD-028', 'name' => 'Lampu Philips 5W', 'category_id' => 4, 'brand_id' => 6, 'unit_id' => 1, 'purchase_price' => 15000, 'sell_price' => 20000],
            ['sku' => 'PRD-029', 'name' => 'Kabel USB', 'category_id' => 4, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 10000, 'sell_price' => 15000],
            ['sku' => 'PRD-030', 'name' => 'Charger Handphone', 'category_id' => 4, 'brand_id' => 7, 'unit_id' => 1, 'purchase_price' => 25000, 'sell_price' => 30000],
            ['sku' => 'PRD-031', 'name' => 'Mouse Wireless', 'category_id' => 4, 'brand_id' => 7, 'unit_id' => 1, 'purchase_price' => 50000, 'sell_price' => 60000],
            ['sku' => 'PRD-032', 'name' => 'Keyboard USB', 'category_id' => 4, 'brand_id' => 7, 'unit_id' => 1, 'purchase_price' => 80000, 'sell_price' => 100000],
            ['sku' => 'PRD-033', 'name' => 'Baterai AA', 'category_id' => 4, 'brand_id' => 6, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 8000],
            ['sku' => 'PRD-034', 'name' => 'Baterai AAA', 'category_id' => 4, 'brand_id' => 6, 'unit_id' => 1, 'purchase_price' => 4000, 'sell_price' => 6000],
            ['sku' => 'PRD-035', 'name' => 'Kabel HDMI', 'category_id' => 4, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 25000, 'sell_price' => 30000],
            ['sku' => 'PRD-036', 'name' => 'Kertas A4', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 4, 'purchase_price' => 40000, 'sell_price' => 50000],
            ['sku' => 'PRD-037', 'name' => 'Bolpoin Standard', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 2000, 'sell_price' => 3000],
            ['sku' => 'PRD-038', 'name' => 'Pensil 2B', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 1500, 'sell_price' => 2500],
            ['sku' => 'PRD-039', 'name' => 'Penghapus', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 1000, 'sell_price' => 2000],
            ['sku' => 'PRD-040', 'name' => 'Penggaris 30cm', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 3000, 'sell_price' => 5000],
            ['sku' => 'PRD-041', 'name' => 'Stabilo', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 7000],
            ['sku' => 'PRD-042', 'name' => 'Isi Stabilo', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 3000, 'sell_price' => 5000],
            ['sku' => 'PRD-043', 'name' => 'Cutter', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 8000],
            ['sku' => 'PRD-044', 'name' => 'Gunting', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 10000, 'sell_price' => 15000],
            ['sku' => 'PRD-045', 'name' => 'Stapler', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 15000, 'sell_price' => 20000],
            ['sku' => 'PRD-046', 'name' => 'Isi Staples', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 8000],
            ['sku' => 'PRD-047', 'name' => 'Penjepit Kertas', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 2000, 'sell_price' => 4000],
            ['sku' => 'PRD-048', 'name' => 'Binder Clip', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 1000, 'sell_price' => 2000],
            ['sku' => 'PRD-049', 'name' => 'Amplop Coklat', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 500, 'sell_price' => 1000],
            ['sku' => 'PRD-050', 'name' => 'Plastik Mika', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 3000, 'sell_price' => 5000],
            ['sku' => 'PRD-051', 'name' => 'Pita Double Tape', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 3000, 'sell_price' => 5000],
            ['sku' => 'PRD-052', 'name' => 'Lem Fox', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 8000],
            ['sku' => 'PRD-053', 'name' => 'Pita Lakban', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 7000, 'sell_price' => 10000],
            ['sku' => 'PRD-054', 'name' => 'Sarung Tangan Karet', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 5000, 'sell_price' => 8000],
            ['sku' => 'PRD-055', 'name' => 'Masker Medis', 'category_id' => 7, 'brand_id' => null, 'unit_id' => 1, 'purchase_price' => 1000, 'sell_price' => 2000],
        ];

        $productIds = [];
        foreach ($products as $product) {
            $created = \App\Models\Product\Product::firstOrCreate([
                'business_id' => $demoBusiness->id,
                'sku' => $product['sku'],
            ], [
                'name' => $product['name'],
                'category_id' => $product['category_id'],
                'brand_id' => $product['brand_id'],
                'unit_id' => $product['unit_id'],
                'purchase_price' => $product['purchase_price'],
                'sell_price' => $product['sell_price'],
                'is_active' => true,
                'type' => 'single',
                'enable_stock' => true,
                'alert_quantity' => 10,
            ]);
            $productIds[] = $created->id;

            // Create variation & stock
            $util = new \App\Utils\ProductUtil();
            $variation = $util->createSingleProductVariation($created, $demoBusiness->id);

            // Create stock location
            \App\Models\Product\VariationLocationDetail::firstOrCreate([
                'variation_id' => $variation->id,
                'location_id' => $demoBusiness->locations()->first()->id,
            ], [
                'qty_available' => 100,
                'qty_reserved' => 0,
            ]);
        }

        // Create demo contacts (customers & suppliers)
        $customers = [
            ['name' => 'PT Konsumen Utama', 'type' => 'customer', 'email' => 'customer1@demo.com', 'mobile' => '0811111111'],
            ['name' => 'Toko Sinar Jaya', 'type' => 'customer', 'email' => 'customer2@demo.com', 'mobile' => '0822222222'],
            ['name' => 'CV Pelanggan Setia', 'type' => 'customer', 'email' => 'customer3@demo.com', 'mobile' => '0833333333'],
            ['name' => 'PT Supplier Global', 'type' => 'supplier', 'email' => 'supplier1@demo.com', 'mobile' => '0844444444'],
            ['name' => 'UD Pemasok Lokal', 'type' => 'supplier', 'email' => 'supplier2@demo.com', 'mobile' => '0855555555'],
        ];

        foreach ($customers as $contact) {
            \App\Models\Contact\Contact::firstOrCreate([
                'business_id' => $demoBusiness->id,
                'name' => $contact['name'],
            ], [
                'type' => $contact['type'],
                'email' => $contact['email'],
                'mobile' => $contact['mobile'],
            ]);
        }

        $this->command->info('KasirPro seeder completed successfully!');
        $this->command->info('Admin: admin@kasirpro.com / 12345678');
        $this->command->info('Kasir: kasir@kasirpro.com / 12345678');
        $this->command->info('Added ' . count($products) . ' products, ' . count($customers) . ' contacts.');
    }
}
