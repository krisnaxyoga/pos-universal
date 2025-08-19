# 👤 User Manual - POS Application

Panduan lengkap penggunaan aplikasi POS dengan Online Store dan integrasi iPaymu.

## 📋 Daftar Isi

- [Pengenalan](#pengenalan)
- [Login dan Role](#login-dan-role)
- [Dashboard Admin](#dashboard-admin)
- [Manajemen Produk](#manajemen-produk)
- [POS (Point of Sale)](#pos-point-of-sale)
- [Online Store](#online-store)
- [Transaksi iPaymu](#transaksi-ipaymu)
- [Laporan](#laporan)
- [Pengaturan](#pengaturan)
- [FAQ](#faq)

## 🚀 Pengenalan

Aplikasi POS ini adalah sistem terintegrasi yang menggabungkan:
- **POS System** - Untuk transaksi offline di toko
- **Online Store** - E-commerce untuk penjualan online
- **iPaymu Integration** - Payment gateway untuk pembayaran online
- **Management System** - Manajemen produk, customer, dan laporan

## 🔐 Login dan Role

### Akses Aplikasi
- **URL Admin**: `http://localhost:8000/login`
- **URL Store**: `http://localhost:8000/store`

### Default User Accounts
| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Admin | admin@example.com | password | Semua fitur |
| Supervisor | supervisor@example.com | password | Manajemen data |
| Kasir | kasir@example.com | password | POS dan transaksi |

### Hak Akses Role

#### 👑 Admin
- Semua akses
- Manajemen user
- Pengaturan sistem
- Laporan lengkap

#### 👨‍💼 Supervisor  
- Manajemen produk dan kategori
- Manajemen customer
- Laporan penjualan
- Transaksi

#### 👨‍💻 Kasir
- POS interface
- Lihat transaksi
- Tidak bisa edit data master

## 📊 Dashboard Admin

### Halaman Utama
Akses: `/dashboard`

**Fitur:**
- **Statistik Penjualan** - Total penjualan hari ini
- **Transaksi Terbaru** - 10 transaksi terakhir
- **Produk Stok Menipis** - Alert produk habis
- **Grafik Penjualan** - Tren penjualan bulanan

**Navigasi Menu:**
- Dashboard
- POS
- Transaksi
- iPaymu
- Produk & Kategori (Admin/Supervisor)
- Customer (Admin/Supervisor)
- Laporan (Admin/Supervisor)
- User Management (Admin)
- Pengaturan (Admin)

## 📦 Manajemen Produk

### Kategori Produk
Akses: `/categories`

**Membuat Kategori:**
1. Klik tombol "Tambah Kategori"
2. Isi nama kategori
3. Pilih status aktif/nonaktif
4. Klik "Simpan"

**Edit Kategori:**
1. Klik ikon edit pada kategori
2. Ubah data yang diperlukan
3. Klik "Update"

### Produk
Akses: `/products`

**Menambah Produk:**
1. Klik "Tambah Produk"
2. Isi form:
   - **Nama Produk** (wajib)
   - **SKU** (unik)
   - **Kategori** (pilih dari dropdown)
   - **Harga** (format: 10000)
   - **Stok** (jumlah)
   - **Deskripsi** (opsional)
   - **Gambar** (upload JPG/PNG)
3. Pilih status aktif
4. Klik "Simpan"

**Fitur Produk:**
- **Search** - Cari produk by nama/SKU
- **Filter Kategori** - Filter by kategori
- **Bulk Actions** - Edit multiple produk
- **Export/Import** - Excel/CSV
- **Gambar** - Upload dan preview

**Edit Stok:**
1. Klik ikon edit pada produk
2. Ubah jumlah stok
3. Simpan perubahan

## 💰 POS (Point of Sale)

### Interface POS
Akses: `/pos`

**Layout POS:**
- **Kiri**: Daftar produk dengan search
- **Kanan**: Keranjang belanja
- **Bawah**: Total dan tombol checkout

### Proses Transaksi

#### 1. Pilih Produk
- **Search Produk**: Ketik nama/SKU di search box
- **Scan Barcode**: Gunakan scanner (jika tersedia)
- **Browse Kategori**: Pilih kategori untuk filter
- **Klik Produk**: Klik produk untuk tambah ke keranjang

#### 2. Edit Keranjang
- **Ubah Qty**: Klik tombol +/- atau edit langsung
- **Hapus Item**: Klik tombol hapus (X)
- **Clear All**: Hapus semua item

#### 3. Customer (Opsional)
- **Pilih Customer**: Dropdown customer existing
- **Guest**: Biarkan kosong untuk guest

#### 4. Pembayaran
- **Cash**: Masukkan jumlah bayar cash
- **Change**: Otomatis hitung kembalian

#### 5. Selesaikan Transaksi
- **Process**: Klik tombol "Process Payment"
- **Print Receipt**: Print struk otomatis
- **New Transaction**: Mulai transaksi baru

### Draft Transaction

**Simpan Draft:**
1. Isi keranjang dengan produk
2. Klik "Save Draft"
3. Beri nama draft
4. Draft tersimpan untuk nanti

**Load Draft:**
1. Klik "Load Draft"
2. Pilih draft yang tersimpan
3. Keranjang terisi sesuai draft

## 🛒 Online Store

### Customer Interface
URL: `/store`

**Fitur Store:**
- **Homepage** - Produk featured dan kategori
- **Search** - Cari produk
- **Filter Kategori** - Browse by kategori
- **Product Detail** - Detail produk dengan gambar
- **Shopping Cart** - Keranjang belanja
- **Checkout** - Form pemesanan

### Proses Order Online

#### 1. Browse Produk
- Kunjungi `/store`
- Browse kategori atau search
- Klik produk untuk detail

#### 2. Add to Cart
- Di halaman product detail
- Pilih quantity
- Klik "Add to Cart"
- Produk masuk keranjang

#### 3. View Cart
- Klik icon cart di header
- Review items di keranjang
- Update quantity atau hapus item

#### 4. Checkout
- Klik "Checkout" di cart
- Isi form customer:
  - **Nama Lengkap**
  - **Email**
  - **No. Telepon** 
  - **Alamat Pengiriman**

#### 5. Payment
- Review order summary
- Klik "Pay with iPaymu"
- Redirect ke halaman iPaymu
- Pilih metode pembayaran
- Selesaikan pembayaran

#### 6. Konfirmasi
- Setelah pembayaran sukses
- Redirect ke halaman success
- Email konfirmasi (jika dikonfigurasi)

## 💳 Transaksi iPaymu

### Dashboard iPaymu
Akses: `/ipaymu/transactions`

**Fitur Dashboard:**
- **Statistik**: Total, completed, pending, failed
- **Revenue**: Total revenue dan fees
- **Payment Methods**: Breakdown by metode
- **Filter**: Status, metode, tanggal
- **Search**: By transaction number/customer

### Detail Transaksi
**Informasi yang ditampilkan:**
- **Transaction Summary**: ID, amount, status
- **iPaymu Details**: Transaction ID, session, method
- **Customer Info**: Data pembeli
- **Payment Timeline**: Riwayat status
- **Items**: Produk yang dibeli

### Status Transaksi

| Status | Deskripsi | Action |
|--------|-----------|--------|
| **Pending** | Menunggu pembayaran | Monitor |
| **Processing** | Pembayaran diproses | Tunggu callback |
| **Completed** | Pembayaran berhasil | Siap kirim |
| **Failed** | Pembayaran gagal | Follow up customer |

### Monitoring Pembayaran

**Real-time Updates:**
- Status update via iPaymu callback
- Automatic stock reduction saat sukses
- Email notification (jika dikonfigurasi)

**Manual Check:**
1. Buka detail transaksi
2. Lihat status terkini
3. Check payment timeline
4. Verifikasi di dashboard iPaymu

## 📊 Laporan

### Sales Report
Akses: `/reports/sales`

**Filter Options:**
- **Date Range**: Dari - sampai tanggal
- **Payment Method**: Cash, online, dll
- **Status**: Completed, pending, dll
- **User**: By kasir/admin

**Data Report:**
- Total penjualan
- Jumlah transaksi
- Average transaction value
- Top selling products
- Sales by hour/day/month

### Product Report
Akses: `/reports/products`

**Metrics:**
- **Best Sellers**: Produk terlaris
- **Stock Status**: Stok menipis
- **Category Performance**: Performa by kategori
- **Revenue by Product**: Revenue per produk

### Export Reports
- **PDF**: Download report as PDF
- **Excel**: Export ke Excel untuk analisis
- **Print**: Print report langsung

## ⚙️ Pengaturan

### App Settings
Akses: `/settings` (Admin only)

**Konfigurasi:**
- **App Name**: Nama aplikasi
- **Company Info**: Data perusahaan
- **Logo**: Upload logo perusahaan
- **Contact**: Email, phone, address

### iPaymu Settings
**Environment Variables (.env):**
```env
IPAYMU_VA=your_va_number
IPAYMU_SECRET_KEY=your_secret_key
IPAYMU_ENVIRONMENT=sandbox
```

**Callback URL:**
Set di dashboard iPaymu: `https://yourdomain.com/api/payment/callback`

### User Management
Akses: `/users` (Admin only)

**Fitur:**
- **Add User**: Tambah user baru
- **Edit Role**: Ubah role user
- **Activate/Deactivate**: Toggle status user
- **Reset Password**: Reset password user

## ❓ FAQ

### Transaksi

**Q: Bagaimana jika customer tidak menyelesaikan pembayaran?**
A: Transaksi akan berstatus "pending" dan expired sesuai setting iPaymu. Stock tidak dikurangi sampai payment sukses.

**Q: Apakah bisa refund?**
A: Refund harus diproses manual melalui dashboard iPaymu atau contact customer service iPaymu.

**Q: Bagaimana track status pembayaran?**
A: Lihat di `/ipaymu/transactions` untuk status real-time semua pembayaran online.

### Produk

**Q: Bagaimana upload gambar produk?**
A: Di form produk, pilih file gambar (JPG/PNG max 2MB). Gambar akan otomatis di-resize.

**Q: Apakah bisa import produk bulk?**
A: Ya, gunakan fitur import di halaman produk dengan template Excel yang disediakan.

**Q: Bagaimana set produk tidak aktif?**
A: Edit produk dan ubah status ke "Tidak Aktif". Produk tidak akan muncul di store dan POS.

### Sistem

**Q: Bagaimana backup data?**
A: Database SQLite ada di `database/database.sqlite`. Copy file ini untuk backup.

**Q: Bagaimana ganti logo dan nama app?**
A: Akses menu Settings (admin) untuk upload logo dan ubah nama aplikasi.

**Q: Apakah support multi-currency?**
A: Saat ini hanya support Rupiah (IDR) sesuai iPaymu.

### Error

**Q: Error "Storage link not found"?**
A: Jalankan `php artisan storage:link` di terminal.

**Q: Callback iPaymu tidak working?**
A: Pastikan callback URL benar dan server bisa diakses dari internet. Untuk development gunakan ngrok.

**Q: Dashboard tidak bisa diakses?**
A: Pastikan sudah login dan role user sesuai dengan halaman yang diakses.

## 📞 Support

**Untuk bantuan teknis:**
- Check dokumentasi lengkap di README.md
- Lihat log error di `storage/logs/laravel.log`
- Create issue di GitHub repository
- Contact administrator sistem

**Untuk bantuan iPaymu:**
- Dashboard iPaymu: https://my.ipaymu.com
- Customer Service iPaymu
- Dokumentasi API iPaymu

---

**Manual Version: 1.0**  
*Last updated: August 2025*  
**Happy selling! 🛍️**