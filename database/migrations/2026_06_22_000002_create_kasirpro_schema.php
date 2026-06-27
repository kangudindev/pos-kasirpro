<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Currencies
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 10);
            $table->string('code', 3);
            $table->string('thousand_separator', 3)->nullable();
            $table->string('decimal_separator', 3)->nullable();
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Businesses
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('tax_label', 10)->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->tinyInteger('fy_start_month')->default(1);
            $table->enum('accounting_method', ['fifo', 'lifo', 'avco'])->default('fifo');
            $table->decimal('default_profit_percent', 5, 2)->default(0);
            $table->enum('sell_price_tax', ['includes', 'excludes'])->default('includes');
            $table->string('sku_prefix', 10)->nullable();
            $table->json('pos_settings')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Business Locations
        Schema::create('business_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name', 256);
            $table->text('landmark')->nullable();
            $table->string('country', 100);
            $table->string('state', 100);
            $table->string('city', 100);
            $table->char('zip_code', 7);
            $table->string('mobile')->nullable();
            $table->string('alternate_number')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('invoice_layout_id')->nullable();
            $table->unsignedInteger('default_selling_price_group_id')->nullable();
            $table->string('printer_type')->nullable();
            $table->text('feature_products')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Roles & Permissions (Spatie)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        // User Location Access
        Schema::create('user_location_access', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('business_location_id');
            $table->primary(['user_id', 'business_location_id']);
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('short_code', 50)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Units
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('actual_name');
            $table->string('short_name', 50);
            $table->boolean('allow_decimal')->default(false);
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->decimal('multiplier', 10, 3)->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Warranties
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->enum('duration_type', ['days', 'months', 'years']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Tax Rates
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4);
            $table->boolean('is_tax_group')->default(false);
            $table->boolean('for_tax_group')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('group_sub_taxes', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_rate_id');
            $table->unsignedBigInteger('sub_tax_id');
            $table->primary(['tax_rate_id', 'sub_tax_id']);
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('sku')->nullable();
            $table->enum('barcode_type', ['C39', 'C128', 'EAN13', 'EAN8', 'UPCA', 'UPCE', 'ITF14'])->default('C128');
            $table->string('item_code', 100)->nullable();
            $table->enum('type', ['single', 'variable', 'combo', 'modifier'])->default('single');
            $table->enum('selling_type', ['transactional', 'solution'])->default('transactional');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->text('sub_unit_ids')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->enum('tax_type', ['exclusive', 'inclusive'])->default('exclusive');
            $table->boolean('enable_stock')->default(true);
            $table->boolean('not_for_selling')->default(false);
            $table->decimal('alert_quantity', 22, 4)->default(0);
            $table->string('image')->nullable();
            $table->text('product_description')->nullable();
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inactive')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('custom_field_1')->nullable();
            $table->string('custom_field_2')->nullable();
            $table->string('custom_field_3')->nullable();
            $table->string('custom_field_4')->nullable();
            $table->string('custom_field_5')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Product Variations
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->boolean('is_dummy')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->string('sub_sku')->nullable();
            $table->unsignedBigInteger('product_variation_id');
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 5, 2)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variation_id');
            $table->unsignedBigInteger('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
            $table->unique(['variation_id', 'location_id']);
        });

        Schema::create('variation_group_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variation_id');
            $table->unsignedInteger('selling_price_group_id');
            $table->decimal('price', 22, 4);
            $table->timestamps();
            $table->unique(['variation_id', 'selling_price_group_id'], 'var_grp_price_uniq');
        });

        Schema::create('product_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id');
            $table->primary(['product_id', 'location_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Contacts
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->decimal('amount', 5, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->enum('type', ['customer', 'supplier', 'both', 'lead']);
            $table->string('supplier_business_name')->nullable();
            $table->string('name');
            $table->string('prefix', 20)->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('mobile');
            $table->string('landline', 20)->nullable();
            $table->string('alternate_number')->nullable();
            $table->integer('pay_term_number')->nullable();
            $table->enum('pay_term_type', ['days', 'months'])->nullable();
            $table->decimal('credit_limit', 22, 4)->nullable();
            $table->decimal('balance', 22, 4)->default(0);
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->enum('contact_status', ['active', 'inactive'])->default('active');
            $table->string('contact_id', 50)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('custom_field_1')->nullable();
            $table->string('custom_field_2')->nullable();
            $table->string('custom_field_3')->nullable();
            $table->string('custom_field_4')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->enum('type', ['purchase', 'sell', 'expense', 'stock_adjustment']);
            $table->string('sub_type', 50)->nullable();
            $table->enum('status', ['received', 'pending', 'ordered', 'draft', 'final'])->nullable();
            $table->enum('payment_status', ['paid', 'due'])->nullable();
            $table->enum('adjustment_type', ['normal', 'abnormal'])->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedInteger('customer_group_id')->nullable();
            $table->string('invoice_no', 100)->nullable();
            $table->string('ref_no', 100)->nullable();
            $table->string('source', 50)->nullable();
            $table->dateTime('transaction_date');
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->string('shipping_details')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_status')->nullable();
            $table->string('delivered_to')->nullable();
            $table->decimal('shipping_charges', 22, 4)->default(0);
            $table->text('additional_notes')->nullable();
            $table->text('staff_note')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->decimal('round_off_amount', 22, 4)->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedInteger('commission_agent')->nullable();
            $table->unsignedInteger('selling_price_group_id')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->integer('recur_interval')->nullable();
            $table->string('recur_interval_type', 20)->nullable();
            $table->date('recur_start_date')->nullable();
            $table->date('recur_end_date')->nullable();
            $table->string('invoice_token', 100)->nullable();
            $table->string('custom_field_1')->nullable();
            $table->string('custom_field_2')->nullable();
            $table->string('custom_field_3')->nullable();
            $table->string('custom_field_4')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Transaction Lines
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->nullable();
            $table->decimal('unit_price_inc_tax', 22, 4)->nullable();
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->string('discount')->nullable();
            $table->decimal('unit_price_before_discount', 22, 4)->nullable();
            $table->text('sell_line_note')->nullable();
            $table->unsignedBigInteger('parent_sell_line_id')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4);
            $table->decimal('quantity_received', 22, 4)->default(0);
            $table->decimal('quantity_sold', 22, 4)->default(0);
            $table->decimal('quantity_adjusted', 22, 4)->default(0);
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // Payments
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->enum('method', ['cash', 'card', 'cheque', 'bank_transfer', 'other']);
            $table->string('payment_type')->nullable();
            $table->string('card_transaction_number')->nullable();
            $table->string('card_number')->nullable();
            $table->enum('card_type', ['visa', 'master'])->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_month', 10)->nullable();
            $table->string('card_year', 10)->nullable();
            $table->char('card_security', 5)->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('payment_link')->nullable();
            $table->dateTime('payment_link_expiry')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // FIFO/LIFO Mapping
        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sell_line_id');
            $table->unsignedBigInteger('purchase_line_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->timestamps();
        });

        // Stock Management
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('ref_no', 100);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft');
            $table->enum('adjustment_type', ['normal', 'abnormal'])->default('normal');
            $table->text('additional_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4);
            $table->decimal('unit_price', 22, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('ref_no', 100);
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->enum('status', ['draft', 'pending', 'in_transit', 'completed', 'cancelled'])->default('draft');
            $table->decimal('shipping_charges', 22, 4)->default(0);
            $table->text('additional_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4);
            $table->timestamps();
        });

        // Pricing
        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('price_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('priority')->nullable();
            $table->string('discount_type')->nullable();
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('applicable_in_spg')->default(false);
            $table->boolean('applicable_in_cg')->default(false);
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('code', 50);
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 22, 4);
            $table->decimal('minimum_amount', 22, 4)->default(0);
            $table->decimal('maximum_discount', 22, 4)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Accounting
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->text('account_details')->nullable();
            $table->unsignedInteger('account_type_id')->nullable();
            $table->decimal('opening_balance', 22, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 22, 4);
            $table->decimal('balance', 22, 4)->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->enum('type', ['debit', 'credit'])->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('opening_amount', 22, 4)->default(0);
            $table->decimal('closing_amount', 22, 4)->nullable();
            $table->decimal('card_amount', 22, 4)->nullable();
            $table->text('cheques')->nullable();
            $table->json('denominations')->nullable();
            $table->enum('status', ['open', 'close']);
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_register_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->timestamps();
        });

        // Invoicing
        Schema::create('invoice_schemes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->enum('scheme_type', ['auto', 'blank', 'manual'])->default('auto');
            $table->string('prefix', 50)->nullable();
            $table->integer('counter_length')->default(1);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->boolean('qr_code')->default(false);
            $table->json('common_settings')->nullable();
            $table->text('letter_head')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reference_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('ref_type', 50);
            $table->integer('ref_count')->default(0);
            $table->timestamps();
            $table->unique(['business_id', 'ref_type']);
        });

        // Supporting Tables
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('transaction_date');
            $table->decimal('amount', 22, 4);
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->string('document')->nullable();
            $table->text('additional_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Membership
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('contact_id');
            $table->string('membership_no', 50);
            $table->integer('points_balance')->default(0);
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'membership_no']);
        });

        Schema::create('reward_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('contact_id');
            $table->integer('points');
            $table->enum('type', ['earn', 'redeem']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        // Scale Integration
        Schema::create('scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('model')->nullable();
            $table->enum('connection_type', ['serial', 'tcpip', 'usb', 'bluetooth']);
            $table->string('port', 20)->nullable();
            $table->integer('baud_rate')->default(9600);
            $table->tinyInteger('data_bits')->default(8);
            $table->tinyInteger('stop_bits')->default(1);
            $table->enum('parity', ['none', 'even', 'odd'])->default('none');
            $table->string('ip_address')->nullable();
            $table->integer('port_number')->default(5000);
            $table->string('protocol')->nullable();
            $table->boolean('is_label_printer')->default(false);
            $table->integer('label_width')->default(40);
            $table->integer('label_height')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('scale_plu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('plu_code', 20);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->tinyInteger('department')->default(1);
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('tare_weight', 10, 4)->default(0);
            $table->enum('unit', ['kg', 'g', 'pcs'])->default('kg');
            $table->boolean('is_active')->default(true);
            $table->string('label_name')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'plu_code']);
        });

        Schema::create('scale_plu_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->tinyInteger('group_code');
            $table->string('name', 100);
            $table->timestamps();
            $table->unique(['business_id', 'group_code']);
        });

        Schema::create('scale_label_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->integer('width_mm')->default(40);
            $table->integer('height_mm')->default(30);
            $table->json('layout')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Barcode Center (Superadmin)
        Schema::create('barcode_center_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('barcode_center_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('barcode_center', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->enum('barcode_type', ['EAN13', 'EAN8', 'UPCA', 'UPCE', 'CODE128', 'CODE39']);
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('origin_country', 100)->nullable();
            $table->string('manufacturer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('barcode_center_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('barcode_center_id');
            $table->decimal('custom_price', 22, 4)->nullable();
            $table->integer('initial_stock')->default(0);
            $table->timestamp('imported_at')->useCurrent();
        });

        Schema::create('tenant_custom_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('barcode')->nullable();
            $table->string('category')->nullable();
            $table->decimal('cost_price', 22, 4)->nullable();
            $table->decimal('selling_price', 22, 4)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('image')->nullable();
            $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('pending_review');
            $table->timestamps();
        });

        Schema::create('barcode_center_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('tenant_custom_product_id');
            $table->enum('notification_type', ['new_product', 'product_claim', 'data_correction']);
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Marketplace
        Schema::create('ecommerce_channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->enum('platform', ['shopee', 'tokopedia', 'lazada', 'woocommerce']);
            $table->string('name');
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('shop_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('channel_id');
            $table->string('channel_product_id')->nullable();
            $table->string('channel_sku')->nullable();
            $table->decimal('channel_price', 22, 4)->nullable();
            $table->integer('channel_stock')->default(0);
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->dateTime('last_sync_at')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'channel_id']);
        });

        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('channel_id');
            $table->string('channel_order_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('status')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('total_amount', 22, 4)->nullable();
            $table->decimal('shipping_cost', 22, 4)->nullable();
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['channel_id', 'channel_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_products');
        Schema::dropIfExists('ecommerce_channels');
        Schema::dropIfExists('barcode_center_notifications');
        Schema::dropIfExists('tenant_custom_products');
        Schema::dropIfExists('barcode_center_imports');
        Schema::dropIfExists('barcode_center');
        Schema::dropIfExists('barcode_center_brands');
        Schema::dropIfExists('barcode_center_categories');
        Schema::dropIfExists('scale_label_templates');
        Schema::dropIfExists('scale_plu_groups');
        Schema::dropIfExists('scale_plu');
        Schema::dropIfExists('scales');
        Schema::dropIfExists('reward_points');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('reference_counters');
        Schema::dropIfExists('invoice_layouts');
        Schema::dropIfExists('invoice_schemes');
        Schema::dropIfExists('cash_register_transactions');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_types');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('selling_price_groups');
        Schema::dropIfExists('stock_transfer_lines');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_lines');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::dropIfExists('transaction_payments');
        Schema::dropIfExists('purchase_lines');
        Schema::dropIfExists('transaction_sell_lines');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_locations');
        Schema::dropIfExists('variation_group_prices');
        Schema::dropIfExists('variation_location_details');
        Schema::dropIfExists('variations');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('group_sub_taxes');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('user_location_access');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('business_locations');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('currencies');
    }
};
