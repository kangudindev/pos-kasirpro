# POS KasirPro - Rencana Pengembangan v1

**Versi:** 1.0
**Tanggal:** Juni 2026
**Target:** Supermarket, Toko Kelontong, Online Shop

---

## 1. VISION & SCOPE

### 1.1 Visi
Membangun aplikasi POS (Point of Sale) profesional yang menggabungkan fitur terbaik dari DreamsPOS (template) dan UltimatePOS (reference) dengan fokus pada pasar Indonesia.

### 1.2 Target Pengguna
| Segment | Kebutuhan Utama |
|---------|-----------------|
| **Supermarket** | Barcode scan, multi-payment, stok besar, receipt thermal, multi-cabang |
| **Toko Kelontong** | Transaksi cepat, stok sederhana, membership, cicilan |
| **Online Shop** | Integrasi marketplace, multi-gudang, shipping, COD |

### 1.3 Tech Stack
| Layer | Technology |
|-------|------------|
| Backend | Laravel 11, PHP 8.3 |
| Database | MySQL 8.0 / PostgreSQL 15 |
| Cache | Redis |
| Queue | Redis / Database |
| Frontend | Blade + Bootstrap 5 + jQuery (existing DreamsPOS) |
| Mobile | React Native / Flutter (Fase 9) |
| API | Laravel Sanctum (token-based) |
| Payment | Midtrans, Xendit, DOKU |
| Marketplace | Shopee API, Tokopedia API, Lazada API |
| Receipt | ESC/POS (thermal printer) |
| PDF | DomPDF / Snappy |
| Charts | ApexCharts |
| Deployment | Docker + Nginx |

---

## 2. FASE 0: FONDASI (Migrasi dari DreamsPOS)

### 2.1 Upgrade Framework
- [ ] Laravel 10 → **Laravel 11**
- [ ] PHP 8.2 → **PHP 8.3**
- [ ] Update semua dependencies di `composer.json`
- [ ] Tambah packages baru:
  - `spatie/laravel-permission` (roles & permissions)
  - `spatie/laravel-medialibrary` (file uploads)
  - `barryvdh/laravel-dompdf` (PDF generation)
  - `intervention/image` (image processing)
  - `laravel/sanctum` (API auth)
  - `laravel/cashier` (optional - payment abstraction)

### 2.2 Perbaikan Codebase DreamsPOS
- [ ] Hapus hardcoded admin bypass di `CustomAuthController`
- [ ] Buat route groups dengan middleware auth yang benar
- [ ] Buat base controller untuk semua modul
- [ ] Buat service provider untuk register services
- [ ] Setup Spatie permissions (roles, permissions)
- [ ] Buat base model dengan soft deletes

### 2.3 Struktur Direktori Baru
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Pos/           # POS & Sales
│   │   ├── Inventory/     # Products & Stocks
│   │   ├── Purchase/      # Purchases & Suppliers
│   │   ├── Finance/       # Accounting & Expenses
│   │   ├── Report/        # Reports
│   │   ├── Settings/      # Settings
│   │   └── Api/           # API controllers
│   ├── Middleware/
│   │   ├── EnsureBusinessIsActive.php
│   │   └── SetLocale.php
│   └── Requests/          # Form Requests
├── Models/
│   ├── Business/
│   ├── Product/
│   ├── Transaction/
│   ├── Contact/
│   └── Settings/
├── Services/
│   ├── Pos/
│   ├── Inventory/
│   ├── Purchase/
│   ├── Finance/
│   └── Marketplace/
├── Events/                # Event-driven architecture
├── Listeners/
├── Jobs/                  # Queue jobs
├── Notifications/         # Email, SMS, WhatsApp
└── Enums/                 # PHP 8.1+ Enums
```

---

## 3. FASE 1: MULTI-TENANT CORE

### 3.1 Database Schema

#### `businesses`
```sql
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
    settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id),
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);
```

#### `business_locations`
```sql
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
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);
```

#### `currencies`
```sql
CREATE TABLE currencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    code VARCHAR(3) NOT NULL,
    exchange_rate DECIMAL(10,4) DEFAULT 1,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3.2 Users Table Modification
```sql
ALTER TABLE users ADD COLUMN business_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED NULL AFTER business_id;
ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NULL AFTER role_id;
ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NULL AFTER first_name;
ALTER TABLE users ADD COLUMN username VARCHAR(255) UNIQUE NULL AFTER last_name;
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER phone;
ALTER TABLE users ADD COLUMN language CHAR(7) DEFAULT 'en' AFTER avatar;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER language;
ALTER TABLE users ADD COLUMN max_sale_discount DECIMAL(5,2) NULL AFTER is_active;
ALTER TABLE users ADD COLUMN is_commission_agent BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN commission_percent DECIMAL(4,2) DEFAULT 0;
ALTER TABLE users ADD COLUMN user_type ENUM('user','sales_commission_agent') DEFAULT 'user';
ALTER TABLE users ADD COLUMN created_by BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
```

### 3.3 Spatie Permissions
```php
// Roles
$admin = Role::create(['name' => 'admin']);
$manager = Role::create(['name' => 'manager']);
$cashier = Role::create(['name' => 'cashier']);
$warehouseStaff = Role::create(['name' => 'warehouse_staff']);
$accountant = Role::create(['name' => 'accountant']);

// Permissions (per module)
Permission::create(['name' => 'products.view']);
Permission::create(['name' => 'products.create']);
Permission::create(['name' => 'products.edit']);
Permission::create(['name' => 'products.delete']);
// ... (100+ permissions)
```

### 3.4 User-Location Access
```sql
CREATE TABLE user_location_access (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    business_location_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_user_location (user_id, business_location_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (business_location_id) REFERENCES business_locations(id)
);
```

---

## 4. FASE 2: PRODUK & INVENTORI

### 4.1 Database Schema

#### Master Tables
```sql
-- Categories (hierarchical)
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_code VARCHAR(50) NULL,
    parent_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (parent_id) REFERENCES categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Units
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    actual_name VARCHAR(255) NOT NULL,
    short_name VARCHAR(50) NOT NULL,
    allow_decimal BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);

-- Tax Rates
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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### Products
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL,
    sku VARCHAR(255) NOT NULL,
    barcode_type ENUM('C39','C128','EAN13','EAN8','UPCA','UPCE','ITF14') DEFAULT 'C128',
    item_code VARCHAR(100) NULL,
    type ENUM('single','variable') NOT NULL,
    selling_type ENUM('transactional','solution') DEFAULT 'transactional',
    unit_id BIGINT UNSIGNED NOT NULL,
    sub_unit_ids TEXT NULL,
    brand_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    tax_id BIGINT UNSIGNED NULL,
    tax_type ENUM('exclusive','inclusive') DEFAULT 'exclusive',
    cost_price DECIMAL(22,4) DEFAULT 0,
    selling_price DECIMAL(22,4) DEFAULT 0,
    profit_percent DECIMAL(5,2) DEFAULT 0,
    discount_rate DECIMAL(5,2) DEFAULT 0,
    discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
    quantity DECIMAL(22,4) DEFAULT 0,
    quantity_alert DECIMAL(22,4) DEFAULT 0,
    enable_stock BOOLEAN DEFAULT TRUE,
    not_for_selling BOOLEAN DEFAULT FALSE,
    weight DECIMAL(10,3) NULL,
    expiry_period INT NULL,
    expiry_period_type ENUM('days','months','years') NULL,
    image VARCHAR(255) NULL,
    description TEXT NULL,
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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (tax_id) REFERENCES tax_rates(id) ON DELETE SET NULL,
    FOREIGN KEY (warranty_id) REFERENCES warranties(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_business (business_id),
    INDEX idx_sku (sku),
    INDEX idx_name (name)
);
```

#### Variations
```sql
CREATE TABLE product_variations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_dummy BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variation_id) REFERENCES product_variations(id) ON DELETE CASCADE,
    INDEX idx_sku (sub_sku)
);
```

#### Product-Location & Stock
```sql
CREATE TABLE product_locations (
    product_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, location_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES business_locations(id) ON DELETE CASCADE
);

CREATE TABLE product_stocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_stock (product_id, variation_id, location_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variation_id) REFERENCES variations(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES business_locations(id) ON DELETE CASCADE
);

CREATE TABLE product_racks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    rack VARCHAR(100) NULL,
    shelf VARCHAR(100) NULL,
    position VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES business_locations(id) ON DELETE CASCADE
);
```

---

## 5. FASE 3: SALES & POS

### 5.1 Database Schema

#### Transactions
```sql
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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id),
    FOREIGN KEY (tax_id) REFERENCES tax_rates(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (location_id) REFERENCES business_locations(id),
    INDEX idx_business_type (business_id, type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_contact (contact_id),
    INDEX idx_status (status)
);
```

#### Transaction Lines
```sql
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
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variation_id) REFERENCES variations(id),
    FOREIGN KEY (tax_id) REFERENCES tax_rates(id)
);

CREATE TABLE purchase_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    purchase_price DECIMAL(22,4) NOT NULL,
    purchase_price_inc_tax DECIMAL(22,4) DEFAULT 0,
    item_tax DECIMAL(22,4) DEFAULT 0,
    tax_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variation_id) REFERENCES variations(id),
    FOREIGN KEY (tax_id) REFERENCES tax_rates(id)
);
```

#### Payments
```sql
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
    payment_link VARCHAR(255) NULL,
    payment_link_expiry DATETIME NULL,
    note TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
);
```

### 5.2 POS Interface Features
- **Barcode Scanner** - Real-time product search by SKU/barcode
- **Hold/Resume Order** - Simpan sementara, ambil kembali
- **Quick Payment** - Shortcut pembayaran cepat
- **Multi-Payment** - Cash + QRIS + E-Wallet dalam 1 transaksi
- **Receipt Printer** - Thermal 58mm/80mm via ESC/POS
- **Customer Display** - Layar kedua untuk pelanggan
- **Keyboard Shortcuts** - F1-F12 untuk aksi cepat
- **Offline Mode** - Transaksi tanpa internet, sync saat online

### 5.3 Payment Methods
```sql
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('cash','card','bank_transfer','e_wallet','credit') NOT NULL,
    icon VARCHAR(255) NULL,
    config JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);
```

Supported Payment Methods:
| Type | Methods |
|------|---------|
| Cash | Tunai |
| Card | Debit, Kredit (BCA, Mandiri, BNI, BRI) |
| QRIS | QRIS All Bank |
| E-Wallet | GoPay, OVO, Dana, ShopeePay, LinkAja |
| Bank Transfer | BCA, BRI, Mandiri, BNI |
| Credit | Hutang (pay later) |

---

## 6. FASE 4: PEMBELIAN & SUPPLY CHAIN

### 6.1 Database Schema

#### Purchase Orders
```sql
CREATE TABLE purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft','pending','ordered','received','cancelled') DEFAULT 'draft',
    ref_no VARCHAR(100) NULL,
    transaction_date DATE NOT NULL,
    expected_date DATE NULL,
    total_before_tax DECIMAL(22,4) DEFAULT 0,
    tax_amount DECIMAL(22,4) DEFAULT 0,
    discount_amount DECIMAL(22,4) DEFAULT 0,
    shipping_charges DECIMAL(22,4) DEFAULT 0,
    final_total DECIMAL(22,4) DEFAULT 0,
    additional_notes TEXT NULL,
    staff_note TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id),
    FOREIGN KEY (location_id) REFERENCES business_locations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE purchase_order_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    unit_cost DECIMAL(22,4) NOT NULL,
    total DECIMAL(22,4) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variation_id) REFERENCES variations(id)
);
```

#### Stock Transfers
```sql
CREATE TABLE stock_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    ref_no VARCHAR(100) NOT NULL,
    from_location_id BIGINT UNSIGNED NOT NULL,
    to_location_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft','in_transit','received','cancelled') DEFAULT 'draft',
    shipping_charges DECIMAL(22,4) DEFAULT 0,
    additional_notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (from_location_id) REFERENCES business_locations(id),
    FOREIGN KEY (to_location_id) REFERENCES business_locations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_transfer_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_transfer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variation_id) REFERENCES variations(id)
);
```

#### Stock Adjustments
```sql
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
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (location_id) REFERENCES business_locations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_adjustment_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(22,4) NOT NULL,
    unit_price DECIMAL(22,4) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES stock_adjustments(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variation_id) REFERENCES variations(id)
);
```

---

## 7. FASE 5: KEUANGAN & ACCOUNTING

### 7.1 Database Schema

#### Accounts
```sql
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
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
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
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
);
```

#### Expenses
```sql
CREATE TABLE expense_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (parent_id) REFERENCES expense_categories(id)
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
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id),
    FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id),
    FOREIGN KEY (location_id) REFERENCES business_locations(id),
    FOREIGN KEY (paid_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### Cash Registers
```sql
CREATE TABLE cash_registers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    closing_amount DECIMAL(22,4) NULL,
    card_amount DECIMAL(22,4) NULL,
    cheques TEXT NULL,
    denominations JSON NULL,
    status ENUM('open','close') NOT NULL,
    closed_at DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (location_id) REFERENCES business_locations(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE cash_register_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_register_id BIGINT UNSIGNED NOT NULL,
    transaction_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (payment_id) REFERENCES transaction_payments(id)
);
```

---

## 8. FASE 6: HARGA & DISKON

### 8.1 Database Schema

```sql
CREATE TABLE selling_price_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    price_type VARCHAR(50) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);

CREATE TABLE customer_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(5,2) DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

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
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (location_id) REFERENCES business_locations(id)
);

CREATE TABLE discount_variations (
    discount_id BIGINT UNSIGNED NOT NULL,
    variation_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (discount_id, variation_id),
    FOREIGN KEY (discount_id) REFERENCES discounts(id) ON DELETE CASCADE,
    FOREIGN KEY (variation_id) REFERENCES variations(id) ON DELETE CASCADE
);

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
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    UNIQUE KEY unique_code (business_id, code)
);
```

---

## 9. FASE 7: MEMBERSHIP & CRM

### 9.1 Database Schema

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
    created_by BIGINT UNSIGNED NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    custom_field_1 VARCHAR(255) NULL,
    custom_field_2 VARCHAR(255) NULL,
    custom_field_3 VARCHAR(255) NULL,
    custom_field_4 VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_business_type (business_id, type),
    INDEX idx_name (name)
);

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
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id),
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
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id)
);

CREATE TABLE user_contact_access (
    user_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, contact_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
);
```

---

## 10. FASE 8: INTEGRASI MARKETPLACE

### 10.1 Database Schema

```sql
CREATE TABLE ecommerce_channels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    platform ENUM('shopee','tokopedia','lazada','woocommerce') NOT NULL,
    name VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NULL,
    api_secret VARCHAR(255) NULL,
    shop_id VARCHAR(100) NULL,
    access_token TEXT NULL,
    refresh_token TEXT NULL,
    token_expires_at DATETIME NULL,
    settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_sync_at DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id)
);

CREATE TABLE ecommerce_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    channel_id BIGINT UNSIGNED NOT NULL,
    channel_product_id VARCHAR(100) NULL,
    channel_sku VARCHAR(100) NULL,
    channel_price DECIMAL(22,4) NULL,
    channel_stock INT DEFAULT 0,
    status ENUM('active','inactive','error') DEFAULT 'active',
    last_sync_at DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (channel_id) REFERENCES ecommerce_channels(id),
    UNIQUE KEY unique_product_channel (product_id, channel_id)
);

CREATE TABLE ecommerce_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    channel_id BIGINT UNSIGNED NOT NULL,
    channel_order_id VARCHAR(100) NOT NULL,
    transaction_id BIGINT UNSIGNED NULL,
    status VARCHAR(50) NULL,
    buyer_name VARCHAR(255) NULL,
    buyer_phone VARCHAR(50) NULL,
    shipping_address TEXT NULL,
    total_amount DECIMAL(22,4) NULL,
    shipping_cost DECIMAL(22,4) NULL,
    synced_at DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id),
    FOREIGN KEY (channel_id) REFERENCES ecommerce_channels(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    UNIQUE KEY unique_channel_order (channel_id, channel_order_id)
);
```

### 10.2 Integration Strategy
| Platform | Sync | Features |
|----------|------|----------|
| **Shopee** | Real-time webhook + Cron 5 min | Stok, harga, pesanan, resi |
| **Tokopedia** | Real-time webhook + Cron 5 min | Stok, harga, pesanan, resi |
| **Lazada** | Real-time webhook + Cron 5 min | Stok, harga, pesanan, resi |

---

## 11. FASE 9: LAPORAN & ANALYTICS

### 11.1 Reports List
1. **Sales Reports** - Harian, mingguan, bulanan, tahunan
2. **Purchase Reports** - Pembelian per supplier, per produk
3. **Inventory Reports** - Stok saat ini, mutasi, expired
4. **Financial Reports** - P&L, balance sheet, cash flow
5. **Tax Reports** - Pajak penjualan & pembelian
6. **Customer Reports** - Member, top buyer, aging
7. **Supplier Reports** - Hutang, performa delivery
8. **Cash Register Reports** - Kas masuk/keluar, selisih
9. **Commission Reports** - Komisi sales
10. **Marketplace Reports** - Per platform, per channel
11. **Expense Reports** - Pengeluaran per kategori
12. **Profit Reports** - Laba per produk, per kategori

### 11.2 Dashboard Widgets
- Sales today / this week / this month
- Top 10 selling products
- Low stock alerts
- Pending purchase orders
- Revenue chart (line)
- Sales by category (pie)
- Customer growth (bar)
- Payment method distribution

---

## 12. FASE 10: MOBILE APP

### 12.1 Tech Stack
- **Framework**: React Native / Flutter
- **State Management**: Redux / Riverpod
- **API**: Laravel Sanctum (token-based)
- **Offline**: SQLite + sync queue

### 12.2 Features
| Module | Features |
|--------|----------|
| **POS** | Barcode scan, quick payment, receipt print |
| **Inventory** | Stock opname, receive goods, transfer |
| **Dashboard** | Real-time sales, charts |
| **Approval** | PO approval, adjustment approval |
| **Delivery** | Track delivery, proof of delivery |

---

## 13. TIMELINE

| Fase | Durasi | Milestone |
|------|--------|-----------|
| Fase 0: Fondasi | 2 minggu | Laravel 11, base structure |
| Fase 1: Multi-Tenant | 2 minggu | Business, users, roles |
| Fase 2: Produk | 2 minggu | Products, variants, stocks |
| Fase 3: Sales & POS | 3 minggu | Transaksi, POS, payments |
| Fase 4: Pembelian | 2 minggu | Purchases, transfers, adjustments |
| Fase 5: Keuangan | 2 minggu | Accounting, cash register |
| Fase 6: Harga & Diskon | 1 minggu | Price groups, discounts |
| Fase 7: Membership | 1 minggu | Points, tiers, CRM |
| Fase 8: Marketplace | 3 minggu | Shopee, Tokopedia, Lazada |
| Fase 9: Laporan | 2 minggu | Reports, dashboard |
| Fase 10: Mobile App | 4 minggu | React Native / Flutter |
| **TOTAL** | **~24 minggu (6 bulan)** | |

---

## 14. DEPENDENCIES

### Composer Packages
```json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-medialibrary": "^11.0",
        "barryvdh/laravel-dompdf": "^2.0",
        "intervention/image": "^3.0",
        "laravel/cashier": "^15.0"
    }
}
```

### NPM Packages
```json
{
    "dependencies": {
        "apexcharts": "^3.45.0",
        "select2": "^4.1.0",
        "sweetalert2": "^11.0.0",
        "toastr": "^2.1.0"
    }
}
```

---

## 15. SEEDER DATA

### Initial Data
- 1 Super Admin user
- 1 Demo business
- 3 demo locations (Toko Pusat, Gudang, Toko Cabang)
- 5 roles (Super Admin, Admin, Manager, Cashier, Warehouse Staff)
- 50+ permissions
- 5 payment methods
- 5 tax rates
- 10 categories
- 5 brands
- 10 units
- 50 sample products
- 20 sample customers
- 10 sample suppliers
- 10 sample sales transactions
- 5 sample purchase transactions

---

*Plan v1.0 — Juni 2026*
