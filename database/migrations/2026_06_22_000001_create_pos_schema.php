<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. USERS & AUTH
        // ============================================================

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // admin, staff, manager
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 2. STORES & WAREHOUSES
        // ============================================================

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // store owner/manager
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 3. PRODUCT MASTER DATA
        // ============================================================

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete(); // hierarchical
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Kilogram, Piece, etc.
            $table->string('short_name'); // kg, pc
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "3 Months", "1 Year"
            $table->text('description')->nullable();
            $table->integer('duration_months'); // warranty duration in months
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 4. PRODUCTS
        // ============================================================

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode_symbology')->nullable(); // Code34, Code35, etc.
            $table->string('item_code')->nullable();
            $table->text('description')->nullable();

            // Relations
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warranty_id')->nullable()->constrained()->nullOnDelete();

            // Product type: single or variable
            $table->enum('type', ['single', 'variable'])->default('single');
            $table->enum('selling_type', ['transactional', 'solution'])->default('transactional');

            // Pricing & stock (for single product)
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('tax_type', ['exclusive', 'inclusive'])->default('exclusive');
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'cash'])->default('percentage');
            $table->integer('quantity')->default(0);
            $table->integer('quantity_alert')->default(0); // low stock alert threshold

            // Custom fields
            $table->date('manufactured_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('has_warranty')->default(false);
            $table->boolean('has_manufacturer')->default(false);
            $table->boolean('has_expiry')->default(false);

            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // ============================================================
        // 5. PRODUCT VARIANTS
        // ============================================================

        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Color, Size, Material, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // Red, Black, XL, etc.
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_value_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 6. PRODUCT STOCK (per warehouse)
        // ============================================================

        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id', 'warehouse_id']);
        });

        // ============================================================
        // 7. CUSTOMERS & SUPPLIERS
        // ============================================================

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // e.g. 201
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // e.g. 201
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 8. TAX RATES
        // ============================================================

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // VAT, GST 5%, etc.
            $table->decimal('rate', 5, 2); // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 9. PAYMENT METHODS
        // ============================================================

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Cash, Card, COD, Cheque, Paypal, Bank Transfer, Debit Card, Scan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 10. SALES (POS TRANSACTIONS)
        // ============================================================

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique(); // SL0101, SL0102, etc.
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // null = Walk-in Customer
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // cashier/biller
            $table->date('date');
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');

            // Totals
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Payment
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid');
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            // Coupon
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('coupon_discount', 15, 2)->default(0);

            // POS specific
            $table->enum('pos_status', ['pos', 'hold', 'void'])->default('pos');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('date');
            $table->index('customer_id');
            $table->index('status');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('name'); // snapshot of product name
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // ============================================================
        // 11. SALES RETURNS
        // ============================================================

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('sale_id')->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['completed', 'pending'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // ============================================================
        // 12. PURCHASES
        // ============================================================

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique(); // PT001, PT002, etc.
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->enum('status', ['received', 'ordered', 'pending'])->default('pending');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('supplier_id');
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // ============================================================
        // 13. PURCHASE RETURNS
        // ============================================================

        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('purchase_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['completed', 'pending'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // ============================================================
        // 14. PURCHASE ORDERS
        // ============================================================

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->date('expected_date')->nullable();
            $table->enum('status', ['draft', 'ordered', 'received', 'cancelled'])->default('draft');
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // ============================================================
        // 15. STOCK TRANSFER
        // ============================================================

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // responsible person
            $table->date('date');
            $table->enum('status', ['draft', 'in_transit', 'received', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

        // ============================================================
        // 16. STOCK ADJUSTMENT
        // ============================================================

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('warehouse_id')->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->enum('adjustment_type', ['addition', 'subtraction'])->default('addition');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

        // ============================================================
        // 17. EXPENSES
        // ============================================================

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Employee Benefits, Foods & Snacks, etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->nullOnDelete();
            $table->string('reference_no')->nullable();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('date');
        });

        // ============================================================
        // 18. COUPONS
        // ============================================================

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // coupon code
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 15, 2); // discount value
            $table->decimal('minimum_amount', 15, 2)->default(0);
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 19. POS SETTINGS
        // ============================================================

        Schema::create('pos_settings', function (Blueprint $table) {
            $table->id();
            $table->string('printer_type')->default('A4');
            $table->boolean('enable_sound_effect')->default(true);
            $table->timestamps();
        });

        Schema::create('pos_settings_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_settings_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // ============================================================
        // 20. INVOICE SETTINGS
        // ============================================================

        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('prefix')->default('INV -');
            $table->integer('due_days')->default(5);
            $table->text('footer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop in reverse order of creation
        Schema::dropIfExists('invoice_settings');
        Schema::dropIfExists('pos_settings_payment_methods');
        Schema::dropIfExists('pos_settings');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('variant_values');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('stores');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'phone', 'avatar', 'is_active', 'created_at', 'updated_at']);
        });
        Schema::dropIfExists('roles');
    }
};
