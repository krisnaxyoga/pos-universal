# 📱 Fitur Barcode & Scanner - POS Application

Aplikasi POS sekarang sudah dilengkapi dengan fitur **generate barcode** dan **camera scanner** untuk setiap produk.

## ✨ Fitur yang Tersedia

### 🏷️ **Barcode Generation**
- **Generate barcode otomatis** untuk setiap produk baru
- **Format EAN-13** (13 digit) - standar internasional
- **Regenerate barcode** jika diperlukan
- **Print barcode** dalam format siap cetak (8 label per halaman)

### 📱 **Camera Scanner**
- **Scan barcode** menggunakan kamera perangkat
- **Real-time detection** dengan QuaggaJS library
- **Automatic product search** setelah barcode terdeteksi
- **Fallback manual input** jika kamera tidak tersedia

## 🚀 Cara Menggunakan

### **1. Generate Barcode Produk**

#### **Di Halaman Detail Produk:**
1. Buka **Products → Lihat** produk
2. Scroll ke bagian **"Barcode Produk"**
3. Klik tombol **"Generate Barcode"** (jika belum ada)
4. Barcode akan otomatis dibuat dengan format EAN-13

#### **Di Halaman Daftar Produk:**
1. Buka **Products** 
2. Di kolom **Aksi**, klik **"Generate"** untuk produk yang belum punya barcode
3. Atau klik **"Regen"** untuk regenerate barcode yang sudah ada

### **2. Print Barcode**

#### **Print dari Detail Produk:**
1. Di halaman detail produk, klik **"Print"**
2. Jendela baru akan terbuka dengan 8 label barcode
3. Gunakan **Ctrl+P** atau klik print otomatis
4. Pilih ukuran kertas **A4** untuk hasil terbaik

#### **Print dari Daftar Produk:**
1. Di daftar produk, klik **"Print"** di baris produk
2. Barcode akan langsung terbuka di tab baru siap print

### **3. Scan Barcode di POS**

#### **Menggunakan Camera Scanner:**
1. Buka halaman **POS - Point of Sale**
2. Klik tombol **kamera** 🎥 di sebelah search bar
3. **Izinkan akses kamera** jika diminta browser
4. **Arahkan kamera** ke barcode produk
5. Produk akan **otomatis ditambahkan** ke keranjang saat terdeteksi

#### **Input Manual Barcode:**
1. Di search bar POS, **ketik langsung** kode barcode
2. Produk akan muncul jika barcode valid
3. Klik produk untuk tambahkan ke keranjang

## 🔧 Spesifikasi Teknis

### **Format Barcode:**
- **Type:** EAN-13
- **Length:** 13 digit
- **Structure:** `20` + `12345` + `{5-digit-product-id}` + `{check-digit}`
- **Example:** `2012345000018`

### **Supported Browsers:**
- ✅ **Chrome/Chromium** (Recommended)
- ✅ **Firefox** 
- ✅ **Safari** (iOS 11+)
- ✅ **Edge**

### **Camera Requirements:**
- 📱 **Mobile:** Back camera (environment facing)
- 💻 **Desktop:** Webcam dengan resolusi minimum 320x240
- 🔒 **HTTPS Required:** untuk akses kamera di production

## 📋 Tips Penggunaan

### **Untuk Scanner:**
1. **Pencahayaan cukup** - pastikan barcode tidak terlalu gelap
2. **Jarak optimal** - 10-30cm dari barcode
3. **Pegang stabil** - hindari gerakan terlalu cepat
4. **Angle yang tepat** - barcode harus lurus, tidak miring

### **Untuk Printing:**
1. Gunakan **printer laser** untuk hasil terbaik
2. **Kertas A4 putih** standar 80gsm
3. Print dengan **actual size** (100%), jangan scale
4. **Test print** satu lembar sebelum print banyak

### **Troubleshooting:**
- **Kamera tidak muncul:** Check permission browser & HTTPS
- **Barcode tidak terdeteksi:** Coba manual input atau cek pencahayaan
- **Print tidak pas:** Pastikan browser zoom 100% dan printer setting actual size

## 🛡️ Keamanan & Performance

### **Security:**
- ✅ CSRF Protection untuk semua barcode operations
- ✅ Input validation untuk barcode format
- ✅ Safe file storage untuk barcode images

### **Performance:**
- ⚡ **Lazy loading** - scanner hanya aktif saat digunakan
- 🗜️ **Optimized images** - barcode SVG untuk file size minimal
- 💾 **Caching** - barcode images disimpan untuk print ulang

## 📚 API Endpoints

### **Generate/Regenerate Barcode:**
```http
POST /products/{id}/generate-barcode
POST /products/{id}/regenerate-barcode
```

### **Search by Barcode:**
```http
POST /pos/search-barcode
POST /api/products/search-barcode
```

### **Print Barcode:**
```http
GET /products/{id}/print-barcode
```

---

## 🎯 Workflow Lengkap

1. **Admin** membuat produk baru
2. **Barcode otomatis** di-generate saat produk dibuat
3. **Print barcode** dan tempel ke produk fisik  
4. **Kasir** scan barcode di POS untuk transaksi cepat
5. **Produk otomatis** masuk ke keranjang dengan harga dan stok yang benar

**Happy Scanning! 📱✨**