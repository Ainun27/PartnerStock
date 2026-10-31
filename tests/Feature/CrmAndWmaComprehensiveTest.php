<?php

namespace Tests\Feature;

use App\Models\CrmAlert;
use App\Models\CrmFollowUp;
use App\Models\Product;
use App\Models\Restock;
use App\Models\SalesRecord;
use App\Models\StockConfirmation;
use App\Models\StockRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Warung;
use App\Services\CrmService;
use App\Services\RecommendationService;
use App\Services\WmaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CrmAndWmaComprehensiveTest extends TestCase
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
     * Skenario 6: Perhitungan WMA dengan contoh angka spesifikasi (Section 6)
     * X1 = 10, X2 = 15, X3 = 20 -> F = 16.5, ceil -> 17
     */
    public function test_wma_calculation_exact_formula_matches_specification(): void
    {
        $wmaService = app(WmaService::class);
        $result = $wmaService->calculate([10, 15, 20], [0.20, 0.30, 0.50]);

        $this->assertEquals(16.5, $result['raw_forecast']);
        $this->assertEquals(17, $result['forecast']);
        $this->assertEquals('cukup', $result['data_status']);
        $this->assertEquals('meningkat', $result['trend']);
        $this->assertEquals(3, $result['periods_count']);
    }

    /**
     * Skenario 7: Penanganan data historis yang kurang dari 3 periode
     */
    public function test_wma_handles_insufficient_periods_gracefully(): void
    {
        $wmaService = app(WmaService::class);

        // 0 Periode
        $res0 = $wmaService->calculate([]);
        $this->assertEquals(0, $res0['forecast']);
        $this->assertEquals('belum_cukup', $res0['data_status']);
        $this->assertEquals(0, $res0['periods_count']);

        // 1 Periode: Bobot normalisasi 100%
        $res1 = $wmaService->calculate([15]);
        $this->assertEquals(15, $res1['forecast']);
        $this->assertEquals('belum_cukup', $res1['data_status']);
        $this->assertEquals(1, $res1['periods_count']);

        // 2 Periode: Bobot normalisasi 40% & 60%
        // (0.40 * 10) + (0.60 * 20) = 4 + 12 = 16
        $res2 = $wmaService->calculate([10, 20]);
        $this->assertEquals(16, $res2['forecast']);
        $this->assertEquals('belum_cukup', $res2['data_status']);
        $this->assertEquals(2, $res2['periods_count']);
    }

    /**
     * Skenario 9: Perbedaan perhitungan Mode A dan Mode B konsinyasi
     * Mode A: R = F
     * Mode B: R = max(0, F - S)
     */
    public function test_consignment_mode_a_and_mode_b_recommendations(): void
    {
        $recService = app(RecommendationService::class);

        // Buat warung uji dengan riwayat [10, 15, 20] -> F = 17, dan sisa stok S = 5
        $warung = Warung::create([
            'nama_warung' => 'Warung Uji Konsinyasi Mode',
            'owner_name' => 'Pak Test',
            'phone' => '089999999999',
            'alamat' => 'Yogyakarta',
            'kode_unik' => 'WRG-MODE-TEST',
            'pin' => '1234',
            'stok_saat_ini' => 5,
            'partnership_status' => 'aktif',
            'is_aktif' => true,
        ]);

        // Simpan konfirmasi stok tersisa S = 5
        StockConfirmation::create([
            'supplier_id' => 1,
            'warung_id' => $warung->id,
            'confirmation_date' => now()->toDateString(),
            'reported_remaining_stock' => 5,
            'data_status' => 'menunggu_pemeriksaan',
        ]);

        // Masukkan 3 periode penjualan: 10, 15, 20
        $dates = [now()->subDays(28), now()->subDays(14), now()->subDays(1)];
        $sales = [10, 15, 20];
        foreach ($sales as $idx => $s) {
            SalesRecord::create([
                'supplier_id' => 1,
                'warung_id' => $warung->id,
                'period_start' => $dates[$idx]->copy()->subDays(14)->toDateString(),
                'period_end' => $dates[$idx]->toDateString(),
                'distributed_quantity' => 20,
                'remaining_stock' => 20 - $s,
                'sold_quantity' => $s,
                'verified_at' => $dates[$idx],
            ]);
        }

        // Hitung Mode A (Stok lama ditarik seluruhnya & diganti baru)
        $recA = $recService->getRecommendationForWarung($warung, 'A');
        $this->assertEquals(17, $recA['forecast_rounded']);
        $this->assertEquals(17, $recA['recommended_quantity'], 'Mode A harus merekomendasikan F = 17');

        // Hitung Mode B (Stok lama tetap dijual: R = max(0, F - S) = 17 - 5 = 12)
        $recB = $recService->getRecommendationForWarung($warung, 'B');
        $this->assertEquals(17, $recB['forecast_rounded']);
        $this->assertEquals(12, $recB['recommended_quantity'], 'Mode B harus merekomendasikan F - S = 17 - 5 = 12');
    }

    /**
     * Skenario 11 & 13: Pemicu Churn Prevention ketika penjualan turun 3 periode & pencegahan duplikasi
     */
    public function test_churn_prevention_triggers_on_3_consecutive_drops_and_avoids_duplicates(): void
    {
        $crmService = app(CrmService::class);

        // Buat warung uji dengan penjualan turun 3 periode: 25 -> 18 -> 10
        $warung = Warung::create([
            'nama_warung' => 'Warung Drop Test',
            'owner_name' => 'Pak Drop',
            'phone' => '088811122233',
            'alamat' => 'Sleman',
            'kode_unik' => 'WRG-DROP-TEST',
            'pin' => '1234',
            'stok_saat_ini' => 10,
            'partnership_status' => 'aktif',
            'is_aktif' => true,
        ]);

        $dates = [now()->subDays(28), now()->subDays(14), now()->subDays(2)];
        $drops = [25, 18, 10];
        foreach ($drops as $idx => $s) {
            SalesRecord::create([
                'supplier_id' => 1,
                'warung_id' => $warung->id,
                'period_start' => $dates[$idx]->copy()->subDays(14)->toDateString(),
                'period_end' => $dates[$idx]->toDateString(),
                'distributed_quantity' => 25,
                'remaining_stock' => 25 - $s,
                'sold_quantity' => $s,
                'verified_at' => $dates[$idx],
            ]);
        }

        // Evaluasi aturan CRM
        $alerts1 = $crmService->evaluateWarung($warung);
        $this->assertNotEmpty($alerts1);

        $alert = CrmAlert::where('warung_id', $warung->id)
            ->where('category', 'churn_prevention')
            ->where('trigger_type', 'penurunan_penjualan_3_periode')
            ->first();

        $this->assertNotNull($alert);
        $this->assertStringContainsString('penurunan selama 3 periode', $alert->description);

        // Evaluasi ulang: Alert tidak boleh diduplikasi (Deduplication Check)
        $initialAlertCount = CrmAlert::where('warung_id', $warung->id)->count();
        $crmService->evaluateWarung($warung);
        $secondAlertCount = CrmAlert::where('warung_id', $warung->id)->count();

        $this->assertEquals($initialAlertCount, $secondAlertCount, 'Alert tidak boleh diduplikasi jika alert sebelumnya masih aktif');
    }

    /**
     * Skenario 14: Pencatatan Tindak Lanjut CRM (Follow Up)
     */
    public function test_recording_crm_follow_up(): void
    {
        $crmService = app(CrmService::class);
        $alert = CrmAlert::first();
        if (!$alert) {
            $crmService->evaluateAll();
            $alert = CrmAlert::first();
        }
        if (!$alert) {
            $warung = Warung::first();
            $alert = CrmAlert::create([
                'warung_id' => $warung ? $warung->id : 1,
                'category' => 'churn_prevention',
                'trigger_type' => 'penurunan_penjualan_3_periode',
                'description' => 'Test churn alert',
                'status' => 'perlu_dihubungi',
            ]);
        }
        $this->assertNotNull($alert);

        $followUp = $crmService->recordFollowUp(
            $alert,
            'whatsapp',
            'Telah menghubungi pemilik warung via WhatsApp. Kendala display sudah diposisikan ulang ke depan.',
            'Cek ulang display saat pengiriman lusa.',
            'selesai'
        );

        $this->assertInstanceOf(CrmFollowUp::class, $followUp);
        $this->assertEquals('selesai', $alert->fresh()->status);
        $this->assertNotNull($alert->fresh()->resolved_at);
    }

    /**
     * Skenario 1: Pembatasan akses & Multi-Role Authentication
     */
    public function test_role_authorization_and_authentication(): void
    {
        $admin = User::where('role', 'supplier')->first();
        $this->assertTrue($admin->isSupplier());

        // Supplier dapat mengakses dashboard dan CRM
        $response = $this->actingAs($admin)->get('/admin/crm');
        $response->assertStatus(200);
        $response->assertSee('Pusat CRM');

        // Pengaturan Sistem
        $settingResp = $this->actingAs($admin)->get('/admin/pengaturan');
        $settingResp->assertStatus(200);
        $settingResp->assertSee('Pengaturan Parameter Sistem');
    }

    /**
     * Skenario 3 & Aturan Bisnis: Produk Tunggal Pak Aceng
     */
    public function test_single_product_business_rule(): void
    {
        $admin = User::first();
        $product = Product::first();
        $this->assertNotNull($product);

        // Coba ganti nama produk menjadi varian lain (harus ditolak oleh controller)
        $response = $this->actingAs($admin)->put('/admin/produk/' . $product->id, [
            'product_code' => 'KRP-BALADO',
            'name' => 'Keripik Pisang Rasa Balado Pedas',
            'unit' => 'bungkus',
            'agreed_price' => 15000,
            'cost_price' => 12000,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Keripik Pisang Original', $product->fresh()->name);
    }

    /**
     * Skenario 5 & 8: Konfirmasi Stok Pra-Kunjungan WhatsApp H-2
     */
    public function test_stock_confirmation_workflow(): void
    {
        $admin = User::first();
        $warung = Warung::first();

        $response = $this->actingAs($admin)->post('/admin/konfirmasi-stok', [
            'warung_id' => $warung->id,
            'confirmation_date' => now()->toDateString(),
            'reported_remaining_stock' => 7,
            'notes' => 'Konfirmasi via WhatsApp H-2 sisa 7 bungkus.',
        ]);

        $response->assertRedirect('/admin/konfirmasi-stok');
        $this->assertEquals(7, $warung->fresh()->stok_saat_ini);
    }

    /**
     * Skenario 16: Penyaringan Laporan Berdasarkan Tanggal dan Warung
     */
    public function test_report_filtering(): void
    {
        $admin = User::first();
        $warung = Warung::first();

        $response = $this->actingAs($admin)->get('/admin/laporan?warung_id=' . $warung->id . '&start_date=' . now()->subMonth()->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee('Laporan & Rekapitulasi');
        $response->assertSee('Total Dititipkan');
        $response->assertSee('Total Terjual');
        $response->assertSee('Total Ditarik');
    }
}
