# POS KasirPro - Ringkasan, Tech Stack & Akses Aplikasi

---

## 1. RINGKASAN

### Tentang POS KasirPro
POS KasirPro adalah aplikasi Point of Sale (POS) profesional yang dirancang untuk:
- **Supermarket** - Barcode scan, multi-payment, stok besar, receipt thermal
- **Toko Kelontong** - Transaksi cepat, stok sederhana, membership
- **Online Shop** - Integrasi marketplace, multi-gudang, shipping

### Fitur Utama
1. **Multi-Tenant** - 1 aplikasi untuk banyak toko (SaaS model)
2. **POS Interface** - Barcode scan, cart, multi-payment
3. **Produk Multi-Variant** - Warna, size, harga berbeda per variant
4. **Integrasi Timbangan** - RS232/LAN, PLU, label printing
5. **Barcode Center** - Master product database (Superadmin managed)
6. **Marketplace** - Shopee, Tokopedia, Lazada integration
7. **Payment Gateway** - Midtrans (QRIS, GoPay, OVO, Dana, Card)
8. **Accounting** - Double entry, cash register
9. **Mobile App** - Flutter (Android + iOS)
10. **Laporan** - Sales, stock, P&L, customer, supplier reports

### Target Pengguna
| Segment | Kebutuhan Utama |
|---------|-----------------|
| Supermarket | Barcode scan, multi-payment, stok besar, multi-cabang |
| Toko Kelontong | Transaksi cepat, stok sederhana, membership |
| Online Shop | Integrasi marketplace, multi-gudang, shipping |

---

## 2. TECH STACK

| Layer | Technology | Keterangan |
|-------|------------|------------|
| **Backend** | Laravel 11, PHP 8.3 | Framework utama |
| **Frontend** | Blade + Bootstrap 5 + jQuery | UI framework |
| **Database** | MySQL 8.0 | Primary database |
| **Cache/Queue** | Redis | Session, queue, caching |
| **Deployment** | Docker + FrankenPHP + Laravel Octane | High performance |
| **Mobile** | Flutter | Cross-platform (Android + iOS) |
| **API** | Laravel Sanctum | Token-based auth untuk Flutter |
| **Payment** | Midtrans | Gateway utama |
| **Marketplace** | Shopee, Tokopedia, Lazada | API integration |
| **Scale Bridge** | Node.js + WebSocket | RS232/LAN timbangan |
| **Label Printer** | ESC/POS / TSPL / ZPL | Support semua merek |
| **Charts** | ApexCharts | Dashboard visual |
| **PDF** | DomPDF | Invoice & laporan |

---

## 3. APLIKASI

### 3.1 URL Akses (Development)

| Service | URL | Port |
|---------|-----|------|
| **POS App** | http://localhost:8000 | 8000 |
| **Scale Bridge** | http://localhost:3000 | 3000 |
| **MySQL** | localhost:3306 | 3306 |
| **Redis** | localhost:6379 | 6379 |

### 3.2 Login Akun Demo

| User | Email | Password | Role | Akses |
|------|-------|----------|------|-------|
| **Admin** | admin@kasirpro.com | password | admin | Full akses |
| **Kasir** | kasir@kasirpro.com | password | cashier | POS & products |

### 3.3 Roles & Permissions

| Role | Akses |
|------|-------|
| **superadmin** | Full akses semua modul |
| **admin** | Full akses semua modul |
| **manager** | Products, contacts, purchases, sells, expenses, reports |
| **cashier** | POS, products view, contacts view |
| **warehouse_staff** | Products, stock adjustments, stock transfers |
| **accountant** | Expenses, financial reports |

---

## 4. CARA INSTALL

### 4.1 Pakai Docker (Recommended)

```bash
# 1. Clone/Kopy project
cd kasirpro

# 2. Jalankan Docker Compose
docker-compose up -d

# 3. Install dependencies
docker-compose exec app composer install

# 4. Setup environment
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# 5. Run migrations
docker-compose exec app php artisan migrate

# 6. Seed database
docker-compose exec app php artisan db:seed --class=KasirProSeeder

# 7. Start Octane
docker-compose exec app php artisan octane:start --server=frankenphp
```

### 4.2 Pakai Local PHP

```bash
# 1. Pastikan PHP 8.3 + Composer terinstall
php -v
composer -V

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
#    - Buat database 'kasirpro' di MySQL
#    - Edit .env sesuai konfigurasi DB

# 5. Run migrations
php artisan migrate

# 6. Seed database
php artisan db:seed --class=KasirProSeeder

# 7. Jalankan aplikasi
php artisan serve
# Atau pakai Octane
php artisan octane:start --server=frankenphp
```

---

## 5. DATABASE

### 5.1 Konfigurasi (.env)

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasirpro
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 5.2 Total Tables: 50+

| Section | Tables |
|---------|--------|
| Business | businesses, business_locations, currencies |
| Auth | users, roles, permissions, model_has_roles, model_has_permissions |
| Products | products, product_variations, variations, variation_location_details, variation_group_prices |
| Contacts | contacts, customer_groups |
| Transactions | transactions, transaction_sell_lines, purchase_lines, transaction_payments |
| Stock | stock_adjustments, stock_adjustment_lines, stock_transfers, stock_transfer_lines |
| Accounting | accounts, account_transactions, cash_registers, cash_register_transactions |
| Pricing | selling_price_groups, discounts, coupons |
| Membership | memberships, reward_points |
| Scale | scales, scale_plu, scale_plu_groups, scale_label_templates |
| Barcode Center | barcode_center, barcode_center_categories, barcode_center_brands |
| Marketplace | ecommerce_channels, ecommerce_products, ecommerce_orders |

---

## 6. API ENDPOINTS

### 6.1 Web Routes

| Method | URL | Description |
|--------|-----|-------------|
| GET | /dashboard | Dashboard |
| GET/POST | /products | Product CRUD |
| GET/POST | /contacts | Contact CRUD |
| GET | /pos | POS Screen |
| POST | /pos/store | Create sale |
| GET/POST | /purchases | Purchase CRUD |
| GET/POST | /expenses | Expense CRUD |
| GET | /reports/* | Reports |
| GET/POST | /memberships | Membership |
| GET/POST | /barcode-center | Barcode Center |
| GET/POST | /scales | Scale Management |
| GET/POST | /marketplace | Marketplace |

### 6.2 API Routes (Mobile)

| Method | URL | Description |
|--------|-----|-------------|
| POST | /api/v1/login | Login |
| POST | /api/v1/logout | Logout |
| GET | /api/v1/profile | User profile |
| GET | /api/v1/products | List products |
| GET | /api/v1/products/{id} | Product detail |
| POST | /api/v1/pos/sale | Create sale |
| GET | /api/v1/pos/recent | Recent transactions |
| POST | /api/v1/pos/search-barcode | Search by barcode |
| GET | /api/v1/dashboard | Dashboard stats |

---

## 7. DOKUMENTASI TAMBAHAN

| File | Keterangan |
|------|------------|
| `plan-v1.md` | Rencana awal |
| `plan-v2.md` | Rencana final dengan semua fitur |
| `ANALISA_POS.md` | Analisa DreamsPOS |
| `PERBANDINGAN_DREAMSVSULTIMATE.md` | Perbandingan DreamsPOS vs UltimatePOS |
| `progress-plan-v2.md` | Progress pengerjaan |
| `scale-bridge/README.md` | Dokumentasi Scale Bridge |

---

## 8. KONTAK & SUPPORT

Untuk pertanyaan dan support:
- **GitHub**: [POS KasirPro Repository]
- **Email**: [developer@kasirpro.com]

---

*POS KasirPro v1.0 - Juni 2026*
