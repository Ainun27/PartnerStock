<?php

namespace App\Http\Controllers;

use App\Models\Warung;
use App\Services\RecommendationService;
use App\Services\WmaService;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    protected RecommendationService $recService;
    protected WmaService $wmaService;

    public function __construct(RecommendationService $recService, WmaService $wmaService)
    {
        $this->recService = $recService;
        $this->wmaService = $wmaService;
    }

    public function index(Request $request)
    {
        $mode = $request->query('mode', $this->recService->getDefaultConsignmentMode());
        $summary = $this->recService->getAllRecommendations(null, $mode);

        $warungs = Warung::where('is_aktif', true)->get();

        $rekomendasiData = collect($summary['items'])->map(function ($item) use ($warungs) {
            $warung = $warungs->firstWhere('id', $item['warung_id']);
            $recOld = $warung ? $warung->rekomendasi_stok : [];

            $urgensi = match ($item['data_status']) {
                'perlu_konfirmasi' => 'kritis',
                'belum_cukup' => 'sedang',
                default => 'aman',
            };

            return [
                'warung' => $warung,
                'stok_saat_ini' => $item['remaining_stock'],
                'rata_rata_laku' => $warung ? $warung->rata_rata_laku : 0,
                'target_ideal' => $item['forecast_rounded'],
                'rekomendasi_titip' => $item['recommended_quantity'],
                'approved_quantity' => $item['approved_quantity'],
                'estimasi_bawa' => $item['approved_quantity'] ?: $item['recommended_quantity'],
                'status_urgensi' => $urgensi,
                'keterangan' => $item['explanation'],
                'label' => $item['wma_details']['trend_label'] ?? 'WMA Aktif',
                'badge_color' => match ($item['data_status']) {
                    'cukup' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'belum_cukup' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'perlu_konfirmasi' => 'bg-red-100 text-red-800 border-red-200',
                    default => 'bg-blue-100 text-blue-800 border-blue-200',
                },
                'taro_terakhir' => $item['recommended_quantity'],
                'sisa_terakhir' => $item['remaining_stock'],
                'laku_terakhir' => $warung && $warung->sesi_terakhir ? (int) $warung->sesi_terakhir->jumlah_laku : 0,
                'riwayat_laku_list' => $recOld['riwayat_laku_list'] ?? [],
                'po_pending' => $recOld['po_pending'] ?? 0,
                'po_tambahan' => $recOld['po_pending'] ?? 0,
                'total_harus_dibawa' => ($item['approved_quantity'] ?: $item['recommended_quantity']) + ($recOld['po_pending'] ?? 0),
                'alasan_pra_produksi' => $item['explanation'],
                'terakhir_dicek' => $item['latest_confirmation_date'] ?: 'Belum pernah dikonfirmasi',
                'consignment_mode' => $item['consignment_mode'],
                'mode_label' => $item['mode_label'],
                'wma_details' => $item['wma_details'],
            ];
        });

        // Urutkan: kritis lebih dahulu, lalu sedang, lalu aman
        $rekomendasiData = $rekomendasiData->sortByDesc(function ($item) {
            $weight = match ($item['status_urgensi']) {
                'kritis' => 3,
                'sedang' => 2,
                'aman' => 1,
                default => 0,
            };
            return $weight * 1000 + $item['estimasi_bawa'];
        })->values();

        $totalKritis = $rekomendasiData->where('status_urgensi', 'kritis')->count();
        $totalSedang = $rekomendasiData->where('status_urgensi', 'sedang')->count();
        $totalAman = $rekomendasiData->where('status_urgensi', 'aman')->count();

        // Total muatan motor/mobil persiapan yang disetujui Pak Aceng
        $totalPcsDisarankan = $rekomendasiData->sum('estimasi_bawa');
        $totalPOAktif = $rekomendasiData->sum('po_pending');
        $totalHarusDibawa = $totalPcsDisarankan + $totalPOAktif;

        return view('admin.rekomendasi.index', compact(
            'mode',
            'rekomendasiData',
            'totalKritis',
            'totalSedang',
            'totalAman',
            'totalPcsDisarankan',
            'totalPOAktif',
            'totalHarusDibawa',
            'summary'
        ));
    }

    /**
     * Setujui / sesuaikan jumlah rekomendasi pengiriman
     */
    public function approve(Request $request, $warungId)
    {
        $warung = Warung::findOrFail($warungId);

        $validated = $request->validate([
            'approved_quantity' => 'required|integer|min:0|max:200',
            'consignment_mode' => 'required|in:A,B',
            'reason' => 'nullable|string|max:500',
        ]);

        $this->recService->saveApproval(
            $warung,
            $validated['approved_quantity'],
            $validated['consignment_mode'],
            $validated['reason'] ?? null
        );

        return back()->with('success', "Persetujuan jumlah bawa untuk {$warung->nama_warung} ({$validated['approved_quantity']} bungkus) berhasil disimpan.");
    }
}
