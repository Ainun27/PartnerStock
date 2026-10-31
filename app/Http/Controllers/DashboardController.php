<?php

namespace App\Http\Controllers;

use App\Models\CrmAlert;
use App\Models\PengajuanStok;
use App\Models\Restock;
use App\Models\SalesRecord;
use App\Models\StockConfirmation;
use App\Models\StockRequest;
use App\Models\StokPenjualan;
use App\Models\Warung;
use App\Services\CrmService;
use App\Services\RecommendationService;
use App\Services\WmaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected CrmService $crmService;
    protected RecommendationService $recService;
    protected WmaService $wmaService;

    public function __construct(
        CrmService $crmService,
        RecommendationService $recService,
        WmaService $wmaService
    ) {
        $this->crmService = $crmService;
        $this->recService = $recService;
        $this->wmaService = $wmaService;
    }

    public function index()
    {
        // Jalankan evaluasi aturan CRM otomatis
        $this->crmService->evaluateAll();

        $warungs = Warung::where('partnership_status', 'aktif')
            ->where('is_aktif', true)
            ->get();

        $totalWarung = $warungs->count();
        $totalStokDiWarung = (int) $warungs->sum('stok_saat_ini');

        // Metrik periode berjalan (30 hari terakhir)
        $startPeriod = Carbon::now()->subDays(30);

        $distribusiBerjalan = (int) Restock::where('distribution_date', '>=', $startPeriod)->sum('quantity_delivered');
        $terjualBerjalan = (int) SalesRecord::where('period_end', '>=', $startPeriod)->sum('sold_quantity');
        $ditarikBerjalan = (int) Restock::where('distribution_date', '>=', $startPeriod)->sum('old_stock_withdrawn');

        // Fallback jika baru migrasi
        if ($terjualBerjalan === 0) {
            $terjualBerjalan = (int) StokPenjualan::where('tanggal_cek', '>=', $startPeriod)->sum('jumlah_laku');
        }
        if ($distribusiBerjalan === 0) {
            $distribusiBerjalan = (int) StokPenjualan::where('tanggal_titip', '>=', $startPeriod)->sum('jumlah_titip');
        }

        $totalTerjual = (int) SalesRecord::sum('sold_quantity');
        if ($totalTerjual === 0) {
            $totalTerjual = (int) StokPenjualan::sum('jumlah_laku');
        }

        $totalPendapatan = SalesRecord::with('warung')->get()->sum(function ($s) {
            $harga = ($s->warung && $s->warung->harga_titip > 0) ? $s->warung->harga_titip : 12000;
            return $s->sold_quantity * $harga;
        });
        if ($totalPendapatan === 0) {
            $totalPendapatan = StokPenjualan::with('warung')->get()->sum(function ($sp) {
                $harga = ($sp->warung && $sp->warung->harga_titip > 0) ? $sp->warung->harga_titip : 12000;
                return $sp->jumlah_laku * $harga;
            });
        }

        // Peringatan Churn Prevention yang belum selesai
        $churnAlertsPending = CrmAlert::with('warung')
            ->where('category', 'churn_prevention')
            ->where('status', '!=', 'selesai')
            ->orderBy('created_at', 'desc')
            ->get();

        // Warung yang belum mengonfirmasi stok (status: Perlu Konfirmasi Stok)
        $warungBelumKonfirmasi = $warungs->filter(function ($w) {
            return $w->needs_stock_confirmation;
        })->values();

        // Permintaan tambahan stok yang menunggu persetujuan
        $pendingStockRequests = StockRequest::with('warung')
            ->where('status', 'menunggu')
            ->orderBy('requested_at', 'desc')
            ->get();

        $pengajuanPending = PengajuanStok::with('warung')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $pengajuanDiprosesCount = PengajuanStok::where('status', 'diproses')->count();

        // Rekomendasi WMA & Rencana Pengiriman Berikutnya
        $recsSummary = $this->recService->getAllRecommendations();
        $rekomendasiList = collect($recsSummary['items'])->map(function ($item) use ($warungs) {
            $warung = $warungs->firstWhere('id', $item['warung_id']);
            return array_merge($item, [
                'warung' => $warung,
                'stok_saat_ini' => $item['remaining_stock'],
                'rata_rata_laku' => $warung ? $warung->rata_rata_laku : 0,
                'target_ideal' => $item['recommended_quantity'],
                'rekomendasi_titip' => $item['approved_quantity'],
                'estimasi_bawa' => $item['approved_quantity'],
                'status_urgensi' => ($item['data_status'] === 'perlu_konfirmasi') ? 'kritis' : 'aman',
                'keterangan' => $item['explanation'],
                'ringkasan' => "Rekomendasi WMA: {$item['forecast_rounded']} bungkus ({$item['mode_label']})",
                'label' => $item['wma_details']['trend_label'] ?? 'WMA Aktif',
                'badge_color' => ($item['data_status'] === 'cukup') ? 'badge-success' : 'badge-warning',
                'taro_terakhir' => $item['recommended_quantity'],
                'sisa_terakhir' => $item['remaining_stock'],
                'laku_terakhir' => $warung && $warung->sesi_terakhir ? (int) $warung->sesi_terakhir->jumlah_laku : 0,
                'alasan_pra_produksi' => $item['explanation'],
                'terakhir_dicek' => $item['latest_confirmation_date'] ?: 'Belum ada konfirmasi',
            ]);
        });

        $totalBawaHariIni = $recsSummary['total_approved'] ?: $recsSummary['total_recommended'];
        $poPendingTotal = (int) PengajuanStok::whereIn('status', ['pending', 'diproses'])->sum('jumlah_diminta');
        $totalStokHarusDibawa = $totalBawaHariIni + $poPendingTotal;

        // Kunjungan / distribusi terbaru
        $kunjunganTerbaru = Restock::with('warung')
            ->orderBy('distribution_date', 'desc')
            ->limit(6)
            ->get();

        // Data Grafik Penjualan, Stok & Estimasi WMA per Warung (Chart.js)
        $chartLabels = $warungs->pluck('nama_warung')->toArray();
        $chartStok = $warungs->pluck('stok_saat_ini')->toArray();
        $chartTerjual = $warungs->map(function ($w) {
            return (int) $w->total_terjual;
        })->toArray();
        $chartWma = $warungs->map(function ($w) use ($rekomendasiList) {
            $rec = $rekomendasiList->firstWhere('warung_id', $w->id);
            return $rec ? (int) ($rec['forecast_rounded'] ?? $rec['target_ideal'] ?? 0) : 0;
        })->toArray();

        return view('admin.dashboard', compact(
            'totalWarung',
            'totalStokDiWarung',
            'distribusiBerjalan',
            'terjualBerjalan',
            'ditarikBerjalan',
            'totalTerjual',
            'totalPendapatan',
            'churnAlertsPending',
            'warungBelumKonfirmasi',
            'pendingStockRequests',
            'pengajuanPending',
            'pengajuanDiprosesCount',
            'poPendingTotal',
            'totalStokHarusDibawa',
            'rekomendasiList',
            'totalBawaHariIni',
            'kunjunganTerbaru',
            'chartLabels',
            'chartStok',
            'chartTerjual',
            'chartWma',
            'recsSummary'
        ));
    }
}
