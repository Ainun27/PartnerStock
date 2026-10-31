<?php

namespace Database\Seeders;

use App\Models\CrmAlert;
use App\Models\CrmFollowUp;
use App\Models\Product;
use App\Models\Restock;
use App\Models\SalesRecord;
use App\Models\StockConfirmation;
use App\Models\StockRecommendation;
use App\Models\StockRequest;
use App\Models\StokPenjualan;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Warung;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic simulation data for Pak Aceng.
     */
    public function run(): void
    {
        // 1. Akun Pemilik Usaha / Supplier (Pak Aceng)
        $supplierUser = User::updateOrCreate(
            ['email' => 'admin@keripikpisang.com'],
            [
                'name' => 'Pak Aceng',
                'password' => Hash::make('admin123'),
                'role' => 'supplier',
                'is_active' => true,
            ]
        );

        $supplier = Supplier::updateOrCreate(
            ['user_id' => $supplierUser->id],
            [
                'business_name' => 'Keripik Pisang Original Pak Aceng',
                'phone' => '081298765432',
                'address' => 'Sentra UMKM Keripik Pisang, Bantul, D.I. Yogyakarta',
            ]
        );

        // 2. Satu-satunya Produk Pak Aceng: Keripik Pisang Original
        $product = Product::updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'product_code' => 'KRP-ORI',
            ],
            [
                'name' => 'Keripik Pisang Original',
                'unit' => 'bungkus',
                'agreed_price' => 15000,
                'cost_price' => 12000,
                'is_active' => true,
            ]
        );

        // 3. Konfigurasi Sistem
        SystemSetting::set('wma_weights', [0.20, 0.30, 0.50], 'Bobot peramalan Weighted Moving Average (X1 terlama 20%, X2 tengah 30%, X3 terbaru 50%)', $supplier->id);
        SystemSetting::set('consignment_mode', 'A', 'Mode Konsinyasi: A (Stok lama ditarik seluruhnya & diganti baru), B (Stok lama tetap dijual)', $supplier->id);
        SystemSetting::set('delivery_interval_days', 14, 'Interval rutin pengiriman konsinyasi (hari)', $supplier->id);
        SystemSetting::set('initial_stock_quota', 20, 'Kuota stok awal untuk warung mitra baru', $supplier->id);

        // 4. Data 6 Warung Mitra Demo
        $warungsData = [
            [
                'name' => 'Warung Berkah Bu Siti',
                'nama_warung' => 'Warung Berkah Bu Siti',
                'owner_name' => 'Bu Siti Aminah',
                'nama_pemilik' => 'Bu Siti Aminah',
                'alamat' => 'Jl. Raya Mawar No. 12, Condongcatur, Sleman',
                'phone' => '081234567890',
                'kontak' => '081234567890',
                'kode_unik' => 'WRG-BERKAH',
                'pin' => '1234',
                'stok_saat_ini' => 4,
                'harga_titip' => 12000,
                'harga_jual' => 15000,
                'partnership_start_date' => Carbon::now()->subMonths(3),
                'delivery_interval_days' => 14,
                'partnership_status' => 'aktif',
                'is_aktif' => true,
                'catatan' => 'Lokasi strategis dekat komplek kos mahasiswa. Penjualan stabil dan meningkat.',
                'email' => 'busiti@warungberkah.com',
            ],
            [
                'name' => 'Warung Madura Cak Rozaq',
                'nama_warung' => 'Warung Madura Cak Rozaq',
                'owner_name' => 'Cak Rozaq',
                'nama_pemilik' => 'Cak Rozaq',
                'alamat' => 'Jl. Gejayan No. 88, Depok, Sleman',
                'phone' => '087855566677',
                'kontak' => '087855566677',
                'kode_unik' => 'WRG-MADURA',
                'pin' => '4567',
                'stok_saat_ini' => 14,
                'harga_titip' => 12000,
                'harga_jual' => 15000,
                'partnership_start_date' => Carbon::now()->subMonths(4),
                'delivery_interval_days' => 14,
                'partnership_status' => 'aktif',
                'is_aktif' => true,
                'catatan' => 'Buka 24 jam penuh. Tiga periode terakhir mengalami penurunan penjualan karena posisi display tergeser.',
                'email' => 'cakrozaq@warungmadura.com',
            ],
            [
                'name' => 'Warung Pojok Mbak Rina',
                'nama_warung' => 'Warung Pojok Mbak Rina',
                'owner_name' => 'Mbak Rina Anggraini',
                'nama_pemilik' => 'Mbak Rina Anggraini',
                'alamat' => 'Jl. Kaliurang KM 9, Gang Jeruk No. 4, Sleman',
                'phone' => '085712341234',
                'kontak' => '085712341234',
                'kode_unik' => 'WRG-POJOK',
                'pin' => '3456',
                'stok_saat_ini' => 6,
                'harga_titip' => 12000,
                'harga_jual' => 15000,
                'partnership_start_date' => Carbon::now()->subMonths(1),
                'delivery_interval_days' => 14,
                'partnership_status' => 'aktif',
                'is_aktif' => true,
                'catatan' => 'Baru bermitra 1 bulan (2 periode). Belum konfirmasi stok via WhatsApp untuk jadwal minggu ini.',
                'email' => 'mbakrina@warungpojok.com',
            ],
            [
                'name' => 'Toko Barokah Pak Joko',
                'nama_warung' => 'Toko Barokah Pak Joko',
                'owner_name' => 'Pak Joko Widodo',
                'nama_pemilik' => 'Pak Joko Widodo',
                'alamat' => 'Pasar Tradisional Kios Blok B-05, Bantul',
                'phone' => '081398765432',
                'kontak' => '081398765432',
                'kode_unik' => 'WRG-BAROKAH',
                'pin' => '2345',
                'stok_saat_ini' => 5,
                'harga_titip' => 12000,
                'harga_jual' => 15000,
                'partnership_start_date' => Carbon::now()->subMonths(3),
                'delivery_interval_days' => 14,
                'partnership_status' => 'aktif',
                'is_aktif' => true,
                'catatan' => 'Kios pasar ramai setiap pagi. Mengajukan permintaan stok tambahan untuk persiapan hari pasar.',
                'email' => 'pakjoko@tokobarokah.com',
            ],
            [
                'name' => 'Kios Snack Sejahtera Mas Danang',
                'nama_warung' => 'Kios Snack Sejahtera Mas Danang',
                'owner_name' => 'Danang Prasetyo',
                'nama_pemilik' => 'Danang Prasetyo',
                'alamat' => 'Area Terminal Condongcatur Kios No. 03',
                'phone' => '082133445566',
                'kontak' => '082133445566',
                'kode_unik' => 'WRG-SEJAHTERA',
                'pin' => '5678',
                'stok_saat_ini' => 12,
                'harga_titip' => 12000,
                'harga_jual' => 15000,
                'partnership_start_date' => Carbon::now()->subMonths(5),
                'delivery_interval_days' => 14,
                'partnership_status' => 'aktif',
                'is_aktif' => true,
                'catatan' => 'Sudah 35 hari belum ada pengiriman ulang (melebihi 2 siklus rutin). Perlu verifikasi keaktifan.',
                'email' => 'masdanang@kiossejahtera.com',
            ],
            [
                'name' => 'Warung Barokah Rejeki Bu Yayuk',
                'nama_warung' => 'Warung Barokah Rejeki Bu Yayuk',
                'owner_name' => 'Bu Yayuk Sri',
                'nama_pemilik' => 'Bu Yayuk Sri',
                'alamat' => 'Jl. Palagan Tentara Pelajar KM 8, Ngaglik',
                'phone' => '081822334455',
                'kontak' => '081822334455',
                'kode_unik' => 'WRG-YAYUK',
                'pin' => '6789',
                'stok_saat_ini' => 0,
                'harga_titip' => 12000,
                'harga_jual' => 15000,
                'partnership_start_date' => Carbon::now()->subDays(3),
                'delivery_interval_days' => 14,
                'partnership_status' => 'aktif',
                'is_aktif' => true,
                'catatan' => 'Mitra baru mendaftar minggu ini. Belum ada riwayat penjualan (Data Belum Cukup). Disarankan bawa kuota awal 20 pcs.',
                'email' => 'buyayuk@warungrejeki.com',
            ],
        ];

        $createdWarungs = [];
        foreach ($warungsData as $data) {
            $userEmail = $data['email'];
            unset($data['email']);

            $warungUser = User::updateOrCreate(
                ['email' => $userEmail],
                [
                    'name' => $data['owner_name'],
                    'password' => Hash::make('warung123'),
                    'role' => 'warung',
                    'is_active' => true,
                ]
            );

            $data['supplier_id'] = $supplier->id;
            $data['user_id'] = $warungUser->id;

            $warung = Warung::updateOrCreate(
                ['kode_unik' => $data['kode_unik']],
                $data
            );
            $createdWarungs[] = $warung;
        }

        // ==========================================
        // 5. RIWAYAT PENJUALAN HISTORIS & SIKLUS KONSINYASI
        // ==========================================

        // WARUNG 1: Warung Berkah Bu Siti (Tren Penjualan Meningkat: 12 -> 16 -> 22)
        // Periode 1: 6 minggu lalu (Titip 20, laku 12, sisa 8 ditarik)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[0],
            $product,
            Carbon::now()->subDays(42),
            Carbon::now()->subDays(28),
            20, 12, 8, 8,
            'Penjualan perdana lancar, kemasan diminati pembeli.'
        );

        // Periode 2: 4 minggu lalu (Titip 20, laku 16, sisa 4 ditarik)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[0],
            $product,
            Carbon::now()->subDays(28),
            Carbon::now()->subDays(14),
            20, 16, 4, 4,
            'Permintaan meningkat, banyak mahasiswa beli malam hari.'
        );

        // Periode 3: 2 minggu lalu (Titip 25, laku 22, sisa 3 ditarik)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[0],
            $product,
            Carbon::now()->subDays(14),
            Carbon::now()->subDays(1),
            25, 22, 3, 3,
            'Sangat laris, habis hampir tak bersisa.'
        );

        // Konfirmasi stok via WA H-2 Bu Siti: sisa 4 bungkus
        StockConfirmation::updateOrCreate(
            [
                'warung_id' => $createdWarungs[0]->id,
                'confirmation_date' => Carbon::now()->subDays(1)->toDateString(),
            ],
            [
                'supplier_id' => $supplier->id,
                'reported_remaining_stock' => 4,
                'physically_checked_stock' => null,
                'data_status' => 'menunggu_pemeriksaan',
                'notes' => 'Konfirmasi via WhatsApp: Stok toples tersisa 4 bungkus, minta dikirim seperti biasa.',
            ]
        );

        // WARUNG 2: Warung Madura Cak Rozaq (Tren Penjualan Menurun: 22 -> 16 -> 10)
        // Periode 1: 6 minggu lalu (Titip 25, laku 22, sisa 3 ditarik)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[1],
            $product,
            Carbon::now()->subDays(42),
            Carbon::now()->subDays(28),
            25, 22, 3, 3,
            'Penjualan awal sangat baik.'
        );

        // Periode 2: 4 minggu lalu (Titip 25, laku 16, sisa 9 ditarik)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[1],
            $product,
            Carbon::now()->subDays(28),
            Carbon::now()->subDays(14),
            25, 16, 9, 9,
            'Mulai menurun karena ada renovasi jalan depan warung.'
        );

        // Periode 3: 2 minggu lalu (Titip 24, laku 10, sisa 14 ditarik)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[1],
            $product,
            Carbon::now()->subDays(14),
            Carbon::now()->subDays(2),
            24, 10, 14, 14,
            'Penjualan turun drastis, toples tergeser ke sudut belakang meja kasir.'
        );

        // Konfirmasi stok via WA H-2 Cak Rozaq: sisa 14 bungkus
        StockConfirmation::updateOrCreate(
            [
                'warung_id' => $createdWarungs[1]->id,
                'confirmation_date' => Carbon::now()->subDays(2)->toDateString(),
            ],
            [
                'supplier_id' => $supplier->id,
                'reported_remaining_stock' => 14,
                'physically_checked_stock' => null,
                'data_status' => 'menunggu_pemeriksaan',
                'notes' => 'Konfirmasi via WhatsApp: Masih banyak sisa sekitar 14 bungkus.',
            ]
        );

        // WARUNG 3: Warung Pojok Mbak Rina (Data Belum Cukup: 2 Periode, 14 -> 18)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[2],
            $product,
            Carbon::now()->subDays(28),
            Carbon::now()->subDays(14),
            20, 14, 6, 6,
            'Penjualan perdana cukup bagus.'
        );
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[2],
            $product,
            Carbon::now()->subDays(14),
            Carbon::now()->subDays(3),
            20, 18, 2, 2,
            'Penjualan meningkat.'
        );
        // Warung 3 belum ada konfirmasi stok baru via WhatsApp (terakhir 14 hari lalu)

        // WARUNG 4: Toko Barokah Pak Joko (Penjualan Stabil: 15 -> 15 -> 15)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[3],
            $product,
            Carbon::now()->subDays(42),
            Carbon::now()->subDays(28),
            20, 15, 5, 5,
            'Penjualan pasar stabil.'
        );
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[3],
            $product,
            Carbon::now()->subDays(28),
            Carbon::now()->subDays(14),
            20, 15, 5, 5,
            'Penjualan stabil.'
        );
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[3],
            $product,
            Carbon::now()->subDays(14),
            Carbon::now()->subDays(2),
            20, 15, 5, 5,
            'Konsisten 15 bungkus per 2 minggu.'
        );

        StockConfirmation::updateOrCreate(
            [
                'warung_id' => $createdWarungs[3]->id,
                'confirmation_date' => Carbon::now()->subDays(2)->toDateString(),
            ],
            [
                'supplier_id' => $supplier->id,
                'reported_remaining_stock' => 5,
                'physically_checked_stock' => null,
                'data_status' => 'menunggu_pemeriksaan',
                'notes' => 'Konfirmasi via WhatsApp: Sisa 5 bungkus toples.',
            ]
        );

        // Permintaan Stok Tambahan (PO) dari Pak Joko (Menunggu Persetujuan)
        StockRequest::updateOrCreate(
            [
                'warung_id' => $createdWarungs[3]->id,
                'requested_at' => Carbon::now()->subDay(),
            ],
            [
                'supplier_id' => $supplier->id,
                'product_id' => $product->id,
                'requested_quantity' => 10,
                'approved_quantity' => null,
                'reason' => 'Ada pesanan untuk hajatan keluarga hari Sabtu ini, butuh tambahan 10 bungkus.',
                'status' => 'menunggu',
            ]
        );

        // WARUNG 5: Kios Mas Danang (Tidak ada restock 35 hari / > 2 siklus)
        $this->seedConsignmentCycle(
            $supplier,
            $createdWarungs[4],
            $product,
            Carbon::now()->subDays(49),
            Carbon::now()->subDays(35),
            20, 8, 12, 12,
            'Pengiriman terakhir 35 hari lalu. Setelah itu belum ada restock lagi.'
        );

        // 6. BUAT PERINGATAN CRM (CRM ALERTS)
        // Alert 1: Churn Prevention untuk Cak Rozaq (Penjualan turun 3 periode berturut-turut)
        $alertChurnRozaq = CrmAlert::updateOrCreate(
            [
                'warung_id' => $createdWarungs[1]->id,
                'trigger_type' => 'penurunan_penjualan_3_periode',
            ],
            [
                'supplier_id' => $supplier->id,
                'category' => 'churn_prevention',
                'trigger_period' => Carbon::now()->format('Y-m'),
                'description' => "Penjualan aktual di Warung Madura Cak Rozaq mengalami penurunan selama 3 periode berturut-turut (22 pcs -> 16 pcs -> 10 pcs). Disarankan hubungi pemilik warung (Cak Rozaq) untuk mengevaluasi kendala display atau perputaran produk.",
                'evidence_data' => [
                    'sales_history' => [22, 16, 10],
                    'last_sales' => 10,
                    'drop_percentage' => 54.5,
                ],
                'status' => 'dalam_tindak_lanjut',
            ]
        );

        // Catatan Tindak Lanjut untuk Alert Cak Rozaq
        CrmFollowUp::create([
            'crm_alert_id' => $alertChurnRozaq->id,
            'supplier_id' => $supplier->id,
            'warung_id' => $createdWarungs[1]->id,
            'follow_up_date' => Carbon::now()->subDays(1),
            'contact_method' => 'whatsapp',
            'result_notes' => 'Telah menghubungi Cak Rozaq via WhatsApp. Beliau menyampaikan toples keripik sempat tergeser ke bawah meja karena ada penambahan stok rokok baru. Berjanji akan menaruh kembali di baris depan kasir.',
            'next_action' => 'Kunjungi warung saat pengiriman rutin dan bantu atur posisi rak toples keripik di depan kasir.',
            'status' => 'dalam_proses',
        ]);

        // Alert 2: Churn Prevention untuk Kios Mas Danang (Tanpa restock 2 siklus / 35 hari)
        CrmAlert::updateOrCreate(
            [
                'warung_id' => $createdWarungs[4]->id,
                'trigger_type' => 'tanpa_restock_2_siklus',
            ],
            [
                'supplier_id' => $supplier->id,
                'category' => 'churn_prevention',
                'trigger_period' => Carbon::now()->format('Y-m'),
                'description' => 'Sudah 35 hari belum ada pengiriman ulang ke Kios Snack Sejahtera Mas Danang (melebihi 2 siklus rutin 28 hari). Cek apakah warung masih aktif beroperasi atau stok tersisa masih ada.',
                'evidence_data' => [
                    'last_visit' => Carbon::now()->subDays(35)->format('Y-m-d'),
                    'days_inactive' => 35,
                    'delivery_interval' => 14,
                ],
                'status' => 'perlu_dihubungi',
            ]
        );

        // Alert 3: Reorder Recommendation untuk Pak Joko (Permintaan tambahan stok)
        CrmAlert::updateOrCreate(
            [
                'warung_id' => $createdWarungs[3]->id,
                'trigger_type' => 'permintaan_stok_tambahan',
            ],
            [
                'supplier_id' => $supplier->id,
                'category' => 'reorder_recommendation',
                'trigger_period' => Carbon::now()->format('Y-m'),
                'description' => 'Toko Barokah Pak Joko mengajukan permintaan stok tambahan (10 bungkus) di luar jadwal rutin. Periksa dan setujui jumlah yang akan dikirim.',
                'evidence_data' => [
                    'requests_count' => 1,
                    'total_quantity' => 10,
                ],
                'status' => 'perlu_ditinjau',
            ]
        );

        // 7. Simpan Rekomendasi WMA Awal untuk Warung Berkah Bu Siti (F = 18.2 -> 19 bungkus)
        StockRecommendation::updateOrCreate(
            [
                'warung_id' => $createdWarungs[0]->id,
                'calculation_date' => Carbon::now()->toDateString(),
            ],
            [
                'supplier_id' => $supplier->id,
                'product_id' => $product->id,
                'forecast_quantity' => 18.2,
                'recommended_quantity' => 19,
                'approved_quantity' => 19,
                'consignment_mode' => 'A',
                'source_periods' => [12, 16, 22],
                'weights_used' => [0.20, 0.30, 0.50],
                'data_status' => 'cukup',
                'adjustment_reason' => 'Sesuai rekomendasi WMA 3 periode meningkat.',
            ]
        );
    }

    /**
     * Helper untuk membuat siklus konsinyasi: restock, sales_record, dan backward compatible stok_penjualans
     */
    private function seedConsignmentCycle(
        Supplier $supplier,
        Warung $warung,
        Product $product,
        Carbon $startDate,
        Carbon $endDate,
        int $distributed,
        int $sold,
        int $remaining,
        int $withdrawn,
        string $notes
    ): void {
        // 1. Simpan SalesRecord resmi
        SalesRecord::create([
            'supplier_id' => $supplier->id,
            'warung_id' => $warung->id,
            'product_id' => $product->id,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'distributed_quantity' => $distributed,
            'opening_stock' => $distributed,
            'remaining_stock' => $remaining,
            'sold_quantity' => $sold,
            'verified_at' => $endDate,
        ]);

        // 2. Simpan Restock resmi (distribusi & penarikan dicatat terpisah)
        Restock::create([
            'supplier_id' => $supplier->id,
            'warung_id' => $warung->id,
            'product_id' => $product->id,
            'distribution_date' => $startDate->toDateString(),
            'quantity_delivered' => $distributed,
            'old_stock_withdrawn' => $withdrawn,
            'approved_quantity' => $distributed,
            'notes' => $notes,
        ]);

        $decision = StokPenjualan::hitungKeputusan($distributed, $remaining);
        $rekomendasiTitip = (int) $decision['rekomendasi_titip'];
        $keputusanText = $decision['ringkasan'] . ' (' . $decision['label'] . ')';

        // 3. Simpan backward compatible StokPenjualan
        StokPenjualan::create([
            'warung_id' => $warung->id,
            'tipe_transaksi' => 'sesi',
            'tanggal_titip' => $startDate->toDateString(),
            'jumlah_titip' => $rekomendasiTitip,
            'tanggal_cek' => $endDate->toDateString(),
            'stok_sebelumnya' => $distributed,
            'jumlah_laku' => $sold,
            'sisa_stok' => $remaining,
            'sisa_fisik' => $remaining,
            'total_pendapatan' => $sold * 12000,
            'catatan' => $notes,
            'keputusan_stok' => $keputusanText,
        ]);
    }
}
