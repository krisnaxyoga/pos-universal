# Setup Instruksi - Aplikasi POS Laravel 12

## Yang Sudah Dikerjakan

### 1. ✅ Project Laravel 12 Baru
- Project Laravel 12 sudah dibuat dengan nama `pos-app`
- Menggunakan PHP 8.2 dan database SQLite

### 2. ✅ Environment Setup
- File `.env` sudah dikonfigurasi
- App name: "POS System"
- Database: SQLite (database/database.sqlite)

### 3. ✅ Dependencies Terinstall
- **Laravel Breeze**: untuk authentication
- **Maatwebsite Excel**: untuk import/export Excel
- **mike42/escpos-php**: untuk thermal printer
- **dompdf/dompdf**: untuk generate PDF
- **TailwindCSS**: untuk styling (sudah termasuk di Breeze)

### 4. ✅ Database Schema Lengkap

#### Tabel yang Sudah Dibuat:
- **users**: User management dengan role (admin, kasir, supervisor)
- **categories**: Kategori produk
- **products**: Master produk dengan stok management
- **transactions**: Header transaksi penjualan
- **transaction_items**: Detail item transaksi

#### Fitur Database:
- Foreign key constraints
- Automatic transaction number generation
- Stock tracking
- Role-based access control

### 5. ✅ Models dengan Relationships
- **User**: hasMany(Transaction)
- **Category**: hasMany(Product)
- **Product**: belongsTo(Category), hasMany(TransactionItem)
- **Transaction**: belongsTo(User), hasMany(TransactionItem)
- **TransactionItem**: belongsTo(Transaction, Product)

### 6. ✅ Sample Data (Seeders)
- 3 user default (admin, kasir, supervisor)
- 5 kategori produk
- 7 produk sample dengan berbagai kategori
- Password default untuk semua user: **password**

### 7. ✅ Authentication & Authorization
- Laravel Breeze terinstall dengan Blade templates
- Role middleware untuk authorization
- User roles: admin, kasir, supervisor

## Cara Menjalankan Aplikasi

### 1. Pastikan Requirements Terpenuhi
```bash
# Pastikan PHP 8.2+ dan Composer terinstall
php --version
composer --version
```

### 2. Install Dependencies
```bash
cd pos-app
composer install
npm install
```

### 3. Setup Database
```bash
# Database sudah di-migrate dan seed, tapi jika perlu ulang:
php artisan migrate:fresh --seed
```

### 4. Build Assets
```bash
npm run build
```

### 5. Jalankan Server
```bash
php artisan serve
```

Aplikasi akan berjalan di: http://localhost:8000

## Login Credentials Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pos.com | password |
| Kasir | kasir1@pos.com | password |
| Supervisor | supervisor@pos.com | password |

## Struktur Aplikasi

```
pos-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controllers untuk setiap fitur
│   │   └── Middleware/      # RoleMiddleware untuk authorization
│   └── Models/              # User, Product, Category, Transaction, TransactionItem
├── database/
│   ├── migrations/          # Schema database
│   └── seeders/            # Data awal
├── resources/
│   └── views/              # Blade templates (Breeze + custom views)
└── routes/
    └── web.php             # Route definitions
```

## Next Steps (Belum Dikerjakan)

1. **Controllers & Routes** - Untuk CRUD operations
2. **Views** - Interface untuk manajemen produk, transaksi, laporan
3. **POS Interface** - Tampilan kasir untuk transaksi
4. **Reports** - Dashboard dan laporan penjualan
5. **Excel Import/Export** - Fitur import/export produk
6. **Printer Integration** - Cetak struk thermal
7. **API Endpoints** - Untuk integrasi external (opsional)

## Database Schema Overview

### Products Table
- ID, Name, Description, SKU, Barcode
- Price, Cost, Stock, Min_Stock
- Category relationship
- Image upload support

### Transactions Table  
- Auto-generated transaction number
- User (kasir) relationship
- Subtotal, Discount, Tax, Total
- Payment method & status
- Timestamps untuk reporting

### Features Ready
- ✅ User role management
- ✅ Product categorization  
- ✅ Stock tracking
- ✅ Transaction recording
- ✅ Basic authentication
- ✅ Database relationships

Aplikasi siap untuk development lanjutan dengan fondasi yang kuat!