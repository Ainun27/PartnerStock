# Aplikasi Titip Jual Produk Berbasis Web
### Analisis Penjualan, Rekomendasi Stok (WMA), dan Aturan CRM Konsinyasi

Aplikasi web manajemen titip jual (konsinyasi) produk berbasis **Laravel 12** dan **PostgreSQL 18** yang dibangun sesuai spesifikasi sistem untuk mengelola rantai pasok dan distribusi usaha **Pak Aceng** ke warung-warung mitra.

---

## 📋 Studi Kasus & Aturan Bisnis

1. **Produk Tunggal**: Pak Aceng hanya memiliki satu produk, yaitu **Keripik Pisang Original** (`KRP-ORI`). Tidak ada varian rasa lain.
2. **Siklus Distribusi Rutin**: Pengiriman dilakukan rutin setiap **dua minggu (14 hari)**, namun interval ini dapat dikonfigurasi melalui menu Pengaturan.
3. **Pemeriksaan Fisik Non-Realtime**: Stok tidak dipantau IoT real-time. Konfirmasi stok dilakukan H-2 via WhatsApp, lalu diverifikasi fisik saat kunjungan rutin.
4. **Pencatatan Terpisah**: Produk lama yang belum terjual ditarik dicatat terpisah sebagai **Penarikan Stok Lama**, bukan produk terjual, dan tidak mengurangi pencatatan produk baru yang dititipkan.
5. **Peramalan Statistik WMA**: Menggunakan metode **Weighted Moving Average (WMA)** 3 periode ($F = 0.20 X_1 + 0.30 X_2 + 0.50 X_3$, pembulatan ke atas / `ceil`).
6. **Mode Penanganan Stok Konsinyasi**:
   - **Mode A (Stok lama ditarik & diganti baru)**: Rekomendasi stok baru murni mengikuti perkiraan WMA ($R = F$).
   - **Mode B (Stok lama tetap dijual)**: Rekomendasi tambahan stok adalah selisih ($R = \max(0, F - S)$).
7. **Aturan CRM Wajib**:
   - **Data Mining**: Ekstraksi pola penjualan 3 periode dan klasifikasi tren (meningkat, menurun, stabil).
   - **Churn Prevention**: Peringatan otomatis jika penjualan turun 3 periode berturut-turut ATAU tidak ada restock dalam 2 siklus rutin (> 28 hari). Dilengkapi deduplikasi alert dan pencatatan riwayat tindak lanjut (`crm_follow_ups`).
   - **Reorder Recommendation**: Menghitung total persiapan muatan kendaraan supplier dan mengelola pengajuan stok tambahan (PO) dari warung mitra.

---

## 🗄️ Struktur Database (PostgreSQL 18)

Sistem menggunakan 11 tabel utama terintegrasi dengan Eloquent ORM:

1. `users`: Autentikasi multi-role (`supplier` dan `warung`), status aktif, timestamps.
2. `suppliers`: Profil usaha Pak Aceng (`Keripik Pisang Original Pak Aceng`), kontak, alamat.
3. `warungs`: Data warung mitra, nama pemilik, kontak, alamat, interval pengiriman (default 14 hari), status kemitraan (`aktif`, `nonaktif`, `ditinjau`), soft deletes.
4. `products`: Satu produk Keripik Pisang Original (kode `KRP-ORI`, harga titip Rp 12.000, harga jual eceran Rp 15.000).
5. `stock_confirmations`: Catatan konfirmasi sisa stok via WhatsApp H-2 sebelum kunjungan fisik rutin.
6. `sales_records`: Rekam penjualan aktual per periode hasil verifikasi fisik kunjungan konsinyasi.
7. `restocks`: Catatan distribusi produk baru dan penarikan stok lama (tercatat terpisah).
8. `stock_requests`: Pengajuan tambahan stok (PO) dari warung mitra di luar siklus rutin.
9. `stock_recommendations`: Hasil peramalan WMA, rekomendasi stok Mode A / Mode B, dan jumlah persetujuan supplier.
10. `crm_alerts`: Peringatan CRM (`data_mining`, `churn_prevention`, `reorder_recommendation`) dengan status penanganan.
11. `crm_follow_ups`: Catatan log tindak lanjut supplier (metode kontak: WhatsApp/telepon/kunjungan, hasil komunikasi, rencana tindak lanjut).
12. `system_settings`: Konfigurasi bobot WMA (`wma_weights`), mode konsinyasi default (`consignment_mode`), interval pengiriman, dan kuota awal warung baru.

---

## 💻 11 Modul Navigasi Supplier (Pak Aceng)

1. **Dashboard**: Ringkasan metrik (Warung Aktif, Distribusi Berjalan, Penjualan Berjalan, Produk Ditarik, Persiapan Pengiriman WMA), banner peringatan Churn Prevention & konfirmasi stok H-2, grafik penjualan Chart.js, serta tabel rekomendasi distribusi.
2. **Data Warung**: Manajemen data warung mitra (CRUD), filter status, nomor kontak, kode unik, dan soft delete.
3. **Data Produk**: Konfigurasi produk tunggal Keripik Pisang Original (harga titip, harga eceran warung).
4. **Distribusi & Penarikan**: Form input kunjungan fisik dua mingguan dengan pencatatan terpisah untuk sisa fisik, stok lama ditarik, dan titip baru. Dilengkapi nota/bukti sesi konsinyasi siap cetak.
5. **Konfirmasi Stok**: Modul input konfirmasi stok WhatsApp H-2 dengan status perbandingan saat cek fisik (*Sesuai* vs *Ada Selisih*).
6. **Analisis Penjualan**: Halaman rincian WMA 3 periode per warung, transparansi rumus, status kecukupan data, dan visualisasi grafik peramalan Chart.js.
7. **Rekomendasi Stok**: Rencana muatan pengiriman pra-kunjungan, toggle switch Mode A vs Mode B, tombol penyesuaian persetujuan muatan, dan total muatan motor/mobil.
8. **Pusat CRM**: 3 tab aturan CRM (Churn Prevention + form modal catat tindak lanjut, Data Mining pola tren, Reorder Recommendation).
9. **Permintaan Tambahan**: Pengelolaan pesanan PO dari warung (setujui, ubah kuota, tolak, penuhi).
10. **Laporan**: Rekapitulasi menyeluruh dengan filter rentang tanggal dan warung, tabel penjualan aktual, tabel distribusi & penarikan terpisah, serta tampilan cetak (*Print Friendly*).
11. **Pengaturan**: Konfigurasi dinamis bobot WMA, mode konsinyasi default, siklus pengiriman (14 hari), dan kuota awal mitra baru.

---

## 🏪 Modul Portal Warung Mitra

1. **Akses Cepat & Aman**: Login menggunakan PIN warung atau tautan slug unik (`/portal/WRG-BERKAH`).
2. **Dashboard Warung**: Sisa stok tercatat di toples, tanggal kunjungan terakhir, dan kontak pemasok.
3. **Informasi Produk**: Detail Keripik Pisang Original (deskripsi dan harga kesepakatan).
4. **Pengajuan Tambahan Stok**: Formulir permintaan restock di luar jadwal rutin beserta alasan.
5. **Histori Pengajuan**: Pelacakan status pesanan (*Menunggu*, *Disetujui*, *Ditolak*, *Dipenuhi*).

---

## 🚀 Panduan Konfigurasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
- PHP 8.2 atau lebih tinggi (ekstensi `pdo_pgsql` aktif)
- Composer
- Node.js & NPM
- PostgreSQL 16 / 17 / 18

### 2. Konfigurasi Database PostgreSQL
Pastikan database PostgreSQL telah dibuat:
```sql
CREATE DATABASE konsinyasi_pisang;
```

Konfigurasi file `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=konsinyasi_pisang
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Migrasi & Seeding Data Demo
Jalankan migrasi seluruh 11 tabel beserta data simulasi konsinyasi:
```bash
php artisan migrate:fresh --seed
```

### 4. Build Frontend Assets
```bash
npm run build
```

### 5. Jalankan Server Lokal
```bash
php artisan serve
```
Buka peramban di: `http://localhost:8000`

---

## 🔑 Akun Demo Pengujian

### A. Akun Supplier / Pemilik Usaha (Pak Aceng)
- **URL Login**: `http://localhost:8000/login`
- **Email**: `admin@keripikpisang.com`
- **Password**: `admin123`

### B. Akun Warung Mitra Demo
Akses melalui portal: `http://localhost:8000/portal`
1. **Warung Berkah Bu Siti** (Tren Meningkat 12 $\rightarrow$ 16 $\rightarrow$ 22 pcs):
   - Slug: `/portal/WRG-BERKAH` | PIN: `1234`
2. **Warung Madura Cak Rozaq** (Tren Menurun 3 Periode 22 $\rightarrow$ 16 $\rightarrow$ 10 pcs $\rightarrow$ Pemicu Churn):
   - Slug: `/portal/WRG-MADURA` | PIN: `4567`
3. **Warung Pojok Mbak Rina** (Data Belum Cukup 2 Periode & Belum Konfirmasi WA H-2):
   - Slug: `/portal/WRG-POJOK` | PIN: `3456`
4. **Toko Barokah Pak Joko** (Penjualan Stabil & Permintaan Tambahan Stok PO):
   - Slug: `/portal/WRG-BAROKAH` | PIN: `2345`
5. **Kios Snack Sejahtera Mas Danang** (Dorman > 28 hari tanpa restock $\rightarrow$ Pemicu Churn):
   - Slug: `/portal/WRG-SEJAHTERA` | PIN: `5678`
6. **Warung Barokah Rejeki Bu Yayuk** (Mitra Baru, 0 riwayat penjualan $\rightarrow$ Kuota Awal 20 pcs):
   - Slug: `/portal/WRG-YAYUK` | PIN: `6789`

---

## 🧪 Hasil Pengujian Fungsional (Section 14)

Semua skenario pengujian fungsional telah divalidasi dan diuji secara otomatis melalui PHPUnit test suites (`tests/Feature/KonsinyasiTest.php` dan `tests/Feature/CrmAndWmaComprehensiveTest.php`):

| Skenario | Hasil | Keterangan |
|---|---|---|
| 1. Login Supplier & Warung | ✅ Berhasil | Role authorization & session portal berfungsi |
| 2. Pembatasan Hak Akses | ✅ Berhasil | Middleware auth & proteksi role aktif |
| 3. CRUD Warung & Produk | ✅ Berhasil | Soft deletes warung & aturan produk tunggal aktif |
| 4. Pencatatan Distribusi & Penarikan | ✅ Berhasil | Transaksi DB memisahkan retur dan titip baru |
| 5. Validasi Stok & Penjualan | ✅ Berhasil | Validasi non-negatif & sinkronisasi saldo |
| 6. WMA Angka Spesifikasi (10, 15, 20) | ✅ Berhasil | $F = 16.5 \rightarrow 17$ bungkus (`ceil`) |
| 7. WMA Data Kurang dari 3 Periode | ✅ Berhasil | Status `belum_cukup` & bobot dinormalisasi |
| 8. Penanganan Stok Belum Konfirmasi | ✅ Berhasil | Status `Perlu Konfirmasi Stok` muncul |
| 9. Perbedaan Mode A dan Mode B | ✅ Berhasil | Mode A ($R = F$) vs Mode B ($R = F - S$) teruji |
| 10. Total Persiapan Muatan | ✅ Berhasil | Menghitung akumulasi seluruh rekomendasi disetujui |
| 11. Churn Penjualan Turun 3 Periode | ✅ Berhasil | Alert dibuat otomatis saat $X_1 > X_2 > X_3$ |
| 12. Churn Tanpa Restock 2 Siklus | ✅ Berhasil | Alert dibuat saat tidak ada restock > 28 hari |
| 13. Pencegahan Alert Duplikat | ✅ Berhasil | Deduplikasi mencegah peringatan ganda |
| 14. Pencatatan Tindak Lanjut CRM | ✅ Berhasil | Log `crm_follow_ups` tersimpan & status terupdate |
| 15. Permintaan Stok Tambahan & Approval | ✅ Berhasil | Siklus pengajuan warung hingga pemenuhan aktif |
| 16. Penyaringan Laporan | ✅ Berhasil | Filter rentang tanggal & warung berfungsi |

**Hasil Eksekusi Test**: **23 Passed (111 Assertions), 0 Failed**.
