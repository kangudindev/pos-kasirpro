# Analisa DreamsPOS v2.0.6 - Blade Files & Database Schema

## 1. Blade Files Terkait POS yang Dianalisa (35+ file)

### Core POS
| File | Fungsi |
|------|--------|
| `pos.blade.php` | Halaman utama transaksi POS (kategori produk, cart, pembayaran) |
| `pos-settings.blade.php` | Pengaturan printer, payment method, sound effect |

### Produk
| File | Fungsi |
|------|--------|
| `add-product.blade.php` | Form tambah produk (single & variable) |
| `edit-product.blade.php` | Form edit produk |
| `product-list.blade.php` | Daftar semua produk |
| `product-details.blade.php` | Detail produk |
| `expired-produks.blade.php` | Produk expired |
| `varriant-attributes.blade.php` | Atribut variant (Color, Size, dll) |
| `barcode.blade.php` | Cetak barcode produk |

### Sales & Purchase
| File | Fungsi |
|------|--------|
| `sales-list.blade.php` | Daftar penjualan |
| `sales-dashboard.blade.php` | Dashboard penjualan |
| `sales-report.blade.php` | Laporan penjualan |
| `sales-returns.blade.php` | Retur penjualan |
| `purchase-list.blade.php` | Daftar pembelian |
| `purchase-report.blade.php` | Laporan pembelian |
| `purchase-order-report.blade.php` | Laporan order pembelian |
| `purchase-returns.blade.php` | Retur pembelian |

### Kontak
| File | Fungsi |
|------|--------|
| `customers.blade.php` | Daftar pelanggan |
| `customer-report.blade.php` | Laporan pelanggan |
| `suppliers.blade.php` | Daftar supplier |
| `supplier-report.blade.php` | Laporan supplier |

### Inventori
| File | Fungsi |
|------|--------|
| `warehouse.blade.php` | Daftar gudang |
| `store-list.blade.php` | Daftar toko |
| `stock-transfer.blade.php` | Transfer stok antar gudang |
| `stock-adjustment.blade.php` | Koreksi stok |
| `manage-stocks.blade.php` | Kelola stok |
| `low-stocks.blade.php` | Stok menipis |

### Keuangan & Pengaturan
| File | Fungsi |
|------|--------|
| `expense-list.blade.php` | Daftar pengeluaran |
| `expense-category.blade.php` | Kategori pengeluaran |
| `expense-report.blade.php` | Laporan pengeluaran |
| `income-report.blade.php` | Laporan pendapatan |
| `coupons.blade.php` | Daftar kupon diskon |
| `tax-rates.blade.php` | Tarif pajak |
| `tax-reports.blade.php` | Laporan pajak |
| `units.blade.php` | Satuan barang |
| `brand-list.blade.php` | Daftar merek |
| `category-list.blade.php` | Daftar kategori |
| `warranty.blade.php` | Daftar garansi |
| `invoice-settings.blade.php` | Pengaturan invoice |
| `invoice-report.blade.php` | Laporan invoice |
| `inventory-report.blade.php` | Laporan inventori |

---

## 2. Data Structures dari Blade Files

### POS Page (`pos.blade.php`)
- **Categories**: Kategori produk dengan icon, nama, jumlah item
- **Products**: Nama produk, harga, stok (Pcs), gambar, kategori
- **Order List**: Transaction ID, customer, qty per item, harga
- **Payment Info**: Sub Total, Tax (GST), Shipping, Discount, Grand Total
- **Payment Method**: Cash, Debit Card, Scan (QR)
- **Actions**: Hold Order, Void, Payment

### Add Product (`add-product.blade.php`)
- **Product Information**: Store, Warehouse, Product Name, Slug, SKU, Category, Sub Category, Sub Sub Category, Brand, Unit, Selling Type, Barcode Symbology, Item Code, Description
- **Pricing & Stocks**: Product Type (Single/Variable), Quantity, Price, Tax Type (Exclusive/Sales Tax), Discount Type, Discount Value, Quantity Alert
- **Product Variants**: Variant Attribute (Color, Size), Variant Value (Red, Black, XL), SKU per variant, Quantity per variant, Price per variant
- **Custom Fields**: Warranties, Manufacturer, Expiry, Manufactured Date, Expiry On
- **Images**: Multiple product images

### Customers (`customers.blade.php`)
- Customer Name, Code, Email, Phone, Country, Avatar

### Suppliers (`suppliers.blade.php`)
- Supplier Name, Code, Email, Phone, Country, Avatar

### Sales List (`sales-list.blade.php`)
- Customer Name, Reference, Date, Status, Grand Total, Paid, Due, Payment Status, Biller

### Purchase List (`purchase-list.blade.php`)
- Supplier Name, Reference, Date, Status, Grand Total, Paid, Due, Created by

### Expense List (`expense-list.blade.php`)
- Category Name, Reference, Date, Status, Amount, Description

### Stock Transfer (`stock-transfer.blade.php`)
- Shop (Warehouse From/To), Product, Reference No, Date, Responsible Person, Notes, Quantity

### Stock Adjustment (`stock-adjustment.blade.php`)
- Warehouse, Product, Reference No, Date, Responsible Person, Notes, Quantity, Adjustment Type

### Coupons (`coupons.blade.php`)
- Shop, Product, Reference No, Date, Responsible Person, Notes, Quantity

### Tax Rates (`tax-rates.blade.php`)
- Name (VAT, GST), Tax Rate %, Created On

### Units (`units.blade.php`)
- Unit Name, Short Name, No of Products, Created On, Status

### Brands (`brand-list.blade.php`)
- Brand Name, Slug, Status

### Categories (`category-list.blade.php`)
- Category Name, Slug, Status

### Warranty (`warranty.blade.php`)
- Name, Description, Duration, Status

### Store List (`store-list.blade.php`)
- Store Name, User Name, Phone, Email, Status

### Warehouse (`warehouse.blade.php`)
- Warehouse Name, User Name, Phone, Email, Status

### Invoice Settings (`invoice-settings.blade.php`)
- Invoice Logo, Invoice Prefix, Invoice Due Days, Footer Note

### POS Settings (`pos-settings.blade.php`)
- POS Printer (A4), Payment Method (COD, Cheque, Card, Paypal, Bank Transfer, Cash), Enable Sound Effect

---

## 3. Database Schema

### Tabel: `roles`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama role (admin, staff, manager) |
| description | text, nullable | Deskripsi role |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `users` (extend default Laravel)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| role_id | bigint FK | FK ke roles, nullable |
| name | varchar | Nama user |
| email | varchar | Email user |
| phone | varchar, nullable | Nomor telepon |
| avatar | varchar, nullable | Path foto profil |
| is_active | boolean | Status aktif |
| password | varchar | Password hash |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `stores`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama toko |
| user_id | bigint FK | FK ke users (pemilik/manajer), nullable |
| phone | varchar, nullable | Nomor telepon toko |
| email | varchar, nullable | Email toko |
| address | text, nullable | Alamat toko |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `warehouses`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama gudang |
| phone | varchar, nullable | Nomor telepon |
| email | varchar, nullable | Email |
| address | text, nullable | Alamat gudang |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `categories`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama kategori |
| parent_id | bigint FK | FK ke categories sendiri (hierarchical), nullable |
| slug | varchar, nullable | Slug URL |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `brands`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama brand |
| slug | varchar, nullable | Slug URL |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `units`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama satuan (Kilogram, Piece) |
| short_name | varchar | Singkatan (kg, pc) |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `warranties`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama garansi (3 Months, 1 Year) |
| description | text, nullable | Deskripsi garansi |
| duration_months | integer | Durasi dalam bulan |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `products`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama produk |
| slug | varchar, nullable | Slug URL |
| sku | varchar, nullable, unique | SKU produk |
| barcode_symbology | varchar, nullable | Tipe barcode (Code34, Code35, Code36) |
| item_code | varchar, nullable | Kode item |
| description | text, nullable | Deskripsi produk |
| category_id | bigint FK | FK ke categories, nullable |
| brand_id | bigint FK | FK ke brands, nullable |
| unit_id | bigint FK | FK ke units, nullable |
| warranty_id | bigint FK | FK ke warranties, nullable |
| type | enum('single','variable') | Tipe produk |
| selling_type | enum('transactional','solution') | Tipe penjualan |
| cost_price | decimal(15,2) | Harga beli/modal |
| selling_price | decimal(15,2) | Harga jual |
| tax_rate | decimal(5,2) | Tarif pajak |
| tax_type | enum('exclusive','inclusive') | Tipe pajak |
| discount_rate | decimal(5,2) | Tarif diskon |
| discount_type | enum('percentage','cash') | Tipe diskon |
| quantity | integer | Jumlah stok |
| quantity_alert | integer | Threshold stok minimum |
| manufactured_date | date, nullable | Tanggal produksi |
| expiry_date | date, nullable | Tanggal kedaluwarsa |
| has_warranty | boolean | Punya garansi |
| has_manufacturer | boolean | Punya info produsen |
| has_expiry | boolean | Punya tanggal expired |
| image | varchar, nullable | Gambar utama produk |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `product_images`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| product_id | bigint FK | FK ke products, cascade delete |
| image_path | varchar | Path gambar |
| is_primary | boolean | Gambar utama |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `variant_attributes`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama atribut (Color, Size, Material) |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `variant_values`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| variant_attribute_id | bigint FK | FK ke variant_attributes, cascade delete |
| value | varchar | Nilai (Red, Black, XL, M) |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `product_variants`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| product_id | bigint FK | FK ke products, cascade delete |
| variant_value_id | bigint FK | FK ke variant_values, cascade delete |
| sku | varchar, nullable | SKU variant |
| cost_price | decimal(15,2), nullable | Harga beli variant |
| selling_price | decimal(15,2), nullable | Harga jual variant |
| quantity | integer | Stok variant |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `product_stocks`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| product_id | bigint FK | FK ke products, cascade delete |
| product_variant_id | bigint FK, nullable | FK ke product_variants, cascade delete |
| warehouse_id | bigint FK | FK ke warehouses, cascade delete |
| quantity | integer | Jumlah stok di gudang ini |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |
| | | Unique constraint: product_id + product_variant_id + warehouse_id |

### Tabel: `customers`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama pelanggan |
| code | varchar, nullable | Kode pelanggan (201, 202, dll) |
| email | varchar, nullable | Email pelanggan |
| phone | varchar, nullable | Nomor telepon |
| country | varchar, nullable | Negara |
| address | text, nullable | Alamat |
| tax_number | varchar, nullable | NPWP/Nomor pajak |
| image | varchar, nullable | Foto profil |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `suppliers`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama supplier |
| code | varchar, nullable | Kode supplier |
| email | varchar, nullable | Email supplier |
| phone | varchar, nullable | Nomor telepon |
| country | varchar, nullable | Negara |
| address | text, nullable | Alamat |
| tax_number | varchar, nullable | NPWP/Nomor pajak |
| image | varchar, nullable | Foto/logo |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `tax_rates`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama pajak (VAT, GST 5%, dll) |
| rate | decimal(5,2) | Persentase pajak |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `payment_methods`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama metode (Cash, Card, COD, Cheque, Paypal, Bank Transfer, Debit Card, Scan) |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `sales`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi (SL0101, dll) |
| customer_id | bigint FK | FK ke customers, nullable (Walk-in Customer) |
| store_id | bigint FK | FK ke stores, nullable |
| user_id | bigint FK | FK ke users (kasir/biller), nullable |
| date | date | Tanggal transaksi |
| status | enum('completed','pending','cancelled') | Status transaksi |
| subtotal | decimal(15,2) | Subtotal sebelum pajak |
| tax_rate | decimal(5,2) | Tarif pajak |
| tax_amount | decimal(15,2) | Jumlah pajak |
| discount_rate | decimal(5,2) | Tarif diskon |
| discount_amount | decimal(15,2) | Jumlah diskon |
| shipping_fee | decimal(15,2) | Biaya pengiriman |
| grand_total | decimal(15,2) | Total akhir |
| paid_amount | decimal(15,2) | Jumlah dibayar |
| due_amount | decimal(15,2) | Jumlah belum dibayar |
| payment_status | enum('paid','partial','unpaid') | Status pembayaran |
| payment_method_id | bigint FK | FK ke payment_methods, nullable |
| coupon_id | bigint FK | FK ke coupons, nullable |
| coupon_discount | decimal(15,2) | Diskon kupon |
| pos_status | enum('pos','hold','void') | Status POS |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

**Index:** date, customer_id, status

### Tabel: `sale_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| sale_id | bigint FK | FK ke sales, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants, nullable |
| sku | varchar, nullable | SKU produk (snapshot) |
| name | varchar | Nama produk (snapshot) |
| quantity | integer | Jumlah item |
| unit_price | decimal(15,2) | Harga satuan |
| cost_price | decimal(15,2) | Modal satuan |
| tax_rate | decimal(5,2) | Tarif pajak |
| tax_amount | decimal(15,2) | Jumlah pajak |
| discount_rate | decimal(5,2) | Tarif diskon |
| discount_amount | decimal(15,2) | Jumlah diskon |
| total | decimal(15,2) | Total per item |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `sale_returns`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi retur |
| sale_id | bigint FK | FK ke sales |
| customer_id | bigint FK | FK ke customers, nullable |
| store_id | bigint FK | FK ke stores, nullable |
| user_id | bigint FK | FK ke users, nullable |
| date | date | Tanggal retur |
| subtotal | decimal(15,2) | Subtotal retur |
| tax_amount | decimal(15,2) | Pajak retur |
| discount_amount | decimal(15,2) | Diskon retur |
| grand_total | decimal(15,2) | Total retur |
| status | enum('completed','pending') | Status retur |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `sale_return_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| sale_return_id | bigint FK | FK ke sale_returns, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants |
| sku | varchar, nullable | SKU produk |
| name | varchar | Nama produk |
| quantity | integer | Jumlah retur |
| unit_price | decimal(15,2) | Harga satuan |
| total | decimal(15,2) | Total per item |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `purchases`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi (PT001, dll) |
| supplier_id | bigint FK | FK ke suppliers, nullable |
| store_id | bigint FK | FK ke stores, nullable |
| warehouse_id | bigint FK | FK ke warehouses, nullable |
| user_id | bigint FK | FK ke users, nullable |
| date | date | Tanggal pembelian |
| status | enum('received','ordered','pending') | Status pembelian |
| subtotal | decimal(15,2) | Subtotal |
| tax_rate | decimal(5,2) | Tarif pajak |
| tax_amount | decimal(15,2) | Jumlah pajak |
| discount_amount | decimal(15,2) | Jumlah diskon |
| shipping_fee | decimal(15,2) | Biaya pengiriman |
| grand_total | decimal(15,2) | Total akhir |
| paid_amount | decimal(15,2) | Jumlah dibayar |
| due_amount | decimal(15,2) | Jumlah belum dibayar |
| payment_status | enum('paid','partial','unpaid') | Status pembayaran |
| payment_method_id | bigint FK | FK ke payment_methods, nullable |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

**Index:** date, supplier_id

### Tabel: `purchase_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| purchase_id | bigint FK | FK ke purchases, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants |
| sku | varchar, nullable | SKU produk |
| name | varchar | Nama produk (snapshot) |
| quantity | integer | Jumlah item |
| unit_cost | decimal(15,2) | Harga satuan (modal) |
| tax_rate | decimal(5,2) | Tarif pajak |
| tax_amount | decimal(15,2) | Jumlah pajak |
| discount_amount | decimal(15,2) | Jumlah diskon |
| total | decimal(15,2) | Total per item |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `purchase_returns`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi retur |
| purchase_id | bigint FK | FK ke purchases |
| supplier_id | bigint FK | FK ke suppliers, nullable |
| store_id | bigint FK | FK ke stores, nullable |
| user_id | bigint FK | FK ke users, nullable |
| date | date | Tanggal retur |
| subtotal | decimal(15,2) | Subtotal retur |
| tax_amount | decimal(15,2) | Pajak retur |
| grand_total | decimal(15,2) | Total retur |
| status | enum('completed','pending') | Status retur |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `purchase_return_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| purchase_return_id | bigint FK | FK ke purchase_returns, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants |
| sku | varchar, nullable | SKU produk |
| name | varchar | Nama produk |
| quantity | integer | Jumlah retur |
| unit_cost | decimal(15,2) | Harga satuan |
| total | decimal(15,2) | Total per item |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `purchase_orders`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi order |
| supplier_id | bigint FK | FK ke suppliers, nullable |
| store_id | bigint FK | FK ke stores, nullable |
| user_id | bigint FK | FK ke users, nullable |
| date | date | Tanggal order |
| expected_date | date, nullable | Tanggal perkiraaan datang |
| status | enum('draft','ordered','received','cancelled') | Status order |
| grand_total | decimal(15,2) | Total akhir |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `purchase_order_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| purchase_order_id | bigint FK | FK ke purchase_orders, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants |
| quantity | integer | Jumlah item |
| unit_cost | decimal(15,2) | Harga satuan |
| total | decimal(15,2) | Total per item |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `stock_transfers`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi transfer |
| from_warehouse_id | bigint FK | FK ke warehouses (asal) |
| to_warehouse_id | bigint FK | FK ke warehouses (tujuan) |
| user_id | bigint FK | FK ke users (penanggung jawab), nullable |
| date | date | Tanggal transfer |
| status | enum('draft','in_transit','received','cancelled') | Status transfer |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `stock_transfer_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| stock_transfer_id | bigint FK | FK ke stock_transfers, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants |
| quantity | integer | Jumlah yang ditransfer |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `stock_adjustments`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| reference_no | varchar, unique | Nomor referensi koreksi |
| warehouse_id | bigint FK | FK ke warehouses |
| user_id | bigint FK | FK ke users, nullable |
| date | date | Tanggal koreksi |
| status | enum('draft','approved','rejected') | Status koreksi |
| adjustment_type | enum('addition','subtraction') | Tipe koreksi |
| notes | text, nullable | Catatan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `stock_adjustment_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| stock_adjustment_id | bigint FK | FK ke stock_adjustments, cascade delete |
| product_id | bigint FK | FK ke products, nullable |
| product_variant_id | bigint FK, nullable | FK ke product_variants |
| quantity | integer | Jumlah koreksi |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `expense_categories`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | varchar | Nama kategori (Employee Benefits, Foods & Snacks) |
| description | text, nullable | Deskripsi |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `expenses`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| expense_category_id | bigint FK | FK ke expense_categories, nullable |
| reference_no | varchar, nullable | Nomor referensi |
| store_id | bigint FK | FK ke stores, nullable |
| user_id | bigint FK | FK ke users, nullable |
| date | date | Tanggal pengeluaran |
| amount | decimal(15,2) | Jumlah pengeluaran |
| status | enum('active','inactive') | Status |
| description | text, nullable | Deskripsi |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

**Index:** date

### Tabel: `coupons`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| code | varchar, unique | Kode kupon |
| type | enum('percentage','fixed') | Tipe diskon |
| value | decimal(15,2) | Nilai diskon |
| minimum_amount | decimal(15,2) | Minimum pembelian |
| usage_limit | integer, nullable | Batas pemakaian |
| used_count | integer | Sudah dipakai berapa kali |
| start_date | date, nullable | Tanggal mulai berlaku |
| end_date | date, nullable | Tanggal berakhir |
| is_active | boolean | Status aktif |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `pos_settings`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| printer_type | varchar | Tipe printer (A4, dll) |
| enable_sound_effect | boolean | Aktifkan efek suara |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `pos_settings_payment_methods`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| pos_settings_id | bigint FK | FK ke pos_settings, cascade delete |
| payment_method_id | bigint FK | FK ke payment_methods, cascade delete |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

### Tabel: `invoice_settings`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| logo | varchar, nullable | Path logo invoice |
| prefix | varchar | Prefix invoice (default: "INV -") |
| due_days | integer | Jatuh tempo dalam hari (default: 5) |
| footer_note | text, nullable | Catatan footer invoice |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

---

## 4. Entity Relationship Summary

```
users ──┬── roles
        ├── stores ──┬── sales
        │            ├── purchases
        │            ├── expenses
        │            ├── purchase_orders
        │            └── purchase_returns
        │
warehouses ──┬── product_stocks
             ├── purchases
             ├── stock_transfers (from_warehouse_id, to_warehouse_id)
             ├── stock_adjustments
             └── purchase_orders

categories (hierarchical: parent_id)
brands
units
warranties

products ──┬── product_images
           ├── product_variants ──┬── variant_values ── variant_attributes
           │                      └── product_stocks
           ├── product_stocks
           ├── sale_items
           ├── sale_return_items
           ├── purchase_items
           ├── purchase_return_items
           ├── purchase_order_items
           ├── stock_transfer_items
           └── stock_adjustment_items

customers ──┬── sales
            └── sale_returns

suppliers ──┬── purchases
            ├── purchase_returns
            └── purchase_orders

payment_methods ──┬── sales
                  └── purchases

tax_rates
expense_categories ── expenses
coupons ── sales
pos_settings ── pos_settings_payment_methods ── payment_methods
invoice_settings
```

---

## 5. Total Tabel: 28 Tabel

| # | Nama Tabel | Estimasi Relasi |
|---|-----------|-----------------|
| 1 | roles | - |
| 2 | users | FK ke roles |
| 3 | stores | FK ke users |
| 4 | warehouses | - |
| 5 | categories | FK ke categories (self-referencing) |
| 6 | brands | - |
| 7 | units | - |
| 8 | warranties | - |
| 9 | products | FK ke categories, brands, units, warranties |
| 10 | product_images | FK ke products |
| 11 | variant_attributes | - |
| 12 | variant_values | FK ke variant_attributes |
| 13 | product_variants | FK ke products, variant_values |
| 14 | product_stocks | FK ke products, product_variants, warehouses |
| 15 | customers | - |
| 16 | suppliers | - |
| 17 | tax_rates | - |
| 18 | payment_methods | - |
| 19 | sales | FK ke customers, stores, users, payment_methods, coupons |
| 20 | sale_items | FK ke sales, products, product_variants |
| 21 | sale_returns | FK ke sales, customers, stores, users |
| 22 | sale_return_items | FK ke sale_returns, products, product_variants |
| 23 | purchases | FK ke suppliers, stores, warehouses, users, payment_methods |
| 24 | purchase_items | FK ke purchases, products, product_variants |
| 25 | purchase_returns | FK ke purchases, suppliers, stores, users |
| 26 | purchase_return_items | FK ke purchase_returns, products, product_variants |
| 27 | purchase_orders | FK ke suppliers, stores, users |
| 28 | purchase_order_items | FK ke purchase_orders, products, product_variants |
| 29 | stock_transfers | FK ke warehouses, users |
| 30 | stock_transfer_items | FK ke stock_transfers, products, product_variants |
| 31 | stock_adjustments | FK ke warehouses, users |
| 32 | stock_adjustment_items | FK ke stock_adjustments, products, product_variants |
| 33 | expense_categories | - |
| 34 | expenses | FK ke expense_categories, stores, users |
| 35 | coupons | - |
| 36 | pos_settings | - |
| 37 | pos_settings_payment_methods | FK ke pos_settings, payment_methods |
| 38 | invoice_settings | - |
