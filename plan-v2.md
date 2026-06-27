# POS KasirPro - Rencana Pengembangan v2.1

**Versi:** 2.1
**Tanggal:** Juni 2026
**Target:** Supermarket, Toko Kelontong, Online Shop
**Basis:** DreamsPOS v2.0.6 (template) + UltimatePOS v6.8 (reference logic)

---

## 1. TECH STACK FINAL

| Layer | Technology | Keterangan |
|-------|------------|------------|
| **Backend** | Laravel 11, PHP 8.3 | Framework utama |
| **Frontend** | Blade + Bootstrap 5 + jQuery | Tetap DreamsPOS template |
| **Database** | MySQL 8.0 | Primary database |
| **Cache/Queue** | Redis | Session, queue, caching |
| **Mobile** | Flutter | Cross-platform (Android + iOS) |
| **API** | Laravel Sanctum | Token-based auth untuk Flutter |
| **Payment** | Midtrans | Gateway utama (QRIS, GoPay, OVO, Dana, Card, Bank Transfer) |
| **Deployment** | Docker + FrankenPHP + Laravel Octane | High performance |
| **Charts** | ApexCharts | Dashboard |
| **PDF** | DomPDF | Invoice & laporan |
| **Scale Bridge** | Node.js + WebSocket | Background service untuk RS232/LAN timbangan |
| **Label Printer** | ESC/POS / TSPL / ZPL | Support semua merek label printer |

### Kenapa FrankenPHP + Laravel Octane?
- **FrankenPHP**: Web server modern berbasis Go/Caddy, support Worker mode
- **Laravel Octane**: Keep app di memory, tidak perlu bootstrap setiap request
- **Hasil**: 10x lebih cepat dari PHP-FPM tradisional
- **Cocok untuk**: POS yang butuh response cepat saat scan barcode

### Kenapa Flutter untuk Mobile?
- Cross-platform (Android + iOS) dalam 1 codebase
- UI yang smooth untuk POS
- Bisa akses kamera untuk barcode scan
- Offline support dengan SQLite

---

## 2. PERUBAHAN DARI v1

### Yang Diubah Berdasarkan Analisa UltimatePOS

| Aspek | v1 | v2 | Alasan |
|-------|----|----|--------|
| **Transaction Model** | Tabel terpisah (sales, purchases) | **Single `transactions` table** + type enum | UltimatePOS bewariskan 7+ tahun, pattern ini proven |
| **Stock Tracking** | `product_stocks` dedicated | **`variation_location_details`** (qty_per_location) | Lebih ringan, sudah proven di UltimatePOS |
| **FIFO/LIFO** | Tidak ada | **`transaction_sell_lines_purchase_lines`** pivot | Stock accounting yang benar |
| **Payment System** | `paid_amount` di header | **`transaction_payments` dedicated table** | Support multi-payment per transaksi |
| **Utils Pattern** | Service class | **Utility class pattern** (seperti UltimatePOS) | Lebih mudah di-maintain |
| **Contact Model** | 2 tabel (customers, suppliers) | **1 tabel `contacts` + type field** | Lebih fleksibel |
| **Receipt Templates** | 1 template | **11 template designs** | Reuse dari UltimatePOS |
| **Permissions** | Basic roles | **54 granular permissions** (Spatie) | Reuse dari UltimatePOS |
| **Currencies** | Manual | **141 currencies seeded** | Reuse dari UltimatePOS |

### Yang DITAMBAH dari v1
- `variation_location_details` - Stock per location
- `transaction_sell_lines_purchase_lines` - FIFO/LIFO mapping
- `sell_line_warranties` - Warranty per sell line
- `selling_price_groups` + `variation_group_prices` - Tiered pricing
- `customer_groups` - Customer grouping
- `discounts` + `discount_variations` - Global discounts
- `cash_registers` + `cash_register_transactions` - Cash register
- `accounts` + `account_transactions` - Double-entry accounting
- `invoice_schemes` + `invoice_layouts` - Flexible invoicing
- `printers` - Receipt printer configs
- `barcodes` - Barcode label templates
- `activity_log` - Audit trail
- `scales` + `scale_plu` - Timbangan & label printing
- `barcode_center` - Master product database (Superadmin)

---

## 3. REUSABLE CODE DARI UltimatePOS

### 3.1 Utility Classes (12 files, ~12,000+ lines)

| File | Lines | Fungsi | Reuse Strategy |
|------|-------|--------|----------------|
| `Util.php` | 1,886 | Base utility, number formatting, payment types, reference generation | **COPY** - ganti namespace |
| `TransactionUtil.php` | 6,523 | Sell/purchase/expense CRUD, payment processing, invoice calculation | **COPY** - adaptasi untuk single-tenant |
| `ProductUtil.php` | 2,400 | Product CRUD, variation management, stock operations, invoice calculation | **COPY** - adaptasi |
| `ContactUtil.php` | 264 | Contact CRUD, walk-in customer, balance calculation | **COPY** - adaptasi |
| `BusinessUtil.php` | 442 | Business setup, default resources, currency/timezone data | **COPY** - adaptasi |
| `CashRegisterUtil.php` | 415 | Register open/close, payment recording, refund | **COPY** |
| `TaxUtil.php` | 25 | Tax calculation helpers | **COPY** |
| `AccountTransactionUtil.php` | 25 | Account transaction helpers | **COPY** |
| `ModuleUtil.php` | 550 | Module management, subscription | **ADAPT** - simplify |
| `NotificationUtil.php` | 383 | Auto notifications, template tags | **COPY** |

### 3.2 Key Methods to Reuse

#### TransactionUtil
```php
createSellTransaction($request, $business_id)
updateSellTransaction($request, $business_id, $id)
createOrUpdateSellLines($request, $business_id, $transaction_id)
createOrUpdatePaymentLines($request, $business_id, $transaction_id)
updatePaymentStatus($transaction_id)
calculatePaymentStatus($transaction_id)
adjustQuantity($variation_id, $location_id, $quantity_adjustment)
mapPurchaseSell($transaction_id, $transaction_type)
updateContactBalance($contact_id, $amount, $type)
getReceiptDetails($transaction_id, $layout_id)
getInvoiceNumber($business_id, $location_id, $scheme_id)
```

#### ProductUtil
```php
createSingleProductVariation($product, $business_id)
createVariableProductVariations($request, $product, $business_id)
updateProductQuantity($variation_id, $location_id, $quantity)
decreaseProductQuantity($variation_id, $location_id, $quantity)
getCurrentStock($variation_id, $location_id)
calculateInvoiceTotal($products, $business_id, $location_id)
getProductDiscount($product, $variation, $contact_group_id, $price_group_id)
```

### 3.3 Seeder Data (Langsung Copy)

| Seeder | Records | Keterangan |
|--------|---------|------------|
| `CurrenciesTableSeeder` | 141 currencies | Mata uang dunia |
| `PermissionsTableSeeder` | 54 permissions | Granular permissions |
| `BarcodesTableSeeder` | 6 templates | Label barcode templates |
| `DummyBusinessSeeder` | 500+ records | Demo data lengkap |

### 3.4 Receipt Templates (11 designs)

| Template | Keterangan |
|----------|------------|
| `classic.blade.php` | Full-featured receipt |
| `slim.blade.php` | Thermal printer ticket |
| `slim2.blade.php` | Alternative slim |
| `elegant.blade.php` | Elegant design |
| `elegant_modified.blade.php` | Modified elegant |
| `detailed.blade.php` | Detailed receipt |
| `columnize-taxes.blade.php` | Tax column layout |
| `delivery_note.blade.php` | Delivery note |
| `packing_slip.blade.php` | Packing slip |
| `english-arabic.blade.php` | Bilingual |

---

## 4. DATABASE SCHEMA (Final)

### 4.1 Core Business Tables

```sql
-- Businesses (Multi-tenant)
CREATE TABLE businesses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    logo VARCHAR(255) NULL,
    tax_number VARCHAR(100) NULL,
    tax_label VARCHAR(10) NULL,
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    fy_start_month TINYINT DEFAULT 1,
    accounting_method ENUM('fifo','lifo','avco') DEFAULT 'fifo',
    default_profit_percent DECIMAL(5,2) DEFAULT 0,
    sell_price_tax ENUM('includes','excludes') DEFAULT 'includes',
    sku_prefix VARCHAR(10) NULL,
    pos_settings JSON NULL,
    enabled_modules JSON NULL,
    settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Business Locations
CREATE TABLE business_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    landmark TEXT NULL,
    country VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code CHAR(7) NOT NULL,
    mobile VARCHAR(20) NULL,
    alternate_number VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    invoice_layout_id INT NULL,
    default_selling_price_group_id INT NULL,
    printer_type VARCHAR(20) NULL,
    feature_products TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Currencies (141 seeded)
CREATE TABLE currencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    code VARCHAR(3) NOT NULL,
    thousand_separator VARCHAR(3) NULL,
    decimal_separator VARCHAR(3) NULL,
    exchange_rate DECIMAL(10,4) DEFAULT 1,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4.2 Users & Auth

```sql
-- Users (enhanced)
ALTER TABLE users ADD COLUMN business_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN username VARCHAR(255) UNIQUE NULL;
ALTER TABLE users ADD COLUMN language CHAR(7) DEFAULT 'en';
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN user_type ENUM('user','sales_commission_agent') DEFAULT 'user';
ALTER TABLE users ADD COLUMN max_sale_discount DECIMAL(5,2) NULL;
ALTER TABLE users ADD COLUMN is_commission_agent BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN commission_percent DECIMAL(4,2) DEFAULT 0;
ALTER TABLE users ADD COLUMN created_by BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN dob DATE NULL;
ALTER TABLE users ADD COLUMN gender VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN bank_details TEXT NULL;
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;

-- Spatie Permissions (54 permissions seeded)
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);

CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);

CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id)
);

-- User-Location Access
CREATE TABLE user_contact_access (
    user_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, contact_id)
);
```

### 4.3 Product Master

```sql
-- Categories (hierarchical)
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_code VARCHAR(50) NULL,
    parent_id BIGINT UNSIGNED NULL,
    slug VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Brands
CREATE TABLE brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    slug VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Units (with sub-units)
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    actual_name VARCHAR(255) NOT NULL,
    short_name VARCHAR(50) NOT NULL,
    allow_decimal BOOLEAN DEFAULT FALSE,
    base_unit_id BIGINT UNSIGNED NULL,
    multiplier DECIMAL(10,3) DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Warranties
CREATE TABLE warranties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    duration INT NOT NULL,
    duration_type ENUM('days','months','years') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Tax Rates (with groups)
CREATE TABLE tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(22,4) NOT NULL,
    is_tax_group BOOLEAN DEFAULT FALSE,
    for_tax_group BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE group_sub_taxes (
    tax_rate_id BIGINT UNSIGNED NOT NULL,
    sub_tax_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (tax_rate_id, sub_tax_id)
);
```

### 4.4 Products & Variations

```sql
-- Products
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL,
    sku VARCHAR(255) NOT NULL,
    barcode_type ENUM('C39','C128','EAN13','EAN8','UPCA','UPCE','ITF14') DEFAULT 'C128',
    item_code VARCHAR(100) NULL,
    type ENUM('single','variable','combo','modifier') NOT NULL,
    selling_type ENUM('transactional','solution') DEFAULT 'transactional',
    unit_id BIGINT UNSIGNED NOT NULL,
    sub_unit_ids TEXT NULL,
    brand_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    sub_category_id BIGINT UNSIGNED NULL,
    tax_id BIGINT UNSIGNED NULL,
    tax_type ENUM('exclusive','inclusive') DEFAULT 'exclusive',
    enable_stock BOOLEAN DEFAULT TRUE,
    not_for_selling BOOLEAN DEFAULT FALSE,
    alert_quantity DECIMAL(22,4) DEFAULT 0,
    image VARCHAR(255) NULL,
    product_description TEXT NULL,
    warranty_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_inactive BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    custom_field_1 VARCHAR(255) NULL,
    custom_field_2 VARCHAR(255) NULL,
    custom_field_3 VARCHAR(255) NULL,
    custom_field_4 VARCHAR(255) NULL,
    custom_field_5 VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Product Variations (groups)
CREATE TABLE product_variations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_dummy BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Variations (sellable units)
CREATE TABLE variations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    sub_sku VARCHAR(255) NULL,
    product_variation_id BIGINT UNSIGNED NOT NULL,
    default_purchase_price DECIMAL(22,4) DEFAULT 0,
    dpp_inc_tax DECIMAL(22,4) DEFAULT 0,
    profit_percent DECIMAL(5,2) DEFAULT 0,
    default_sell_price DECIMAL(22,4) DEFAULT 0,
    sell_price_inc_tax DECIMAL(22,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Variation Location Details (stock per location)
CREATE TABLE variation_location_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variation_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    qty_available DECIMAL(22,4) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_var_loc (variation_id, location_id)
);

-- Variation Group Prices (tiered pricing)
CREATE TABLE variation_group_prices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variation_id BIGINT UNSIGNED NOT NULL,
    selling_price_group_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(22,4) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_var_price (variation_id, selling_price_group_id)
);

-- Product Racks
CREATE TABLE product_racks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    rack VARCHAR(100) NULL,
    shelf VARCHAR(100) NULL,
    position VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Product Locations (pivot)
CREATE TABLE product_locations (
    product_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, location_id)
);
```

### 4.5 Contacts (Unified)

```sql
CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    type ENUM('customer','supplier','both','lead') NOT NULL,
    supplier_business_name VARCHAR(255) NULL,
    name VARCHAR(255) NOT NULL,
    prefix VARCHAR(20) NULL,
    first_name VARCHAR(255) NULL,
    middle_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    tax_number VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    address TEXT NULL,
    shipping_address TEXT NULL,
    mobile VARCHAR(20) NOT NULL,
    landline VARCHAR(20) NULL,
    alternate_number VARCHAR(20) NULL,
    pay_term_number INT NULL,
    pay_term_type ENUM('days','months') NULL,
    credit_limit DECIMAL(22,4) NULL,
    balance DECIMAL(22,4) DEFAULT 0,
    customer_group_id INT NULL,
    contact_status ENUM('active','inactive') DEFAULT 'active',
    contact_id VARCHAR(50) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    custom_field_1 VARCHAR(255) NULL,
    custom_field_2 VARCHAR(255) NULL,
    custom_field_3 VARCHAR(255) NULL,
    custom_field_4 VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Customer Groups
CREATE TABLE customer_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(5,2) DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4.6 Transactions (Single Table Pattern)

```sql
-- Central Transactions Table
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    type ENUM('purchase','sell','expense','stock_adjustment') NOT NULL,
    sub_type VARCHAR(50) NULL,
    status ENUM('received','pending','ordered','draft','final') NULL,
    payment_status ENUM('paid','due') NULL,
    adjustment_type ENUM('normal','abnormal') NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    customer_group_id INT NULL,
    invoice_no VARCHAR(100) NULL,
    ref_no VARCHAR(100) NULL,
    source VARCHAR(50) NULL,
    transaction_date DATETIME NOT NULL,
    total_before_tax DECIMAL(22,4) DEFAULT 0,
    tax_id BIGINT UNSIGNED NULL,
    tax_amount DECIMAL(22,4) DEFAULT 0,
    discount_type ENUM('fixed','percentage') NULL,
    discount_amount DECIMAL(22,4) DEFAULT 0,
    shipping_details VARCHAR(255) NULL,
    shipping_address TEXT NULL,
    shipping_status VARCHAR(50) NULL,
    delivered_to VARCHAR(255) NULL,
    shipping_charges DECIMAL(22,4) DEFAULT 0,
    additional_notes TEXT NULL,
    staff_note TEXT NULL,
    final_total DECIMAL(22,4) DEFAULT 0,
    round_off_amount DECIMAL(22,4) NULL,
    exchange_rate DECIMAL(10,4) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NULL,
    commission_agent INT NULL,
    selling_price_group_id INT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    recur_interval INT NULL,
    recur_interval_type VARCHAR(20) NULL,
    recur_start_date DATE NULL,
    recur_end_date DATE NULL,
    invoice_token VARCHAR(100) NULL,
    custom_field_1 VARCHAR(255) NULL,
    custom_field_2 VARCHAR(255) NULL,
    custom_field_3 VARCHAR(255) NULL,
    custom_field_4 VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Sell Line Items
CREATE TABLE transaction_sell_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) DEFAULT 0,
    unit_price DECIMAL(22,4) NULL,
    unit_price_inc_tax DECIMAL(22,4) NULL,
    item_tax DECIMAL(22,4) DEFAULT 0,
    tax_id BIGINT UNSIGNED NULL,
    discount VARCHAR(50) NULL,
    unit_price_before_discount DECIMAL(22,4) NULL,
    sell_line_note TEXT NULL,
    parent_sell_line_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Purchase Line Items
CREATE TABLE purchase_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    quantity_received DECIMAL(22,4) DEFAULT 0,
    quantity_sold DECIMAL(22,4) DEFAULT 0,
    quantity_adjusted DECIMAL(22,4) DEFAULT 0,
    quantity_returned DECIMAL(22,4) DEFAULT 0,
    purchase_price DECIMAL(22,4) NOT NULL,
    purchase_price_inc_tax DECIMAL(22,4) DEFAULT 0,
    item_tax DECIMAL(22,4) DEFAULT 0,
    tax_id BIGINT UNSIGNED NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Payments
CREATE TABLE transaction_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(22,4) DEFAULT 0,
    method ENUM('cash','card','cheque','bank_transfer','other') NOT NULL,
    payment_type VARCHAR(50) NULL,
    card_transaction_number VARCHAR(100) NULL,
    card_number VARCHAR(50) NULL,
    card_type ENUM('visa','master') NULL,
    card_holder_name VARCHAR(255) NULL,
    card_month VARCHAR(10) NULL,
    card_year VARCHAR(10) NULL,
    card_security CHAR(5) NULL,
    cheque_number VARCHAR(100) NULL,
    bank_account_number VARCHAR(100) NULL,
    bank_account_name VARCHAR(255) NULL,
    payment_link VARCHAR(255) NULL,
    payment_link_expiry DATETIME NULL,
    note TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- FIFO/LIFO Mapping
CREATE TABLE transaction_sell_lines_purchase_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sell_line_id BIGINT UNSIGNED NOT NULL,
    purchase_line_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4.7 Stock Management

```sql
-- Stock Adjustments
CREATE TABLE stock_adjustments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    ref_no VARCHAR(100) NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft','approved','cancelled') DEFAULT 'draft',
    adjustment_type ENUM('normal','abnormal') DEFAULT 'normal',
    additional_notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE stock_adjustment_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    unit_price DECIMAL(22,4) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Stock Transfers
CREATE TABLE stock_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    ref_no VARCHAR(100) NOT NULL,
    from_location_id BIGINT UNSIGNED NOT NULL,
    to_location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft','pending','in_transit','completed','cancelled') DEFAULT 'draft',
    shipping_charges DECIMAL(22,4) DEFAULT 0,
    additional_notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE stock_transfer_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_transfer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4.8 Pricing & Discounts

```sql
-- Selling Price Groups
CREATE TABLE selling_price_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    price_type VARCHAR(50) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Discounts
CREATE TABLE discounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    brand_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NULL,
    priority INT NULL,
    discount_type VARCHAR(20) NULL,
    discount_amount DECIMAL(22,4) DEFAULT 0,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    applicable_in_spg BOOLEAN DEFAULT FALSE,
    applicable_in_cg BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Coupons
CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    type ENUM('percentage','fixed') NOT NULL,
    value DECIMAL(22,4) NOT NULL,
    minimum_amount DECIMAL(22,4) DEFAULT 0,
    maximum_discount DECIMAL(22,4) NULL,
    usage_limit INT NULL,
    used_count INT DEFAULT 0,
    starts_at DATE NULL,
    ends_at DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY unique_code (business_id, code)
);
```

### 4.9 Accounting

```sql
-- Accounts
CREATE TABLE account_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    account_number VARCHAR(100) NULL,
    account_details TEXT NULL,
    account_type_id INT NULL,
    opening_balance DECIMAL(22,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE account_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(22,4) NOT NULL,
    balance DECIMAL(22,4) NULL,
    transaction_id BIGINT UNSIGNED NULL,
    type ENUM('debit','credit') NULL,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Cash Registers
CREATE TABLE cash_registers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    opening_amount DECIMAL(22,4) DEFAULT 0,
    closing_amount DECIMAL(22,4) NULL,
    card_amount DECIMAL(22,4) NULL,
    cheques TEXT NULL,
    denominations JSON NULL,
    status ENUM('open','close') NOT NULL,
    closed_at DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE cash_register_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_register_id BIGINT UNSIGNED NOT NULL,
    transaction_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4.10 Invoicing

```sql
-- Invoice Schemes
CREATE TABLE invoice_schemes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    scheme_type ENUM('auto','blank','manual') DEFAULT 'auto',
    prefix VARCHAR(50) NULL,
    counter_length INT DEFAULT 1,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Invoice Layouts
CREATE TABLE invoice_layouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    qr_code BOOLEAN DEFAULT FALSE,
    common_settings JSON NULL,
    letter_head TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Reference Counters
CREATE TABLE reference_counters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    ref_type VARCHAR(50) NOT NULL,
    ref_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_ref (business_id, ref_type)
);
```

### 4.11 Supporting Tables

```sql
-- Expenses
CREATE TABLE expense_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NULL,
    expense_category_id BIGINT UNSIGNED NULL,
    ref_no VARCHAR(100) NULL,
    location_id BIGINT UNSIGNED NULL,
    transaction_date DATE NOT NULL,
    amount DECIMAL(22,4) NOT NULL,
    paid_by BIGINT UNSIGNED NULL,
    account_id INT NULL,
    document VARCHAR(255) NULL,
    additional_notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Activity Log
CREATE TABLE activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    log_name VARCHAR(255) NULL,
    description TEXT NOT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id BIGINT UNSIGNED NULL,
    causer_type VARCHAR(255) NULL,
    causer_id BIGINT UNSIGNED NULL,
    properties JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Document & Notes (polymorphic)
CREATE TABLE document_and_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notable_type VARCHAR(255) NOT NULL,
    notable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    document VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Printers
CREATE TABLE printers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    connection_type ENUM('network','windows','linux','mac') NOT NULL,
    ip_address VARCHAR(50) NULL,
    path VARCHAR(255) NULL,
    char_per_line INT DEFAULT 42,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Notification Templates
CREATE TABLE notification_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('email','sms') NOT NULL,
    mail_class VARCHAR(255) NULL,
    subject TEXT NULL,
    body TEXT NOT NULL,
    whatsapp_text TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Memberships
CREATE TABLE memberships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    membership_no VARCHAR(50) NOT NULL,
    points_balance INT DEFAULT 0,
    tier ENUM('bronze','silver','gold','platinum') DEFAULT 'bronze',
    expires_at DATE NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_membership (business_id, membership_no)
);

CREATE TABLE reward_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    points INT NOT NULL,
    type ENUM('earn','redeem') NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    expires_at DATE NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 5. SCALE INTEGRATION (Timbangan & Label Printing)

### 5.1 Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│                    POS KasirPro (Web App)                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Scale Service │  │ PLU Manager  │  │ Label Printer│      │
│  │   (WebSocket) │  │              │  │   Service    │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │              │
└─────────┼──────────────────┼──────────────────┼──────────────┘
          │                  │                  │
    ┌─────▼─────┐     ┌─────▼─────┐     ┌─────▼─────┐
    │ Digital   │     │ Database  │     │ Label     │
    │ Scale     │     │ (MySQL)   │     │ Printer   │
    │ RS232/LAN │     │           │     │ RS232/LAN │
    └───────────┘     └───────────┘     └───────────┘
```

### 5.2 Database Schema

```sql
-- Konfigurasi Timbangan
CREATE TABLE scales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    model VARCHAR(100) NULL,
    connection_type ENUM('serial','tcpip','usb','bluetooth') NOT NULL,
    port VARCHAR(20) NULL,
    baud_rate INT DEFAULT 9600,
    data_bits TINYINT DEFAULT 8,
    stop_bits TINYINT DEFAULT 1,
    parity ENUM('none','even','odd') DEFAULT 'none',
    ip_address VARCHAR(50) NULL,
    port_number INT DEFAULT 5000,
    protocol VARCHAR(50) NULL,
    is_label_printer BOOLEAN DEFAULT FALSE,
    label_width INT DEFAULT 40,
    label_height INT DEFAULT 30,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);

-- PLU (Price Look-Up)
CREATE TABLE scale_plu (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    plu_code VARCHAR(20) NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NULL,
    department TINYINT DEFAULT 1,
    price_per_kg DECIMAL(10,2) NOT NULL,
    tare_weight DECIMAL(10,4) DEFAULT 0,
    unit ENUM('kg','g','pcs') DEFAULT 'kg',
    is_active BOOLEAN DEFAULT TRUE,
    label_name VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_plu (business_id, plu_code)
);

-- PLU Groups
CREATE TABLE scale_plu_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    group_code TINYINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_group (business_id, group_code)
);

-- Label Templates
CREATE TABLE scale_label_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    width_mm INT DEFAULT 40,
    height_mm INT DEFAULT 30,
    layout JSON NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 5.3 Scale Bridge Service (Node.js)

**Teknologi:**
- `node-serialport` untuk RS232
- `socket.io` untuk WebSocket ke browser
- System tray untuk background service

**Fitur:**
- Auto-reconnect jika koneksi putus
- Multiple scale support
- Real-time weight display di POS
- PLU sync ke timbangan

**Protocol yang Didukung:**
- CAS (CAS-TF / CAS-ED)
- Acom
- Mettler Toledo (MT-SICS)
- Custom ASCII

---

## 6. BARCODE CENTER (Master Product Database)

### 6.1 Konsep

Barcode Center dikelola oleh **Superadmin SaaS**. Tenant tidak bisa edit/hapus data Barcode Center. Fungsi Barcode Center adalah memudahkan tenant menginput data produk tanpa scan satu per satu.

```
┌─────────────────────────────────────────────────────────────────┐
│                     SUPERADMIN (SaaS Owner)                     │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │              BARCODE CENTER (Master Database)            │   │
│  │                                                         │   │
│  │  • Ribuan produk sudah terdaftar (EAN-13 dari pabrik)   │   │
│  │  • Nama produk, kategori, gambar, barcode               │   │
│  │  • Data ini SAMA untuk semua tenant                     │   │
│  │  • Tenant TIDAK bisa edit/hapus data ini                │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        TENANT (Toko/Kios)                       │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  1. Search di Barcode Center                            │   │
│  │  2. Pilih produk yang dijual                            │   │
│  │  3. Atur harga jual sendiri (mark-up)                   │   │
│  │  4. Atur stok awal                                      │   │
│  │  5. Produk masuk ke daftar produk tenant                │   │
│  │                                                         │   │
│  │  ⛔ Jika produk TIDAK ada di Barcode Center:            │   │
│  │     → Tenant bisa input manual                          │   │
│  │     → Data tersimpan HANYA di tenant itu                │   │
│  │     → Superadmin dapat notifikasi                       │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Database Schema

```sql
-- Master Product Database (Superadmin only)
CREATE TABLE barcode_center (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(50) NOT NULL UNIQUE,
    barcode_type ENUM('EAN13','EAN8','UPCA','UPCE','CODE128','CODE39') NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL,
    category_id BIGINT UNSIGNED NULL,
    brand_id BIGINT UNSIGNED NULL,
    unit_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    weight DECIMAL(10,3) NULL,
    origin_country VARCHAR(100) NULL,
    manufacturer VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Kategori di Barcode Center
CREATE TABLE barcode_center_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    icon VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Brand di Barcode Center
CREATE TABLE barcode_center_brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logo VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Riwayat import dari Barcode Center
CREATE TABLE barcode_center_imports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    barcode_center_id BIGINT UNSIGNED NOT NULL,
    custom_price DECIMAL(22,4) NULL,
    initial_stock INT DEFAULT 0,
    imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (barcode_center_id) REFERENCES barcode_center(id)
);

-- Produk Tenant (manual input, BUKAN dari Barcode Center)
CREATE TABLE tenant_custom_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    barcode VARCHAR(50) NULL,
    category VARCHAR(255) NULL,
    cost_price DECIMAL(22,4) NULL,
    selling_price DECIMAL(22,4) NULL,
    quantity INT DEFAULT 0,
    image VARCHAR(255) NULL,
    status ENUM('pending_review','approved','rejected') DEFAULT 'pending_review',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);

-- Notifikasi ke Superadmin
CREATE TABLE barcode_center_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    tenant_custom_product_id BIGINT UNSIGNED NOT NULL,
    notification_type ENUM('new_product','product_claim','data_correction') NOT NULL,
    message TEXT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (tenant_custom_product_id) REFERENCES tenant_custom_products(id)
);
```

### 6.3 Keamanan Barcode Center

| Ancaman | Solusi |
|---------|--------|
| **Web Scraping** | Rate limiting (100 req/hour), CAPTCHA, obfuscation |
| **Mass Copy** | Tidak ada fitur export, max 20 item per search |
| **API Abuse** | API throttling, watermark, logging |
| **Screenshot** | Detect abnormal pattern (banyak view berturut-turut) |
| **Database Leak** | Enripsi, akses terbatas, audit log |

**Security Implementation:**
```php
// Rate Limiting
Route::middleware('throttle:100,hour')->group(function () {
    Route::get('/barcode-center/search', ...);
    Route::get('/barcode-center/product/{id}', ...);
});

// No Export Endpoint
// Tidak ada route untuk export/dump semua data

// Search Result Limit
$results = $query->limit(20)->get();

// Activity Logging
ActivityLog::create([
    'description' => 'Barcode center product viewed',
    'subject_id' => $product->id,
    'properties' => ['ip' => request()->ip()]
]);
```

### 6.4 Fitur Barcode Center

**Superadmin Panel:**
| Fitur | Keterangan |
|-------|------------|
| Dashboard | Total produk, statistik |
| Product List | List semua produk |
| Add Product | Form tambah produk |
| Bulk Import | Import dari CSV/Excel |
| Category Manager | Kelola kategori |
| Brand Manager | Kelola brand |

**Tenant Features:**
| Fitur | Keterangan |
|-------|------------|
| Search Products | Cari di Barcode Center |
| Filter by Category | Filter kategori |
| Filter by Brand | Filter brand |
| Quick Add | Tambah dengan satu klik |
| Custom Pricing | Atur harga jual |
| Initial Stock | Atur stok awal |

---

## 7. CONTROLLER STRUCTURE

### 7.1 Thin Controllers Pattern

```php
class SellPosController extends Controller
{
    public function __construct(
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil,
        ContactUtil $contactUtil,
        CashRegisterUtil $cashRegisterUtil,
    ) {
        // Dependency injection
    }

    public function store(SellPosRequest $request)
    {
        // 1. Permission check
        // 2. Cash register check
        // 3. Credit limit check
        // 4. Calculate invoice total
        // 5. DB transaction begin
        // 6. Create transaction
        // 7. Create sell lines
        // 8. Create payments
        // 9. Decrease stock
        // 10. Update cash register
        // 11. Update payment status
        // 12. Map purchase-sell (FIFO)
        // 13. Send notifications
        // 14. Activity log
        // 15. DB commit
    }
}
```

### 7.2 Controllers to Create

| Controller | Lines (UltimatePOS) | Adaptasi |
|------------|---------------------|----------|
| `SellPosController` | 3,265 | **COPY** - simplify |
| `SellController` | 1,800 | **COPY** - simplify |
| `PurchaseController` | 1,436 | **COPY** - simplify |
| `ProductController` | 2,428 | **COPY** - simplify |
| `ContactController` | 1,716 | **COPY** - simplify |
| `StockAdjustmentController` | 520 | **COPY** |
| `StockTransferController` | 960 | **COPY** |
| `ExpenseController` | 891 | **COPY** |
| `CashRegisterController` | - | **CREATE** |
| `AccountController` | - | **CREATE** |
| `ReportController` | - | **CREATE** |
| `ScaleController` | - | **CREATE** |
| `BarcodeCenterController` | - | **CREATE** |
| `MarketplaceController` | - | **CREATE** |

---

## 8. TIMELINE FINAL

| Fase | Durasi | Keterangan |
|------|--------|------------|
| **Fase 0: Fondasi** | 1 minggu | Laravel 11 + FrankenPHP + Octane, base structure |
| **Fase 1: Multi-Tenant** | 1.5 minggu | Business, users, roles (reuse seeder) |
| **Fase 2: Produk** | 1.5 minggu | Products, variations, stocks (reuse ProductUtil) |
| **Fase 3: Sales & POS** | 2 minggu | Transaksi, POS, payments (reuse TransactionUtil) |
| **Fase 4: Pembelian** | 1.5 minggu | Purchases, transfers, adjustments |
| **Fase 5: Keuangan** | 1.5 minggu | Accounting, cash register |
| **Fase 6: Harga & Diskon** | 1 minggu | Price groups, discounts, coupons |
| **Fase 7: Membership** | 0.5 minggu | Points, tiers, CRM |
| **Fase 8: Marketplace** | 2 minggu | Shopee, Tokopedia, Lazada API |
| **Fase 9: Laporan** | 1.5 minggu | Reports, dashboard |
| **Fase 10: Mobile App** | 3 minggu | Flutter app |
| **Fase 11: Scale Integration** | 2 minggu | Node.js bridge, PLU, label printing |
| **Fase 12: Barcode Center** | 2 minggu | Master DB, security, tenant flow |
| **TOTAL** | **~23 minggu (5.75 bulan)** | |

---

## 9. ESTIMASI KODE YANG BISA DI-COPY

| Kategori | File | Lines | Effort |
|----------|------|-------|--------|
| **Utils** | 10 files | ~12,000 | Tinggal rename namespace |
| **Models** | 47 files | ~8,000 | Copy + tambah business_id scope |
| **Seeders** | 4 files | ~1,500 | Langsung pakai |
| **Receipt Templates** | 11 files | ~5,000 | Copy + sesuaikan |
| **POS Views** | 15 files | ~4,000 | Adaptasi dari UltimatePOS |
| **Controllers** | 12 files | ~15,000 | Copy + simplify |
| **Migrations** | ~50 files | ~4,000 | Copy + tambah kolom |
| **Config** | 5 files | ~500 | Copy + sesuaikan |
| **TOTAL** | ~154 files | ~50,000 | **~60% reusable** |

---

## 10. YANG HARUS DIBUAT BARU

1. **Scale Bridge Service** - Node.js + WebSocket untuk RS232/LAN timbangan
2. **Barcode Center** - Master DB + security + tenant flow
3. **Marketplace Integration** - Shopee, Tokopedia, Lazada API
4. **Mobile API Endpoints** - REST API untuk Flutter
5. **Midtrans Integration** - QRIS, GoPay, OVO, Dana, Card
6. **Multi-tenant Middleware** - Business scope middleware
7. **Indonesian Tax Rules** - PPN, PPh calculations
8. **Barcode Scanner Integration** - Real-time search
9. **Customer Display Screen** - Layar kedua
10. **Offline Mode** - Transaksi tanpa internet

---

## 11. DEPENDENCIES

### Composer Packages
```json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/octane": "^2.0",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-medialibrary": "^11.0",
        "barryvdh/laravel-dompdf": "^2.0",
        "intervention/image": "^3.0"
    }
}
```

### Docker Stack
```yaml
services:
  app:
    image: dunglas/frankenphp
    # FrankenPHP + Laravel Octane
  mysql:
    image: mysql:8.0
  redis:
    image: redis:7-alpine
  node:
    image: node:20-alpine
    # Scale Bridge Service
```

---

*Plan v2.1 — Juni 2026*
