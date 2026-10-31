# 📊 Dokumentasi Diagram UML Lengkap - Sistem PartnerStock
### Use Case Diagram, Activity Diagrams, & Class Diagram Berbasis PlantUML

Dokumen ini memuat diagram pemodelan berorientasi objek (*Object-Oriented Analysis and Design*) menggunakan sintaks **PlantUML** standar untuk seluruh alur bisnis, aktor, modul pekerjaan, dan arsitektur data pada platform **PartnerStock (Sistem Distribusi & Konsinyasi B2B)**.

---

## 📑 Daftar Isi
1. [Use Case Diagram (Ringkas & Standar)](#1-use-case-diagram-ringkas--standar)
2. [Activity Diagrams (Alur Kerja Seluruh Proses Bisnis)](#2-activity-diagrams-seluruh-alur-pekerjaan)
   - [Activity 1: Siklus Kunjungan Konsinyasi Fisik & Pencatatan Nota](#activity-1-siklus-distribusi-konsinyasi-fisik--pencatatan-nota)
   - [Activity 2: Konfirmasi Stok Pra-Kunjungan (H-2) & Verifikasi Selisih](#activity-2-konfirmasi-stok-pra-kunjungan-h-2--verifikasi-selisih)
   - [Activity 3: Peramalan Stok Sistem (WMA 3 Periode) & Penyesuaian Muatan](#activity-3-peramalan-stok-sistem-wma-3-periode--penyesuaian-muatan)
   - [Activity 4: Evaluasi Otomatis CRM & Tindak Lanjut Churn Prevention](#activity-4-evaluasi-otomatis-crm--tindak-lanjut-churn-prevention)
   - [Activity 5: Portal Mandiri Warung & Pengajuan Restock (PO)](#activity-5-portal-mandiri-mitra-warung--pengajuan-restock-po)
   - [Activity 6: Rekapitulasi Laporan & Kalkulasi Finansial](#activity-6-rekapitulasi-laporan--kalkulasi-finansial)
3. [Class Diagram Ringkas & Terpadu (5 Entitas Inti)](#3-class-diagram-ringkas--terpadu-5-entitas-inti-bisnis)
4. [Entity Relationship Diagram (ERD Inti Sistem - 5 Tabel Utama)](#4-entity-relationship-diagram-erd-inti-sistem---5-tabel-utama)
5. [Petunjuk Render PlantUML](#5-petunjuk-render-plantuml)

---

## 1. Use Case Diagram (Ringkas & Standar)

Diagram Use Case ini memodelkan kebutuhan fungsional sistem pada tingkat tinggi (*high-level*) yang berfokus pada aksi kerja utama (**Mengelola / Memantau / Mengajukan**) dengan **2 Aktor Pengguna**:
1. **Supplier**: Mengelola seluruh aktivitas operasional kemitraan, master data, distribusi fisik, peramalan restock, CRM, dan rekapitulasi.
2. **Mitra Warung**: Mengakses portal mandiri untuk memantau stok toples konsinyasi, melakukan konfirmasi, dan mengajukan pesanan restock (PO).

```plantuml
@startuml usecase_partnerstock
!theme plain
skinparam backgroundColor #FFFFFF
skinparam handwritten false
skinparam shadowing false
skinparam packageStyle rectangle
skinparam actorStyle awesome

skinparam sequence {
    ArrowColor #0F172A
    ActorBorderColor #0F172A
}

skinparam usecase {
    BackgroundColor #F8FAFC
    BorderColor #0284C7
    FontName "Plus Jakarta Sans", Arial, sans-serif
    FontSize 11
    BorderThickness 1.2
}

left to right direction

actor "Supplier" as Supplier #EA580C
actor "Mitra Warung" as Mitra #10B981

rectangle "Sistem Informasi PartnerStock" {

    ' Paket Autentikasi
    usecase "Melakukan Autentikasi\n(Login & Logout)" as UC_Auth
    usecase "Mengakses Portal Mandiri\n(Autentikasi PIN)" as UC_PortalAuth

    ' Paket Pekerjaan Supplier
    usecase "Memantau Dashboard &\nStatistik Penjualan" as UC_Dashboard
    usecase "Mengelola Data Mitra Warung" as UC_Warung
    usecase "Mengelola Master Produk" as UC_Produk
    usecase "Mengelola Distribusi\nStok Konsinyasi" as UC_Distribusi
    usecase "Mencetak Nota Konsinyasi" as UC_Nota
    usecase "Mengelola Konfirmasi Stok\nPra-Kunjungan (H-2)" as UC_Konfirmasi
    usecase "Mengelola Analisis Penjualan &\nRekomendasi WMA" as UC_WMA
    usecase "Mengelola Aturan CRM &\nChurn Prevention" as UC_CRM
    usecase "Mengelola Permintaan\nTambahan Stok (PO)" as UC_PO_Admin
    usecase "Mengelola Laporan &\nRekapitulasi Finansial" as UC_Laporan
    usecase "Mencetak Laporan" as UC_CetakLaporan

    ' Paket Pekerjaan Mitra Warung
    usecase "Melihat Informasi Sisa Stok &\nRiwayat Konsinyasi" as UC_CekStok
    usecase "Melakukan Konfirmasi\nSisa Stok Mandiri" as UC_KonfirmasiMitra
    usecase "Mengajukan Permintaan\nTambahan Stok (PO)" as UC_PO_Mitra
}

' Hubungan Aktor Supplier
Supplier --> UC_Auth
Supplier --> UC_Dashboard
Supplier --> UC_Warung
Supplier --> UC_Produk
Supplier --> UC_Distribusi
Supplier --> UC_Konfirmasi
Supplier --> UC_WMA
Supplier --> UC_CRM
Supplier --> UC_PO_Admin
Supplier --> UC_Laporan

' Hubungan Aktor Mitra Warung
Mitra --> UC_PortalAuth
Mitra --> UC_CekStok
Mitra --> UC_KonfirmasiMitra
Mitra --> UC_PO_Mitra

' Relasi Include
UC_Distribusi ..> UC_Nota : <<include>>
UC_Laporan ..> UC_CetakLaporan : <<include>>

@enduml
```

---

## 2. Activity Diagrams (Seluruh Alur Pekerjaan)

### Activity 1: Siklus Distribusi Konsinyasi Fisik & Pencatatan Nota
Diagram ini memodelkan pekerjaan rutin kunjungan supplier ke warung mitra (verifikasi toples, kalkulasi terjual, penarikan stok lama, dan cetak bukti nota transaksi).

```plantuml
@startuml activity_distribusi_konsinyasi
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false
skinparam activity {
    BackgroundColor #F8FAFC
    BorderColor #0284C7
    FontName "Plus Jakarta Sans", Arial, sans-serif
    FontSize 11
}

|Supplier|
start
:Kunjungi lokasi warung mitra sesuai jadwal rutin 14 hari;
:Buka menu "Distribusi Stok" -> Klik "+ Catat Distribusi";
:Pilih Mitra Warung tujuan;

|Sistem PartnerStock|
:Muat data titip sebelumnya ($Taro), harga titip, dan riwayat terakhir;

|Supplier|
:Lakukan cek fisik toples keripik pisang di warung;
:Hitung jumlah toples/bungkus yang masih tersisa ($Sisa_Fisik);
:Input angka "Sisa Fisik" pada formulir;

|Sistem PartnerStock|
:Hitung otomatis produk terjual:
$Terjual = \max(0, Taro - Sisa_Fisik)$;
:Hitung persentase terjual:
$Persen = (Terjual / Taro) \times 100\%$;
:Evaluasi aturan Periodic Review Inventory Model:
- Jika Sisa == 0 -> Habis Total (Stockout) -> Kuota Naik + Safety Stock 10
- Jika Persen >= 80% atau Sisa <= 5 -> Laris Manis -> Kuota Naik + Safety Stock 8
- Jika Persen >= 50% -> Stabil -> Pertahankan Kuota Normal
- Jika Persen < 50% -> Lambat -> Evaluasi & Kurangi Kuota;
:Tampilkan status rekomendasi & estimasi kuota titip baru (minimal 10 pcs);

|Supplier|
if (Apakah ada stok lama berumur mendekati batas display?) then (Ya, tarik stok lama)
    :Tarik produk lama dari etalase warung;
    :Input jumlah penarikan pada kolom "Stok Lama Ditarik";
else (Tidak, produk masih segar)
    :Biarkan stok lama di toples;
endif

:Tentukan dan input jumlah "Titip Baru" yang diserahkan ke pemilik warung;
:Ketik catatan kunjungan (jika ada);
:Klik "Simpan Sesi Konsinyasi";

|Sistem PartnerStock|
:Validasi input data transaksi (angka non-negatif);
start
partition "Database Transaction" {
    :Simpan record transaksi ke tabel `stok_penjualans`;
    :Simpan record pengiriman baru ke tabel `restocks`;
    :Simpan rekam penjualan aktual ke tabel `sales_records`;
    :Perbarui sisa stok aktif di tabel `warungs` ($stok_saat_ini = Sisa_Fisik + Titip_Baru - Ditarik$);
}
:Buat data nota sesi konsinyasi resmi;
:Tampilkan halaman Nota Sesi Konsinyasi;

|Supplier|
:Klik "Cetak Nota";
:Serahkan nota fisik ke pemilik warung mitra dan terima pembayaran produk terjual;
stop
@enduml
```

---

### Activity 2: Konfirmasi Stok Pra-Kunjungan (H-2) & Verifikasi Selisih
Diagram ini memodelkan alur konfirmasi stok H-2 sebelum kunjungan fisik supplier untuk mendeteksi selisih stok lebih awal.

```plantuml
@startuml activity_konfirmasi_stok
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false

|Supplier|
start
:Buka menu "Konfirmasi Stok" H-2 sebelum tanggal kunjungan rutin;
:Identifikasi warung yang perlu konfirmasi stok;
:Kirim pesan WhatsApp konfirmasi ke nomor kontak pemilik warung;

|Mitra Warung|
:Terima pesan WhatsApp dari supplier;
:Cek sisa fisik toples keripik di warung;
if (Pilihan cara konfirmasi) then (Balas Pesan WhatsApp)
    :Kirimkan angka sisa toples via chat WA;
    |Supplier|
    :Terima pesan balasan WA dari mitra;
    :Buka form "Catat Konfirmasi Stok";
    :Input tanggal konfirmasi, sisa stok dilaporkan, dan catatan WA;
    :Klik "Simpan Konfirmasi";
else (Login Portal Warung)
    :Buka URL Portal Warung -> Masukkan PIN Mitra;
    :Buka menu "Konfirmasi Stok Mandiri";
    :Input angka sisa toples fisik dan kirim;
endif

|Sistem PartnerStock|
:Simpan data ke tabel `stock_confirmations`;
:Set status konfirmasi = "Terkonfirmasi";
:Kalkulasi estimasi kebutuhan muatan pra-kunjungan;

|Supplier|
:Tiba hari kunjungan fisik (H-0);
:Lakukan inspeksi fisik toples di lokasi warung mitra;
:Input data cek fisik aktual pada form kunjungan;

|Sistem PartnerStock|
:Bandingkan angka konfirmasi ($S_{WA}$) dengan cek fisik aktual ($S_{Fisik}$);
if ($S_{WA} == S_{Fisik}$) then (Sama)
    :Tandai status audit: "Sesuai";
else (Ada Perbedaan)
    :Hitung selisih: $Discrepancy = |S_{WA} - S_{Fisik}|$;
    :Tandai status audit: "Ada Selisih";
    :Tampilkan badge selisih pada riwayat audit;
endif
stop
@enduml
```

---

### Activity 3: Peramalan Stok Sistem (WMA 3 Periode) & Penyesuaian Muatan
Diagram ini memodelkan pekerjaan sistem dalam menghitung ramalan kebutuhan produk berdasarkan bobot historis penjualan dan perencanaan muatan kendaraan.

```plantuml
@startuml activity_wma_forecasting
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false

|Supplier|
start
:Buka menu "Rekomendasi Stok" atau "Analisis Penjualan";
:Pilih mode konsinyasi yang digunakan (Mode A atau Mode B);

|Sistem PartnerStock|
:Ambil konfigurasi bobot WMA dari `system_settings`:
Bobot: $w_1 = 0.20$ (terlama), $w_2 = 0.30$ (tengah), $w_3 = 0.50$ (terbaru);
:Iterasi seluruh warung mitra aktif;

partition "Proses WmaService" {
    :Ambil riwayat penjualan aktual 3 periode terakhir ($X_1, X_2, X_3$);
    if (Jumlah data periode tersedia) then (3 Periode atau Lebih)
        :Hitung nilai WMA:
        $Raw\_Forecast = (0.20 \times X_1) + (0.30 \times X_2) + (0.50 \times X_3)$;
        :Terapkan pembulatan ke atas (*ceiling*):
        $F = \lceil Raw\_Forecast \rceil$;
        :Tentukan status data = "Cukup";
        :Klasifikasikan tren penjualan:
        - Jika $X_1 < X_2 < X_3$ -> Tren "Meningkat"
        - Jika $X_1 > X_2 > X_3$ -> Tren "Menurun"
        - Lainnya -> Tren "Stabil";
    else if (1 atau 2 Periode)
        :Normalisasi bobot sesuai periode yang ada;
        :Hitung perkiraan proporsional;
        :Tandai status data = "Belum Cukup (Normalisasi)";
    else (0 Periode / Mitra Baru)
        :Ambil kuota awal mitra baru dari setting (default: 20 bungkus);
        :Tandai status data = "Mitra Baru";
    endif
}

partition "Proses RecommendationService" {
    :Ambil sisa stok terakhir warung ($S$);
    if (Mode Konsinyasi == "Mode A") then (Stok Lama Ditarik & Diganti Baru)
        :Formula Rekomendasi:
        $Rekomendasi = F$;
    else (Mode B: Stok Lama Tetap Dijual)
        :Formula Rekomendasi:
        $Rekomendasi = \max(0, F - S)$;
    endif
    :Pastikan batas minimal penaruhan (minimal 10 bungkus jika warung aktif);
}

:Simpan hasil kalkulasi ke tabel `stock_recommendations`;
:Tampilkan tabel rencana muatan pra-kunjungan;

|Supplier|
:Tinjau rekomendasi sistem per warung;
if (Perlu penyesuaian kuota manual?) then (Ya, ubah kuota)
    :Klik tombol sesuaikan;
    :Input angka kuota persetujuan (approval);
    :Simpan penyesuaian;
    |Sistem PartnerStock|
    :Perbarui kolom `approved_quantity`;
else (Tidak, gunakan saran sistem)
    :Gunakan kuota rekomendasi sistem;
endif

|Sistem PartnerStock|
:Akumulasi total kuota seluruh warung yang disetujui:
$Total\_Muatan = \sum Approved\_Quantity$;
:Kalkulasi kapasitas muatan kendaraan (motor / mobil);
:Tampilkan ringkasan "Total Stok yang Harus Dibawa";

|Supplier|
:Muat keripik pisang ke kendaraan supplier sesuai total muatan;
stop
@enduml
```

---

### Activity 4: Evaluasi Otomatis CRM & Tindak Lanjut Churn Prevention
Diagram ini memodelkan pekerjaan pengawasan kemitraan, deteksi anomali penjualan atau masa dorman, serta pencatatan log tindakan pembinaan supplier.

```plantuml
@startuml activity_crm_churn
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false

|Sistem PartnerStock|
start
:Trigger otomatis saat membuka Dashboard atau menu Pusat CRM (`evaluateAll`);
:Ambil seluruh warung mitra dengan status kemitraan aktif;

partition "Evaluasi Aturan Churn Prevention" {
    :Periksa kondisi 1: Riwayat 3 periode penjualan;
    if (Penjualan turun 3 periode berturut ($X_1 > X_2 > X_3$)?) then (Ya, terdeteksi penurunan)
        :Buat objek alert kategori: `churn_prevention`;
        :Deskripsi: "Penjualan turun 3 periode berturut-turut";
        :Tingkat keparahan = `danger`;
    endif

    :Periksa kondisi 2: Tanggal transaksi restock terakhir;
    if (Hari sejak restock terakhir > 28 hari (> 2 siklus)?) then (Ya, terdeteksi dorman)
        :Buat objek alert kategori: `churn_prevention`;
        :Deskripsi: "Sudah > 28 hari tidak ada pengiriman/restock";
        :Tingkat keparahan = `warning`;
    endif

    :Periksa deduplikasi:
    Cek apakah sudah ada alert sejenis yang masih berstatus belum selesai;
    if (Alert aktif sudah ada di tabel `crm_alerts`?) then (Ya)
        :Abaikan (jangan buat duplikat);
    else (Belum ada)
        :Simpan alert baru ke database dengan status "Aktif";
    endif
}

|Supplier|
:Buka menu "Pusat CRM" atau lihat banner alert di Dashboard;
:Identifikasi warung mitra yang berisiko churn;
:Klik tombol "Tindak Lanjut / Follow Up";
:Buka modal form tindak lanjut;
:Lakukan komunikasi dengan pemilik warung (kunjungan / telepon / WhatsApp);
:Input metode komunikasi yang digunakan;
:Input ringkasan hasil pembicaraan dan rencana aksi (misal: ganti display, diskon promo);
:Pilih status penyelesaian ("Selesai" / "Dalam Proses");
:Klik "Simpan Tindak Lanjut";

|Sistem PartnerStock|
:Simpan log ke tabel `crm_follow_ups`;
:Perbarui status `crm_alerts` menjadi "Selesai";
:Perbarui metrik retensi kemitraan di Dashboard;
stop
@enduml
```

---

### Activity 5: Portal Mandiri Mitra Warung & Pengajuan Restock (PO)
Diagram ini memodelkan alur interaksi pemilik warung mitra saat mengakses portal mandiri via PIN dan mengajukan pesanan restock di luar siklus rutin.

```plantuml
@startuml activity_portal_warung
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false

|Mitra Warung|
start
:Buka browser pada perangkat smartphone / PC;
:Akses alamat `/portal`;

|Sistem PartnerStock|
:Tampilkan formulir otentikasi cepat PIN Portal Warung;

|Mitra Warung|
:Masukkan 4 digit PIN Mitra Warung;
:Klik "Masuk Portal";

|Sistem PartnerStock|
:Validasi PIN terhadap kolom `pin` di tabel `warungs`;
if (PIN valid?) then (Ya)
    :Set session portal warung mitra;
    :Buka halaman `/portal/{kode_unik}`;
else (Tidak valid)
    :Tampilkan notifikasi "PIN salah atau warung nonaktif";
    stop
endif

:Tampilkan Dashboard Warung:
- Sisa stok tercatat di toples
- Tanggal pengiriman terakhir
- Harga kesepakatan produk
- Histori transaksi titip jual;

|Mitra Warung|
if (Stok di toples menipis sebelum jadwal rutin 14 hari?) then (Ya, butuh restock cepat)
    :Klik tombol "Ajukan Tambahan Stok";
    :Isi jumlah bungkus yang diminta;
    :Isi catatan kebutuhan (misal: ada pesanan hajatan warga);
    :Klik "Kirim Permintaan";

    |Sistem PartnerStock|
    :Validasi kuota permintaan (minimal > 0);
    :Simpan data ke tabel `pengajuan_stoks` dengan status "Pending";
    :Tampilkan estimasi tiba (H+2) pada portal mitra;
else (Tidak)
    :Cukup pantau stok & riwayat penjualan;
    stop
endif

|Supplier|
:Login ke akun Admin Supplier;
:Buka menu "Permintaan Tambahan";
:Lihat daftar pengajuan berstatus "Pending";
:Pilih dropdown status pada baris warung terkait:
- Ubah status menjadi "Diproses" saat barang disiapkan
- Ubah status menjadi "Selesai" saat barang diserahkan ke warung;

|Sistem PartnerStock|
:Perbarui record `pengajuan_stoks` di database;
:Status terbaru langsung tersinkronisasi di portal warung mitra;
stop
@enduml
```

---

### Activity 6: Rekapitulasi Laporan & Kalkulasi Finansial
Diagram ini memodelkan pekerjaan pembuatan laporan rekapitulasi penjualan aktual, penarikan stok lama, dan penghitungan pendapatan supplier.

```plantuml
@startuml activity_laporan_keuangan
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false

|Supplier|
start
:Buka menu "Laporan" pada sidebar navigasi;

|Sistem PartnerStock|
:Tampilkan formulir filter laporan;

|Supplier|
:Tentukan parameter filter:
- Pilih "Semua Warung" atau nama warung spesifik
- Pilih rentang "Dari Tanggal" dan "Sampai Tanggal";
:Klik tombol "Filter";

|Sistem PartnerStock|
partition "Agregasi Data Laporan" {
    :Query tabel `restocks` dalam rentang tanggal -> Hitung $Total\_Dititipkan$;
    :Query tabel `sales_records` dalam rentang tanggal -> Hitung $Total\_Terjual$;
    :Query tabel `restocks` kolom `old_stock_withdrawn` -> Hitung $Total\_Ditarik$;
    :Hitung Pendapatan Finansial Supplier secara dinamis:
    $$\text{Pendapatan} = \sum (\text{Terjual} \times \text{Harga Titip Warung})$$;
}
:Tampilkan 4 Kartu KPI Ringkasan:
1. Total Dititipkan (bungkus baru)
2. Total Terjual (bungkus laku)
3. Total Ditarik (stok lama retur)
4. Pendapatan Supplier (Rp);
:Tampilkan Tabel Rincian 1: Penjualan Aktual (Periode, Mitra, Stok Awal, Sisa Fisik, Terjual, Subtotal Pendapatan);
:Tampilkan Tabel Rincian 2: Distribusi & Penarikan Terpisah (Tanggal, Mitra, Titip Baru, Stok Lama Ditarik);

|Supplier|
if (Butuh dokumen cetak / PDF?) then (Ya)
    :Klik tombol "Cetak Laporan";
    |Sistem PartnerStock|
    :Terapkan CSS `@media print` (sembunyikan sidebar, navbar, tombol filter);
    :Buka jendela cetak sistem operasi (Print Dialog);
    |Supplier|
    :Pilih printer fisik atau "Save as PDF";
    :Simpan dokumen laporan resmi;
else (Tidak)
    :Cukup pantau angka di layar;
endif
stop
@enduml
```

---

## 3. Class Diagram Ringkas & Terpadu (5 Entitas Inti Bisnis)

Class Diagram di bawah ini memodelkan struktur kode sumber platform **PartnerStock** secara minimalis dan tepat sasaran berfokus pada **5 Model Inti Bisnis**:
- **Lapisan Model Eloquent (5 Entitas Utama)**: `User`, `Warung`, `Product`, `StokPenjualan` (menyatukan pencatatan distribusi, penjualan & retur), dan `PengajuanStok` (PO mandiri).
- **Lapisan Service Logika Bisnis**: Peramalan WMA dan evaluasi CRM dijalankan secara dinamis (*real-time logic engine*) dari riwayat `StokPenjualan` tanpa membebani basis data dengan tabel tambahan.
- **Lapisan Controller (MVC)**: Pengendali antarmuka web supplier dan portal mitra warung.

```plantuml
@startuml class_diagram_partnerstock
!theme plain
skinparam backgroundColor #FFFFFF
skinparam handwritten false
skinparam shadowing false
skinparam classAttributeIconSize 0

skinparam class {
    BackgroundColor #FFFFFF
    BorderColor #0F172A
    FontName "Plus Jakarta Sans", Arial, sans-serif
    FontSize 10
}

package "App\\Models (5 Entitas Bisnis Inti)" {

    class User {
        +id: int
        +name: string
        +email: string
        +password: string
        +role: string
        +is_active: bool
        --
        +warung(): HasOne
        +isSupplier(): bool
        +isMitra(): bool
    }

    class Warung {
        +id: int
        +user_id: int
        +nama_warung: string
        +nama_pemilik: string
        +alamat: text
        +kontak: string
        +kode_unik: string
        +pin: string
        +stok_saat_ini: int
        +harga_titip: int
        +harga_jual: int
        +partnership_status: string
        +delivery_interval_days: int
        +is_aktif: bool
        --
        +user(): BelongsTo
        +stokPenjualans(): HasMany
        +pengajuanStoks(): HasMany
        +getSesiTerakhirAttribute(): StokPenjualan
    }

    class Product {
        +id: int
        +product_code: string
        +name: string
        +unit: string
        +agreed_price: int
        +cost_price: int
        +is_active: bool
        --
        +stokPenjualans(): HasMany
    }

    class StokPenjualan {
        +id: int
        +warung_id: int
        +product_id: int
        +tipe_transaksi: string
        +tanggal_cek: date
        +tanggal_titip: date
        +stok_sebelumnya: int
        +sisa_fisik: int
        +jumlah_laku: int
        +jumlah_titip: int
        +old_stock_withdrawn: int
        +sisa_stok: int
        +total_pendapatan: int
        +catatan: text
        +keputusan_stok: string
        --
        +warung(): BelongsTo
        +product(): BelongsTo
        +{static} hitungKeputusan(taro: int, sisa: int): array
    }

    class PengajuanStok {
        +id: int
        +warung_id: int
        +tanggal_pengajuan: date
        +jumlah_diminta: int
        +status: string
        +catatan: text
        +catatan_pemasok: text
        +tanggal_diproses: datetime
        --
        +warung(): BelongsTo
        +getStatusLabelAttribute(): string
    }
}

package "App\\Services (Business Logic Engine)" {

    class WmaService {
        +calculate(salesValues: array, customWeights: array): array
        +calculateForWarung(warung: Warung): array
    }

    class RecommendationService {
        -wmaService: WmaService
        --
        +getRecommendationForWarung(warung: Warung, mode: string): array
        +getAllRecommendations(mode: string): array
    }

    class CrmService {
        -wmaService: WmaService
        -recService: RecommendationService
        --
        +evaluateAll(): array
        +evaluateWarung(warung: Warung): array
    }
}

package "App\\Http\\Controllers (Application Web Layer)" {

    class AuthController {
        +showLogin(): View
        +login(request: Request): RedirectResponse
        +logout(request: Request): RedirectResponse
    }

    class DashboardController {
        -crmService: CrmService
        -recService: RecommendationService
        -wmaService: WmaService
        --
        +index(): View
    }

    class WarungController {
        +index(request: Request): View
        +create(): View
        +store(request: Request): RedirectResponse
        +edit(warung: Warung): View
        +update(request: Request, warung: Warung): RedirectResponse
        +destroy(warung: Warung): RedirectResponse
    }

    class KunjunganController {
        +index(request: Request): View
        +create(): View
        +store(request: Request): RedirectResponse
        +nota(id: int): View
    }

    class PengajuanAdminController {
        +index(request: Request): View
        +updateStatus(request: Request, id: int): RedirectResponse
    }

    class WarungPortalController {
        +index(): View
        +authByPin(request: Request): RedirectResponse
        +show(kode_unik: string): View
        +submitPengajuan(request: Request, kode_unik: string): RedirectResponse
        +logout(): RedirectResponse
    }

    class CrmController {
        -crmService: CrmService
        -wmaService: WmaService
        --
        +index(request: Request): View
    }
}

' Relasi Antar Model Inti
User "1" *-- "0..*" Warung : membina mitra
User "1" *-- "0..1" Warung : akun portal warung

Warung "1" *-- "0..*" StokPenjualan : riwayat transaksi konsinyasi
Warung "1" *-- "0..*" PengajuanStok : permintaan tambahan (PO)

Product "1" -- "0..*" StokPenjualan : item produk konsinyasi

' Dependensi Service Layer ke Model
RecommendationService ..> WmaService : uses
CrmService ..> WmaService : uses
RecommendationService ..> Warung : evaluates
WmaService ..> StokPenjualan : calculates from history
CrmService ..> StokPenjualan : monitors sales trends

' Dependensi Controller ke Service & Model
DashboardController ..> CrmService : invokes
DashboardController ..> RecommendationService : queries
DashboardController ..> Warung : queries
KunjunganController ..> StokPenjualan : records visit
KunjunganController ..> Warung : updates stock
WarungPortalController ..> Warung : authenticates PIN
WarungPortalController ..> PengajuanStok : stores PO
PengajuanAdminController ..> PengajuanStok : manages status
CrmController ..> CrmService : displays alerts

@enduml
```

---

## 4. Entity Relationship Diagram (ERD Inti Sistem - 5 Tabel Utama)

Diagram ERD (*Entity-Relationship Diagram*) di bawah ini memodelkan struktur basis data inti **PartnerStock (PostgreSQL: `konsinyasi_pisang`)** yang berfokus pada 5 entitas bisnis utama menggunakan notasi resmi **Crow's Foot (IE Notation)**:

> [!TIP]
> **Kaidah Akademik Database Bersih**:
> - Hanya menampilkan 5 entitas nyata bisnis konsinyasi: Pengguna, Mitra Warung, Produk Keripik, Riwayat Distribusi/Penjualan, dan Permintaan Restock (PO).
> - Perhitungan peramalan WMA dan evaluasi CRM dijalankan secara dinamis (*real-time logic*) di lapisan aplikasi, sehingga basis data memenuhi prinsip **Third Normal Form (3NF)** tanpa menyimpan data kalkulasi berulang (*no derived redundancy*).

```plantuml
@startuml erd_partnerstock_simple
!theme plain
skinparam backgroundColor #FFFFFF
skinparam shadowing false
skinparam linetype ortho
skinparam roundcorner 5

skinparam entity {
    BackgroundColor #FFFFFF
    BorderColor #0F172A
    FontName "Plus Jakarta Sans", Arial, sans-serif
    FontSize 10
    HeaderBackgroundColor #F1F5F9
}

entity "users" as users {
    * id : BIGINT <<PK>>
    --
    * name : VARCHAR(255)
    * email : VARCHAR(255) <<UNIQUE>>
    * password : VARCHAR(255)
    * role : VARCHAR(50) [supplier / mitra]
    * is_active : BOOLEAN
    created_at : TIMESTAMP
    updated_at : TIMESTAMP
}

entity "warungs" as warungs {
    * id : BIGINT <<PK>>
    --
    user_id : BIGINT <<FK>>
    * nama_warung : VARCHAR(255)
    nama_pemilik : VARCHAR(255)
    alamat : TEXT
    kontak : VARCHAR(50)
    * kode_unik : VARCHAR(50) <<UNIQUE>>
    * pin : VARCHAR(10)
    stok_saat_ini : INT
    harga_titip : INT
    harga_jual : INT
    partnership_status : VARCHAR(50) [aktif / nonaktif]
    delivery_interval_days : INT
    partnership_start_date : DATE
    is_aktif : BOOLEAN
    created_at : TIMESTAMP
    updated_at : TIMESTAMP
}

entity "products" as products {
    * id : BIGINT <<PK>>
    --
    * product_code : VARCHAR(50)
    * name : VARCHAR(255)
    unit : VARCHAR(50)
    agreed_price : DECIMAL(12,2)
    cost_price : DECIMAL(12,2)
    is_active : BOOLEAN
    created_at : TIMESTAMP
    updated_at : TIMESTAMP
}

entity "stok_penjualans" as stok_penjualans {
    * id : BIGINT <<PK>>
    --
    * warung_id : BIGINT <<FK>>
    product_id : BIGINT <<FK>>
    tipe_transaksi : VARCHAR(50) [titip_awal / sesi]
    tanggal_cek : DATE
    tanggal_titip : DATE
    stok_sebelumnya : INT
    sisa_fisik : INT
    jumlah_laku : INT
    jumlah_titip : INT
    old_stock_withdrawn : INT
    sisa_stok : INT
    total_pendapatan : INT
    catatan : TEXT
    keputusan_stok : VARCHAR(50)
    created_at : TIMESTAMP
    updated_at : TIMESTAMP
}

entity "pengajuan_stoks" as pengajuan_stoks {
    * id : BIGINT <<PK>>
    --
    * warung_id : BIGINT <<FK>>
    tanggal_pengajuan : DATE
    jumlah_diminta : INT
    status : VARCHAR(50) [pending / diproses / selesai / ditolak]
    catatan : TEXT
    catatan_pemasok : TEXT
    tanggal_diproses : TIMESTAMP
    created_at : TIMESTAMP
    updated_at : TIMESTAMP
}

' Relasi Antar Entitas (Crow's Foot Notation)
users ||--o| warungs : "1 user opsional terhubung ke 1 akun warung"
users ||--o{ warungs : "1 supplier mengelola banyak mitra warung"

warungs ||--o{ stok_penjualans : "1 warung memiliki banyak riwayat transaksi konsinyasi"
warungs ||--o{ pengajuan_stoks : "1 warung dapat mengajukan banyak permintaan stok (PO)"

products ||--o{ stok_penjualans : "1 jenis produk dititipkan di banyak transaksi konsinyasi"

@enduml
```

---

## 5. Petunjuk Render PlantUML

Anda dapat merender seluruh kode PlantUML di atas menjadi berkas grafis (PNG, SVG, atau PDF) melalui beberapa cara mudah:

### Opsi A: Ekstensi VS Code / IDE
1. Buka file [`DIAGRAM_SISTEM.md`](file:///d:/1-tugas-kuliah/berkas%20file/s7/projek-web/DIAGRAM_SISTEM.md) di Visual Studio Code.
2. Pasang ekstensi **PlantUML** oleh *jebbs*.
3. Letakkan kursor di dalam salah satu blok diagram kode `@startuml ... @enduml`.
4. Tekan pintasan: `Alt + D` untuk melihat pratinjau instan (*Instant Preview*).
5. Klik kanan pada jendela pratinjau -> **Export Current Diagram** untuk menyimpan menjadi format SVG atau PNG resolusi tinggi.

### Opsi B: Web Editor Instan (Tanpa Instalasi)
1. Salin seluruh teks yang berada di dalam blok kode (dari `@startuml` hingga `@enduml`).
2. Kunjungi salah satu portal editor online resmi berikut:
   - **PlantText**: [https://www.planttext.com](https://www.planttext.com)
   - **PlantUML Online Server**: [https://www.plantuml.com/plantuml/uml/](https://www.plantuml.com/plantuml/uml/)
3. Tempel (*paste*) kode tersebut ke kolom editor.
4. Diagram akan langsung dirender secara otomatis dan siap diunduh (*Download SVG / PNG*).

### Opsi C: Command Line Interface (CLI)
Jika menggunakan Java dan `plantuml.jar`:
```bash
# Render seluruh diagram dalam file menjadi gambar PNG
java -jar plantuml.jar DIAGRAM_SISTEM.md -o ./diagram_output

# Render menjadi vektor SVG berkualitas cetak
java -jar plantuml.jar -tsvg DIAGRAM_SISTEM.md -o ./diagram_output
```
