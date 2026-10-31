# 📦 PartnerStock - Sistem Manajemen Distribusi & Konsinyasi B2B
### Platform Distribusi Stok, Peramalan Kebutuhan (WMA), dan CRM Kemitraan Warung

**PartnerStock** adalah platform web manajemen rantai pasok dan distribusi titip jual (konsinyasi) produk berbasis **Laravel 12** dan **PostgreSQL 18**. Sistem ini dirancang untuk mendigitalkan seluruh siklus kemitraan distributor dengan warung-warung mitra, mulai dari pemantauan stok fisik, peramalan restock otomatis (*Weighted Moving Average*), manajemen pesanan tambahan warung, hingga pencegahan churn mitra berbasis aturan CRM.

---

## 📋 Fitur Utama & Aturan Bisnis Sistem

1. **Siklus Distribusi Konsinyasi**:
   - Pengiriman dan pengecekan fisik dilakukan secara berkala ke warung-warung mitra.
   - Pencatatan transaksi memisahkan antara **stok lama ditarik** (retur/display) dan **titip baru**, sehingga data penjualan aktual ($Terjual = Titip Sebelumnya - Sisa Fisik$) tercatat akurat dan transparan.
2. **Peramalan Stok Sistem (Weighted Moving Average - WMA 3 Periode)**:
   - Menghitung rekomendasi kuota titip baru berdasarkan tren penjualan riil 3 periode terakhir:
     $$F = (0.20 \times X_1) + (0.30 \times X_2) + (0.50 \times X_3)$$
   - Menggunakan pembulatan ke atas (*ceiling*) dengan batasan kuota minimal penaruhan (10 bungkus).
3. **Pusat CRM & Churn Prevention**:
   - **Data Mining**: Mengklasifikasikan pola dan tren performa warung mitra (meningkat, stabil, atau menurun).
   - **Churn Prevention**: Otomatis mendeteksi dan memberi peringatan jika penjualan warung menurun 3 periode berturut-turut atau tidak ada restock lebih dari 28 hari (> 2 siklus).
   - **Follow-up Logging**: Form pencatatan riwayat komunikasi/tindak lanjut distributor ke pemilik warung.
4. **Portal Mandiri Mitra Warung**:
   - Warung mitra dapat masuk ke portal tanpa akun rumit, cukup dengan memasukkan **PIN Mitra** unik.
   - Fitur konfirmasi sisa stok mandiri dan pengajuan permintaan tambahan stok (*restock request*) kapan saja secara online.

---

## 💻 Modul Utama Distributor (PartnerStock)

1. **Dashboard Ringkas & Eksekutif**:
   - 4 Kartu KPI Utama: *Total Mitra Warung Aktif*, *Distribusi Periode Ini*, *Permintaan Tambahan Pending*, dan *Total Seluruh Produk Terjual*.
   - Grafik interaktif Chart.js menampilkan perbandingan tren produk terjual, sisa stok, dan saran WMA.
2. **Mitra Warung**:
   - Tampilan kartu (*card grid*) modern dan kontras tinggi.
   - Informasi pemilik, alamat, akses langsung ke nomor WhatsApp pemilik hanya dengan sekali klik simbol WA resmi.
   - Badge PIN Mitra Warung yang aman dan jelas untuk memudahkan pemberian akses ke mitra.
3. **Distribusi Stok**:
   - Ringkasan rekomendasi stok sistem, permintaan tambahan warung, dan total stok yang harus dibawa distributor hari ini.
   - Tabel riwayat sesi kunjungan konsinyasi (titip sebelumnya, sisa fisik, terjual, kuota titip baru, dan status rekomendasi).
   - Formulir pencatatan kunjungan fisik cepat dan cetak nota sesi konsinyasi resmi.
4. **Permintaan Tambahan**:
   - Daftar pesanan restock yang diajukan langsung oleh warung mitra via portal.
   - Kolom status interaktif (*Pending*, *Diproses*, *Selesai*) untuk pemenuhan pesanan secara cepat.
5. **Laporan & Rekapitulasi**:
   - Rekapitulasi menyeluruh performa distribusi, penjualan aktual, penarikan stok lama, dan total pendapatan distributor.
   - Filter tanggal fleksibel dan tampilan ramah cetak (*print-friendly*).
6. **Portal Mitra Warung**:
   - Tampilan mobile-friendly untuk warung mengecek stok tercatat, riwayat titip jual, serta mengajukan restock.

---

## 🗄️ Struktur Database (PostgreSQL 18)

Sistem menggunakan tabel terintegrasi yang saling terhubung melalui Eloquent ORM:

1. `users`: Autentikasi multi-role (`supplier` distributor dan `warung`), status akun aktif.
2. `suppliers`: Profil usaha distributor, kontak, dan alamat pusat operasional.
3. `warungs`: Data warung mitra, nama pemilik, kontak WA, alamat, PIN login unik, interval pengiriman, harga titip, harga eceran, dan soft deletes.
4. `products`: Master data produk konsinyasi (kode produk, satuan, harga kesepakatan).
5. `stok_penjualans`: Rekam transaksi konsinyasi per sesi kunjungan (titip sebelumnya, sisa fisik, jumlah terjual, titip baru, penarikan stok).
6. `pengajuan_stoks`: Pengajuan permintaan stok tambahan mandiri dari warung mitra.
7. `stock_confirmations`: Catatan konfirmasi sisa stok pra-kunjungan.
8. `sales_records`: Rekapitulasi data historis penjualan untuk perhitungan algoritma WMA.
9. `restocks`: Catatan riwayat pengiriman stok baru dan penarikan stok lama.
10. `crm_alerts`: Peringatan otomatis sistem terkait retensi mitra (*churn prevention*, *reorder*, tren).
11. `crm_follow_ups`: Catatan log tindak lanjut komunikasi distributor ke warung.
12. `system_settings`: Konfigurasi bobot WMA, mode konsinyasi, dan parameter operasional.

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
- PHP 8.2 atau lebih tinggi (pastikan ekstensi `pdo_pgsql` aktif)
- Composer
- Node.js & NPM
- PostgreSQL 16 / 17 / 18

### 2. Clone Repository
```bash
git clone https://github.com/Ainun27/PartnerStock.git
cd PartnerStock
```

### 3. Install Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Lingkungan (.env)
Salin file konfigurasi contoh:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi PostgreSQL pada file `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=konsinyasi_pisang
DB_USERNAME=postgres
DB_PASSWORD=password_postgres_anda
```

Pastikan database sudah dibuat di PostgreSQL:
```sql
CREATE DATABASE konsinyasi_pisang;
```

### 5. Migrasi & Seeding Data Awal
Jalankan migrasi seluruh tabel beserta data simulasi konsinyasi:
```bash
php artisan migrate:fresh --seed
```

### 6. Kompilasi Frontend Assets
```bash
npm run build
```

### 7. Jalankan Server Aplikasi
```bash
php artisan serve
```
Akses aplikasi melalui peramban di: **`http://localhost:8000`**

---

## 🔑 Kredensial Akun Pengujian

### A. Akun Distributor / Supplier (Admin)
- **URL Login**: `http://localhost:8000/login`
- **Email**: `admin@keripikpisang.com`
- **Password**: `admin123`

### B. Portal Warung Mitra
- **URL Portal**: `http://localhost:8000/portal`
- **Daftar PIN Warung Demo**:
  - **Warung Berkah Bu Siti**: PIN `1234`
  - **Warung Madura Cak Rozaq**: PIN `4567`
  - **Warung Pojok Mbak Rina**: PIN `3456`
  - **Toko Barokah Pak Joko**: PIN `2345`
  - **Kios Snack Sejahtera Mas Danang**: PIN `5678`
  - **Warung Barokah Rejeki Bu Yayuk**: PIN `6789`

---

## 🧪 Validasi Pengujian Otomatis

Seluruh logika bisnis, algoritma WMA, aturan CRM, otentikasi role, alur konsinyasi, dan workflow portal warung telah diuji melalui automated test suite Laravel:

```bash
php artisan test
```

**Hasil Pengujian:**
- **Status**: **24 Passed (122 Assertions), 0 Failed**
- Meliputi Unit Test & Feature Test komprehensif.

---

## 📄 Lisensi
Dikembangkan untuk keperluan sistem distribusi dan kemitraan konsinyasi **PartnerStock**.
