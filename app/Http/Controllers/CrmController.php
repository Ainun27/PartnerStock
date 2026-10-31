<?php

namespace App\Http\Controllers;

use App\Models\CrmAlert;
use App\Models\CrmFollowUp;
use App\Models\PengajuanStok;
use App\Models\Warung;
use App\Services\CrmService;
use App\Services\RecommendationService;
use App\Services\WmaService;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    protected CrmService $crmService;
    protected WmaService $wmaService;
    protected RecommendationService $recService;

    public function __construct(CrmService $crmService, WmaService $wmaService, RecommendationService $recService)
    {
        $this->crmService = $crmService;
        $this->wmaService = $wmaService;
        $this->recService = $recService;
    }

    /**
     * Tampilkan Pusat CRM dengan 3 Aturan CRM:
     * 1. Data Mining
     * 2. Churn Prevention
     * 3. Reorder Recommendation
     */
    public function index(Request $request)
    {
        // Jalankan evaluasi aturan CRM otomatis (deteksi churn & pattern baru tanpa duplikasi)
        $this->crmService->evaluateAll();

        $activeTab = $request->query('tab', 'churn_prevention');
        $statusFilter = $request->query('status', 'all');

        // 1. Data Mining: pola WMA & tren semua warung
        $warungs = Warung::where('partnership_status', 'aktif')->where('is_aktif', true)->orderBy('nama_warung')->get();
        $dataMiningList = [];
        foreach ($warungs as $warung) {
            $analysis = $this->wmaService->calculateForWarung($warung);
            $rec = $this->recService->getRecommendationForWarung($warung);
            $dataMiningList[] = [
                'warung' => $warung,
                'analysis' => $analysis,
                'recommendation' => $rec,
            ];
        }

        // 2. Churn Prevention Alerts & Riwayat Tindak Lanjut
        $churnQuery = CrmAlert::with(['warung', 'followUps' => function ($q) {
            $q->orderBy('follow_up_date', 'desc');
        }])->where('category', 'churn_prevention');

        if ($statusFilter !== 'all') {
            $churnQuery->where('status', $statusFilter);
        }

        $churnAlerts = $churnQuery->orderByRaw("CASE WHEN status = 'selesai' THEN 2 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Reorder Recommendation
        $reorderRecs = $this->recService->getAllRecommendations();
        $pendingStockRequests = PengajuanStok::with('warung')
            ->where('status', 'pending')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        $summary = $this->crmService->getCrmSummary();

        return view('admin.crm.index', compact(
            'activeTab',
            'statusFilter',
            'dataMiningList',
            'churnAlerts',
            'reorderRecs',
            'pendingStockRequests',
            'summary'
        ));
    }

    /**
     * Catat tindak lanjut atas peringatan Churn Prevention
     */
    public function storeFollowUp(Request $request, $alertId)
    {
        $alert = CrmAlert::findOrFail($alertId);

        $validated = $request->validate([
            'contact_method' => 'required|in:whatsapp,telepon,kunjungan_langsung',
            'result_notes' => 'required|string|max:1000',
            'next_action' => 'nullable|string|max:500',
            'status' => 'required|in:perlu_ditinjau,perlu_dihubungi,sudah_dihubungi,dalam_tindak_lanjut,selesai',
        ]);

        $this->crmService->recordFollowUp(
            $alert,
            $validated['contact_method'],
            $validated['result_notes'],
            $validated['next_action'] ?? null,
            $validated['status']
        );

        return redirect()->route('admin.crm.index', ['tab' => 'churn_prevention'])
            ->with('success', 'Catatan tindak lanjut berhasil disimpan dan status peringatan diperbarui.');
    }
}
