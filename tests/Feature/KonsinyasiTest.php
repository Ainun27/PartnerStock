<?php

namespace Tests\Feature;

use App\Models\PengajuanStok;
use App\Models\StokPenjualan;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KonsinyasiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (Warung::count() === 0) {
            $this->seed();
        }
    }

    /**
     * Test 1: Halaman Landing dapat diakses dan menampilkan role selector
     */
    public function test_landing_page_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Konsinyasi Keripik Pisang');
        $response->assertSee('Pemasok / Distributor');
        $response->assertSee('Warung Mitra');
    }

    /**
     * Test 2: Pemasok dapat login menggunakan kredensial email & password
     */
    public function test_pemasok_can_login_with_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@keripikpisang.com',
            'password' => 'admin123',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test 3: Dashboard Pemasok menampilkan data konsinyasi
     */
    public function test_admin_dashboard_displays_metrics(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Stok');
        $response->assertSee('Terjual');
        $response->assertSee('Pengajuan');
    }

    /**
     * Test 3b: Halaman Rencana Bawa Stok Pra-Kunjungan menampilkan muatan motor
     */
    public function test_admin_rekomendasi_page_displays_clean_pra_kunjungan_plan(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get('/admin/rekomendasi');

        $response->assertStatus(200);
        $response->assertSee('Rencana Bawa Stok Hari Ini');
        $response->assertSee('Total Muatan Motor');
        $response->assertSee('Pra-Kunjungan');
    }

    /**
     * Test 4: Portal Warung Mitra memerlukan autentikasi PIN
     */
    public function test_warung_portal_requires_pin_authentication(): void
    {
        $warung = Warung::where('kode_unik', 'WRG-BERKAH')->first();
        $this->assertNotNull($warung);

        // Akses langsung tanpa PIN diredirect ke portal index
        $response = $this->get('/portal/' . $warung->kode_unik);
        $response->assertRedirect('/portal');

        // Login dengan PIN yang benar
        $loginResponse = $this->post('/portal/auth', [
            'warung_id' => $warung->id,
            'pin' => $warung->pin,
        ]);
        $loginResponse->assertRedirect('/portal/' . $warung->kode_unik);

        // Setelah login / ada session, halaman portal dapat dibuka
        $portalResponse = $this->withSession(['portal_warung_id' => $warung->id])
                               ->get('/portal/' . $warung->kode_unik);
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee($warung->nama_warung);
        $portalResponse->assertSee($warung->nama_pemilik);
        $portalResponse->assertSee('Form Pengajuan Tambahan Stok');
    }

    /**
     * Test 5: Warung Mitra dapat mengajukan tambahan stok
     */
    public function test_warung_mitra_can_submit_restock_request(): void
    {
        $warung = Warung::where('kode_unik', 'WRG-BAROKAH')->first();

        $response = $this->withSession(['portal_warung_id' => $warung->id])
                         ->post('/portal/' . $warung->kode_unik . '/pengajuan', [
            'tanggal_pengajuan' => now()->toDateString(),
            'jumlah_diminta' => 35,
            'catatan' => 'Minta tambahan 35 bungkus keripik pisang manis gurih.',
        ]);

        $response->assertRedirect('/portal/' . $warung->kode_unik);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengajuan_stoks', [
            'warung_id' => $warung->id,
            'jumlah_diminta' => 35,
            'status' => 'pending',
            'catatan' => 'Minta tambahan 35 bungkus keripik pisang manis gurih.',
        ]);
    }

    /**
     * Test 5b: Warung Mitra dapat mengonfirmasi sisa stok langsung dari portal
     */
    public function test_warung_mitra_can_submit_stock_confirmation_from_portal(): void
    {
        $warung = Warung::where('kode_unik', 'WRG-BAROKAH')->first();

        $response = $this->withSession(['portal_warung_id' => $warung->id])
                         ->post('/portal/' . $warung->kode_unik . '/konfirmasi', [
            'confirmation_date' => now()->toDateString(),
            'reported_remaining_stock' => 12,
            'notes' => 'Stok sisa di toples tinggal 12 bungkus.',
        ]);

        $response->assertRedirect('/portal/' . $warung->kode_unik);
        $response->assertSessionHas('success');

        $warung->refresh();
        $this->assertEquals(12, $warung->stok_saat_ini);

        // Periksa tampilan portal menampilkan komponen dashboard warung yang direvisi
        $portalResponse = $this->withSession(['portal_warung_id' => $warung->id])
                               ->get('/portal/' . $warung->kode_unik);
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Stok Terakhir Dikonfirmasi');
        $portalResponse->assertSee('Status Konfirmasi Stok');
        $portalResponse->assertSee('Jadwal Distribusi Berikutnya');
        $portalResponse->assertSee('Konfirmasi Stok');
        $portalResponse->assertSee('Ajukan Permintaan Stok');
    }

    /**
     * Test 6: Input Data Kunjungan & Kalkulasi Stok Otomatis (Sisa Stok = Stok Awal - Produk Terjual)
     */
    public function test_pemasok_input_kunjungan_and_stock_auto_recalculation(): void
    {
        $admin = User::first();
        $testCode = 'WRG-TEST-' . uniqid();
        $warung = Warung::create([
            'nama_warung' => 'Warung Tes Kalkulasi',
            'nama_pemilik' => 'Pak Budi',
            'alamat' => 'Jl. Kaliurang KM 5',
            'kontak' => '081299998888',
            'kode_unik' => $testCode,
            'pin' => '1234',
            'stok_saat_ini' => 20, // Stok awal di warung = 20 pcs
            'harga_titip' => 12000,
            'harga_jual' => 15000,
        ]);

        // Pemasok datang: Cek fisik barang laku 15 pcs, lalu titip baru 25 pcs
        // Kalkulasi:
        // Sisa fisik = 20 - 15 = 5 pcs
        // Stok baru akhir = 5 + 25 = 30 pcs
        // Pendapatan = 15 x Rp 12.000 = Rp 180.000
        $response = $this->actingAs($admin)->post('/admin/kunjungan', [
            'warung_id' => $warung->id,
            'tanggal_cek' => now()->toDateString(),
            'jumlah_laku' => 15,
            'jumlah_titip' => 25,
            'catatan' => 'Pengecekan fisik dan pengisian stok baru.',
        ]);

        $warung->refresh();

        // Verifikasi sisa stok di warung otomatis menjadi 30 pcs
        $this->assertEquals(30, $warung->stok_saat_ini);

        // Verifikasi transaksi tercatat di database
        $this->assertDatabaseHas('stok_penjualans', [
            'warung_id' => $warung->id,
            'stok_sebelumnya' => 20,
            'jumlah_laku' => 15,
            'jumlah_titip' => 25,
            'sisa_stok' => 30,
            'total_pendapatan' => 180000,
        ]);
    }

    /**
     * Test 7: Fitur Rekomendasi Stok
     */
    public function test_rekomendasi_stok_calculation(): void
    {
        $warung = Warung::where('kode_unik', 'WRG-BERKAH')->first();
        $rekomendasi = $warung->rekomendasi_stok;

        $this->assertIsArray($rekomendasi);
        $this->assertArrayHasKey('rata_rata_laku', $rekomendasi);
        $this->assertArrayHasKey('target_ideal', $rekomendasi);
        $this->assertArrayHasKey('rekomendasi_titip', $rekomendasi);
        $this->assertArrayHasKey('status_urgensi', $rekomendasi);
        $this->assertArrayHasKey('keterangan', $rekomendasi);
    }

    /**
     * Test 8: Input Sesi Konsinyasi (Taro 40 pcs, Sisa 8 pcs) -> Terjual 32 pcs & Keputusan Stok Laris
     */
    public function test_input_sesi_konsinyasi_dengan_taro_dan_sisa(): void
    {
        $admin = User::first();
        $testCode = 'WRG-SESI-' . uniqid();
        $warung = Warung::create([
            'nama_warung' => 'Warung Sesi Test',
            'nama_pemilik' => 'Bu Hani',
            'alamat' => 'Jl. Gejayan No. 10',
            'kontak' => '081233334444',
            'kode_unik' => $testCode,
            'pin' => '1234',
            'stok_saat_ini' => 40,
            'harga_titip' => 12000,
            'harga_jual' => 15000,
        ]);

        // Distributor datang: Sesi awal ditaro 40 pcs, dicek sisa fisik 8 pcs.
        // Terjual = 40 - 8 = 32 pcs (80% laku).
        // Keputusan: Laris Manis -> Rekomendasi Naikkan Titip: 50 pcs.
        // Titip baru untuk sesi depan = 45 pcs.
        // Stok akhir warung = 8 + 45 = 53 pcs.
        $response = $this->actingAs($admin)->post('/admin/kunjungan', [
            'warung_id' => $warung->id,
            'tanggal_cek' => now()->toDateString(),
            'stok_ditaro' => 40,
            'sisa_fisik' => 8,
            'jumlah_titip' => 45,
            'catatan' => 'Sesi 1: Penjualan sangat laris.',
        ]);

        $warung->refresh();
        $this->assertEquals(53, $warung->stok_saat_ini);

        $this->assertDatabaseHas('stok_penjualans', [
            'warung_id' => $warung->id,
            'stok_sebelumnya' => 40,
            'sisa_fisik' => 8,
            'jumlah_laku' => 32,
            'jumlah_titip' => 45,
            'sisa_stok' => 53,
        ]);

        $latestLog = StokPenjualan::where('warung_id', $warung->id)->first();
        $this->assertNotNull($latestLog);
        $this->assertEquals('laris', $latestLog->keputusan['status']);
        $this->assertStringContainsString('Naikkan Titip', $latestLog->keputusan['ringkasan']);
    }

    /**
     * Test 9: Algoritma Keputusan Stok Per Sesi
     */
    public function test_algoritma_keputusan_stok(): void
    {
        // 1. Habis total: Taro 40, Sisa 0
        $d1 = StokPenjualan::hitungKeputusan(40, 0);
        $this->assertEquals('habis', $d1['status']);
        $this->assertGreaterThanOrEqual(40, $d1['rekomendasi_titip']);

        // 2. Laris: Taro 40, Sisa 5 (Terjual 35 / 88%)
        $d2 = StokPenjualan::hitungKeputusan(40, 5);
        $this->assertEquals('laris', $d2['status']);
        $this->assertGreaterThan(40, $d2['rekomendasi_titip']);

        // 3. Stabil: Taro 40, Sisa 15 (Terjual 25 / 62% -> saran 30)
        $d3 = StokPenjualan::hitungKeputusan(40, 15);
        $this->assertEquals('stabil', $d3['status']);
        $this->assertEquals(30, $d3['rekomendasi_titip']);

        // 3b. Stabil (Skenario User: Taro 40, Sisa 20, Terjual 20 / 50% -> saran 25 pcs, bukan 40 pcs)
        $dUser = StokPenjualan::hitungKeputusan(40, 20);
        $this->assertEquals('stabil', $dUser['status']);
        $this->assertEquals(25, $dUser['rekomendasi_titip']);

        // 4. Lambat: Taro 40, Sisa 25 (Terjual 15 / 38%)
        $d4 = StokPenjualan::hitungKeputusan(40, 25);
        $this->assertEquals('lambat', $d4['status']);
        $this->assertLessThan(40, $d4['rekomendasi_titip']);

        // 5. Macet: Taro 40, Sisa 38 (Terjual 2 / 5%)
        $d5 = StokPenjualan::hitungKeputusan(40, 38);
        $this->assertEquals('macet', $d5['status']);
        $this->assertEquals(0, $d5['rekomendasi_titip']);
    }

    /**
     * Test 10: Form Input Data Stok & Penjualan renders properly
     */
    public function test_create_sesi_form_renders_properly(): void
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get('/admin/kunjungan/create');
        $response->assertStatus(200);
        $response->assertSee('Input Data Stok & Penjualan');
        $response->assertSee('Stok yang Dititipkan');
        $response->assertSee('Sisa Stok yang Ada Sekarang');
        $response->assertSee('Titip Baru');
        $response->assertSee('data-stok-lalu', false);
    }

    /**
     * Test 11: Nota / Bukti Sesi Konsinyasi renders properly
     */
    public function test_nota_sesi_renders_properly(): void
    {
        $admin = User::first();
        $sesi = StokPenjualan::first();
        $this->assertNotNull($sesi);

        $response = $this->actingAs($admin)->get('/admin/kunjungan/' . $sesi->id . '/nota');
        $response->assertStatus(200);
        $response->assertSee('Bukti Sesi Konsinyasi');
        $response->assertSee('Taro (Awal)');
        $response->assertSee('Sisa (Dicek)');
        $response->assertSee('Keputusan Stok');
    }
}
