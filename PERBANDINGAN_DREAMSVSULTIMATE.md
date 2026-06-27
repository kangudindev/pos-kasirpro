# Perbandingan DreamsPOS v2.0.6 vs UltimatePOS v6.8

**Tanggal Analisa:** Juni 2026
**Tujuan:** Analisa gap & fitur antara DreamsPOS (template) dengan UltimatePOS (reference POS system)

---

## 1. RINGKASAN EKSEKUTIF

| Aspek | DreamsPOS v2.0.6 | UltimatePOS v6.8 |
|-------|-------------------|-------------------|
| **Tipe** | Template/Startkit | Full POS Application |
| **Total Tabel** | 38 tabel | ~50+ tabel (base) + module tables |
| **Multi-tenant** | Tidak | Ya (`business_id` di semua tabel) |
| **Roles & Permissions** | Sederhana (1 tabel) | Spatie (polymorphic, granular) |
| **Soft Deletes** | Tidak ada | Ya (di banyak tabel) |
| **Modular System** | Tidak | Ya (21 modules) |
| **Arsitektur Transaksi** | Tabel terpisah per tipe | Single `transactions` + type enum |
| **Kontak** | 2 tabel terpisah (customers, suppliers) | 1 tabel `contacts` + type field |
| **Multi-Currency** | Tidak | Ya (`currencies` table) |
| **Accounting** | Tidak | Ya (`accounts`, `account_transactions`) |
| **Restaurant Module** | Tidak | Ya (built-in) |

---

## 2. ARSITEKTUR DATABASE

### 2.1 Multi-Tenancy

| Fitur | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Multi-business/tenant | Tidak ada | `business_id` di semua tabel utama |
| Business table | Tidak ada | `business` (currency, tax, logo, settings) |
| Business locations | `stores` + `warehouses` | `business_locations` (unified) |
| User access control | Role-based sederhana | `user_contact_access` pivot |

**Kesimpulan:** UltimatePOS dirancang untuk SaaS multi-tenant, DreamsPOS untuk single-tenant. Jika DreamsPOS ingin multi-tenant, perlu menambahkan `business_id` ke semua tabel utama.

### 2.2 Roles & Permissions

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel roles | `roles` (id, name, description) | `roles` (Spatie: id, name, guard_name) |
| Permissions | Tidak ada | `permissions`, `model_has_permissions`, `role_has_permissions` |
| User role link | `users.role_id` FK | Polymorphic via Spatie |
| Granularity | Level admin/staff/manager | Per-action permissions |

**Gap:** DreamsPOS belum punya sistem permission granular. Untuk POS skala menengah kebutuhan ini penting.

### 2.3 Soft Deletes

| Tabel | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| users | Tidak | Ya |
| contacts | Tidak | Ya |
| products | Tidak | Ya |
| variations | Tidak | Ya |
| brands | Tidak | Ya |
| categories | Tidak | Ya |
| units | Tidak | Ya |
| tax_rates | Tidak | Ya |
| selling_price_groups | Tidak | Ya |
| business_locations | Tidak | Ya |

**Rekomendasi:** DreamsPOS sebaiknya menambahkan soft deletes untuk tabel-tabel master data agar data tidak hilang permanen.

---

## 3. MODUL PRODUK

### 3.1 Tabel Produk

| Kolom | DreamsPOS | UltimatePOS | Catatan |
|-------|-----------|-------------|---------|
| name | Ya | Ya | |
| sku | Ya | Ya | |
| type (single/variable) | Ya | Ya | |
| unit_id | Ya | Ya | |
| brand_id | Ya | Ya | |
| category_id | Ya | Ya | |
| sub_category_id | Tidak | Ya | DreamsPOS pakai hierarchical categories |
| tax / tax_id | `tax_rate` (decimal) | `tax` (FK → tax_rates) | UltimatePOS lebih fleksibel |
| tax_type | Ya | Ya | |
| cost_price | `cost_price` | `default_purchase_price` | |
| selling_price | `selling_price` | `default_sell_price` | |
| barcode_type | `barcode_symbology` | `barcode_type` (7 tipe) | UltimatePOS lebih lengkap |
| image | Ya | Ya | |
| description | `description` | `product_description` | |
| is_active / is_inactive | `is_active` | `is_inactive` | |
| enable_stock | Tidak | Ya | DreamsPOS selalu track stok |
| alert_quantity / quantity_alert | `quantity_alert` | `alert_quantity` | |
| warranty_id | Ya | Ya | |
| not_for_selling | Tidak | Ya | |
| weight | Tidak | Ya | Untuk shipping |
| expiry_period | Tidak | Ya | (days/months/years) |
| custom_fields | Tidak | 20 custom fields | |
| secondary_unit_id | Tidak | Ya | Dual unit system |
| sub_unit_ids | Tidak | Ya | JSON array |
| selling_type | Ya (`transactional/solution`) | Tidak | DreamsPOS exclusive |
| manufactured_date | Ya | Tidak | DreamsPOS exclusive |
| has_manufacturer | Ya | Tidak | DreamsPOS exclusive |
| has_expiry | Ya | Tidak | DreamsPOS exclusive |
| preparation_time_in_minutes | Tidak | Ya | Restaurant feature |

### 3.2 Tabel Variant/Attribute

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Struktur | 3 level: `variant_attributes` → `variant_values` → `product_variants` | 2 level: `product_variations` → `variations` |
| Nama atribut | Tabel `variant_attributes` | Kolom `name` di `product_variations` |
| Nilai atribut | Tabel `variant_values` | Kolom `name` di `variations` |
| SKU per variant | Ya (`product_variants.sku`) | Ya (`variations.sub_sku`) |
| Harga per variant | Ya (`cost_price`, `selling_price`) | Ya (`default_purchase_price`, `default_sell_price`) |
| Stok per variant | Ya (`product_variants.quantity`) | Via `product_locations` pivot |
| Profit percent | Tidak | Ya (`variations.profit_percent`) |
| Sell price incl tax | Tidak | Ya (`variations.sell_price_inc_tax`) |

**Analisa:** DreamsPOS punya struktur variant yang lebih bersih (3 tabel terpisah), UltimatePOS lebih fleksibel tapi lebih kompleks.

### 3.3 Product Images

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel terpisah | Ya (`product_images`) | Tidak (via `media` polymorphic) |
| Multiple images | Ya | Ya |
| Primary image flag | Ya (`is_primary`) | Tidak |

**Keunggulan DreamsPOS:** Memiliki tabel `product_images` dedicated yang lebih sederhana.

### 3.4 Product Stocks

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel stok per gudang | Ya (`product_stocks`) | Via `product_locations` pivot + transaction lines |
| Unique constraint | `product_id + product_variant_id + warehouse_id` | Tidak explicit |
| Stok di products | Ya (`products.quantity`) | Tidak (dihitung dari transaksi) |

**Keunggulan DreamsPOS:** Lebih straightforward dengan tabel `product_stocks` dedicated.

---

## 4. MODUL KONTAK (CUSTOMERS/SUPPLIERS)

| Kolom | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Struktur | 2 tabel: `customers`, `suppliers` | 1 tabel: `contacts` + `type` field |
| Type | Tabel terpisah | `customer` / `supplier` / `both` / `lead` |
| Code | Ya | `contact_id` (external reference) |
| Name | Ya | Ya |
| Email | Ya | Ya |
| Phone | Ya (`phone`) | Ya (`mobile`, `landline`, `alternate_number`) |
| Country | Ya | Ya |
| City | Tidak | Ya |
| State | Tidak | Ya |
| Address | Ya (`address`) | Ya (`permanent_address`, `current_address`, `shipping_address`) |
| Tax number | Ya | Ya |
| Image | Ya | Tidak (via `media` polymorphic) |
| Customer group | Tidak | Ya (`customer_groups` + discount) |
| Credit limit | Tidak | Ya |
| Pay term | Tidak | Ya (`pay_term_number`, `pay_term_type`) |
| Balance | Tidak | Ya (running balance) |
| Custom fields | Tidak | 4 custom fields |
| Supplier business name | Tidak | Ya |
| Landmark | Tidak | Ya |
| Position (job) | Tidak | Ya |
| Shipping address | Tidak | Ya |

**Gap DreamsPOS:**
- Belum punya `customer_groups` untuk tiered pricing
- Belum punya `credit_limit` dan `pay_term`
- Belum punya `balance` tracking
- Belum ada custom fields

---

## 5. MODUL TRANSAKSI

### 5.1 Arsitektur Transaksi

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Pendekatan | **Tabel terpisah**: `sales`, `purchases`, `purchase_orders`, `stock_transfers`, `stock_adjustments`, `expenses` | **Single table**: `transactions` + `type` enum |
| Line items | Tabel terpisah: `sale_items`, `purchase_items`, dll | Tabel terpisah: `transaction_sell_lines`, `purchase_lines`, `stock_adjustment_lines` |
| Payments | Dihitung dari `paid_amount` di header | Tabel `transaction_payments` dedicated |
| Retur | Tabel terpisah: `sale_returns`, `purchase_returns` | Via `sub_type` di `transactions` |

**Analisa:**
- DreamsPOS: Lebih mudah dipahami, tapi lebih banyak tabel
- UltimatePOS: Lebih fleksibel, tapi query lebih kompleks

### 5.2 Penjualan (Sales)

| Kolom | DreamsPOS (`sales`) | UltimatePOS (`transactions` type=sell) |
|-------|---------------------|----------------------------------------|
| reference_no | Ya (`reference_no`) | Ya (`ref_no`, `invoice_no`) |
| customer | `customer_id` FK | `contact_id` FK |
| store/location | `store_id` FK | `location_id` FK |
| cashier | `user_id` FK | `created_by` FK |
| date | `date` (date) | `transaction_date` (datetime) |
| status | `completed/pending/cancelled` | `received/pending/ordered/draft/final` |
| subtotal | `subtotal` | `total_before_tax` |
| tax_rate | `tax_rate` | Via `tax_id` FK |
| tax_amount | `tax_amount` | `tax_amount` |
| discount | `discount_rate`, `discount_amount` | `discount_type`, `discount_amount` |
| shipping | `shipping_fee` | `shipping_charges`, `shipping_details`, `shipping_address` |
| grand_total | `grand_total` | `final_total` |
| paid | `paid_amount` | Via `transaction_payments` |
| due | `due_amount` | Dihitung dari payments |
| payment_status | `paid/partial/unpaid` | `paid/due` |
| payment_method | `payment_method_id` FK | Via `transaction_payments.method` |
| coupon | `coupon_id` FK, `coupon_discount` | Tidak ada built-in |
| pos_status | `pos/hold/void` | Tidak ada |
| commission_agent | Tidak | Ya (`commission_agent` FK) |
| selling_price_group | Tidak | Ya (`selling_price_group_id`) |
| reward_point | Tidak | Ya (`reward_point_redeemed`) |
| recurring | Tidak | Ya (`is_recurring`, `recur_interval`) |
| invoice_token | Tidak | Ya (public invoice link) |
| round_off | Tidak | Ya (`round_off_amount`) |
| exchange_rate | Tidak | Ya (multi-currency) |
| custom_fields | Tidak | 4 custom fields |

### 5.3 Pembelian (Purchases)

| Kolom | DreamsPOS (`purchases`) | UltimatePOS (`transactions` type=purchase) |
|-------|-------------------------|-------------------------------------------|
| Tabel | `purchases` (terpisah) | `transactions` (type=purchase) |
| reference_no | Ya | Ya (`ref_no`) |
| supplier | `supplier_id` FK | `contact_id` FK |
| warehouse | `warehouse_id` FK | `location_id` FK |
| status | `received/ordered/pending` | `received/pending/ordered/draft` |
| payment_status | `paid/partial/unpaid` | `paid/due` |

### 5.4 Purchase Orders

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel dedicated | Ya (`purchase_orders`, `purchase_order_items`) | Via `transactions` (sub_type) |
| Expected date | Ya | Tidak |

### 5.5 Stock Transfer

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel dedicated | Ya (`stock_transfers`, `stock_transfer_items`) | Via `transactions` (type=stock_adjustment) |
| From/To warehouse | Ya | Tidak explicit |

### 5.6 Stock Adjustment

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel dedicated | Ya (`stock_adjustments`, `stock_adjustment_items`) | Via `transactions` (type=stock_adjustment) |
| Adjustment type | `addition/subtraction` | `normal/abnormal` |

---

## 6. MODUL PEMBAYARAN

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Payment methods | Tabel `payment_methods` | Enum di `transaction_payments.method` |
| Payment record | Dihitung di header (`paid_amount`) | Tabel `transaction_payments` dedicated |
| Card details | Tidak | Ya (card_number, card_type, expiry, etc.) |
| Cheque details | Tidak | Ya (`cheque_number`) |
| Bank transfer | Tidak | Ya (`bank_account_number`) |
| Payment link | Tidak | Ya (`payment_link`, `payment_link_expiry`) |

**Gap DreamsPOS:** Belum ada tabel `transaction_payments` dedicated untuk detail pembayaran per transaksi.

---

## 7. MODUL INVENTORI

### 7.1 Gudang/Lokasi

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Nama tabel | `stores` + `warehouses` | `business_locations` |
| Product-Location | Via `product_stocks` | Via `product_locations` pivot |
| Product rack | Tidak | Ya (`product_racks`) |

### 7.2 Stock Transfer

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel dedicated | Ya | Tidak (via transactions) |
| Status tracking | `draft/in_transit/received/cancelled` | Via transaction status |
| Items | `stock_transfer_items` | Via `stock_adjustment_lines` |

### 7.3 Stock Adjustment

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel dedicated | Ya | Via `transactions` |
| Status | `draft/approved/rejected` | Via transaction status |

**Keunggulan DreamsPOS:** Lebih mudah diakses dan dikelola karena tabel dedicated.

---

## 8. MODUL KEUANGAN

### 8.1 Expenses

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Tabel expenses | Ya | Via `transactions` (type=expense) |
| Expense categories | Ya (`expense_categories`) | Ya (`expense_categories`) |
| Hierarchical categories | Tidak | Ya (`parent_id`) |
| Paid via | Tidak | Ya (`paid_by` FK) |

### 8.2 Accounting

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Accounts table | Tidak | Ya (`accounts`, `account_types`) |
| Account transactions | Tidak | Ya (`account_transactions`) |
| Double entry | Tidak | Ya (debit/credit) |
| Cash register | Tidak | Ya (`cash_registers`, `cash_register_transactions`) |

**Gap Besar:** DreamsPOS belum punya modul accounting yang memadai untuk bisnis menengah.

### 8.3 Currencies

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Multi-currency | Tidak | Ya (`currencies` table) |
| Exchange rate | Tidak | Ya |
| Cash denominations | Tidak | Ya (`cash_denominations`) |

---

## 9. MODUL HARGA & DISKON

### 9.1 Price Groups

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Selling price groups | Tidak | Ya (`selling_price_groups`) |
| Customer groups | Tidak | Ya (`customer_groups` + discount) |
| Tiered pricing | Tidak | Ya |

### 9.2 Discounts

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Global discounts | Tidak | Ya (`discounts` table) |
| Discount by brand | Tidak | Ya |
| Discount by category | Tidak | Ya |
| Discount by location | Tidak | Ya |
| Discount priority | Tidak | Ya |
| Date range discount | Tidak | Ya (`starts_at`, `ends_at`) |
| Coupon system | Ya (`coupons`) | Tidak built-in |

**Analisa:** DreamsPOS punya sistem kupon, UltimatePOS punya sistem diskon global yang lebih fleksibel.

---

## 10. MODUL INVOICE

| Aspek | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Invoice settings | Ya (`invoice_settings`) | Via `invoice_layouts` |
| Invoice layouts | Tidak | Ya (`invoice_layouts`) |
| Invoice prefix | Ya | Via `invoice_schemes` |
| QR code | Tidak | Ya (`qr_code` di layout) |
| Letter head | Tidak | Ya (`letter_head`) |
| Invoice token/link | Tidak | Ya (`invoice_token` di transactions) |

---

## 11. FITUR KHUSUS

### 11.1 Fitur yang ADA di DreamsPOS tapi TIDAK di UltimatePOS

| Fitur | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| POS Hold/Void | Ya (`pos_status`) | Tidak |
| Sound effect setting | Ya (`pos_settings`) | Tidak |
| Product images table | Ya (`product_images`) | Via `media` polymorphic |
| Warranty boolean flags | `has_warranty`, `has_manufacturer`, `has_expiry` | Tidak |
| Manufactured date | Ya | Tidak |

### 11.2 Fitur yang TIDAK di DreamsPOS tapi ADA di UltimatePOS

| Fitur | DreamsPOS | UltimatePOS |
|-------|-----------|-------------|
| Multi-tenant (business_id) | Tidak | Ya |
| Spatie permissions | Tidak | Ya |
| Soft deletes | Tidak | Ya |
| Cash registers | Tidak | Ya |
| Accounting (double entry) | Tidak | Ya |
| Multi-currency | Tidak | Ya |
| Selling price groups | Tidak | Ya |
| Customer groups | Tidak | Ya |
| Global discounts | Tidak | Ya |
| Invoice layouts (multi) | Tidak | Ya |
| Recurring invoices | Tidak | Ya |
| Reward points | Tidak | Ya |
| Commission agents | Tidak | Ya |
| Restaurant module | Tidak | Ya |
| Manufacturing module | Tidak | Ya |
| Ecommerce module | Tidak | Ya |
| CRM module | Tidak | Ya |
| Custom fields (20 on products) | Tidak | Ya |
| Dual unit system | Tidak | Ya |
| Shipping details/address | Tidak | Ya |
| Product weight | Tidak | Ya |
| Notification templates | Tidak | Ya |
| Document & notes (polymorphic) | Tidak | Ya |
| Dashboard configurations | Tidak | Ya |
| Types of services | Tidak | Ya |
| Import/export batch | Tidak | Ya |
| Saudi/ZATCA integration | Tidak | Ya |
| Delivery tracking | Tidak | Ya (`delivery_date`, `delivery_person_id`) |

---

## 12. STATISTIK PERBANDINGAN

| Metrik | DreamsPOS | UltimatePOS |
|--------|-----------|-------------|
| Total tabel custom | 38 | ~50+ base tables |
| Total migrations | 1 | 100+ files |
| Core entity tables | 15 | ~25 base tables |
| Transaction tables | 12 (6 header + 6 items) | 5 (1 transactions + 4 line items) |
| Supporting tables | 11 | ~20 tables |
| Module tables | 0 | 21 modules |
| Custom fields support | Tidak | 20+ fields |
| Soft deletes | 0 tabel | 10+ tabel |
| Polymorphic relations | 0 | 3+ (media, notes, permissions) |

---

## 13. REKOMENDASI PENINGKATAN DreamsPOS

### Prioritas Tinggi (Critical)

1. **Tambahkan `business_id`** ke tabel utama untuk multi-tenant support
2. **Tambahkan Spatie permissions** untuk granular access control
3. **Tambahkan soft deletes** ke tabel master data (products, customers, suppliers, categories, brands, units)
4. **Tambahkan tabel `transaction_payments`** untuk detail pembayaran (card, cheque, bank transfer)
5. **Tambahkan `cash_registers`** untuk tracking kas fisik

### Prioritas Sedang (Important)

6. **Tambahkan `customer_groups`** untuk tiered pricing
7. **Tambahkan `selling_price_groups`** untuk harga berbeda per lokasi/kelompok
8. **Tambahkan `discounts` table** untuk diskon global (bukan hanya kupon)
9. **Tambahkan `accounts` & `account_transactions`** untuk basic accounting
10. **Tambahkan `currencies`** untuk multi-currency support
11. **Tambahkan `invoice_layouts`** untuk multiple invoice template
12. **Tambahkan custom fields** (minimal 4-5) di products dan contacts

### Prioritas Rendah (Nice to Have)

13. **Tambahkan `notification_templates`** untuk email/SMS notifikasi
14. **Tambahkan `document_and_notes`** polymorphic untuk notes di semua entity
15. **Tambahkan `media`** polymorphic untuk file attachments
16. **Tambahkan `types_of_services`** untuk service-based transactions
17. **Tambahkan `reward_points`** system
18. **Tambahkan `commission_agents`** untuk sales commission
19. **Tambahkan restaurant module** (table, waiter, kitchen order)
20. **Tambahkan `product_racks`** untuk lokasi fisik produk di gudang

---

## 14. KEKUATAN DreamsPOS vs UltimatePOS

Meskipun DreamsPOS memiliki banyak gap, DreamsPOS memiliki beberapa keunggulan:

1. **Sederhana & Mudah Dipahami** - Struktur tabel yang lebih straightforward
2. **Tabel Dedicated** - Stock transfer, stock adjustment, purchase orders punya tabel sendiri
3. **POS-specific features** - Hold order, void, sound effect
4. **Product images table** - Lebih mudah daripada polymorphic
5. **Coupon system** - Built-in coupon management
6. **Warranty flags** - Boolean flags untuk warranty, manufacturer, expiry
7. **Template-ready** - Lebih mudah di-custom untuk kebutuhan spesifik
8. **Cleaner variant structure** - 3 tabel terpisah lebih mudah dipahami

---

## 15. KESIMPULAN

DreamsPOS v2.0.6 adalah **template/starting point** yang baik untuk membangun aplikasi POS. Namun untuk menjadi POS system yang kompetitif seperti UltimatePOS, perlu penambahan signifikan terutama pada:

1. **Multi-tenancy & access control** (business_id + Spatie)
2. **Accounting & financial tracking** (accounts, cash registers)
3. **Advanced pricing** (price groups, customer groups, discounts)
4. **Payment detail tracking** (transaction_payments table)
5. **Soft deletes & data integrity**

UltimatePOS v6.8 adalah **reference yang sangat baik** untuk fitur-fitur yang perlu ditambahkan ke DreamsPOS. Strukturnya mature dan sudah teruji di production.

---

*Generated by opencode analysis — Juni 2026*
