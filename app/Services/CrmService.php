<?php

namespace App\Services;

use App\Models\CrmAlert;
use App\Models\CrmFollowUp;
use App\Models\PengajuanStok;
use App\Models\StockRequest;
use App\Models\SystemSetting;
use App\Models\Warung;
use Carbon\Carbon;

class CrmService
{
    protected WmaService $wmaService;
    protected RecommendationService $recService;

    public function __construct(WmaService $wmaService, RecommendationService $recService)
    {
        $this->wmaService = $wmaService;
        $this->recService = $recService;
    }

    /**
     * Jalankan evaluasi aturan CRM untuk seluruh warung aktif dan perbarui peringatan CRM
     *
     * @param int|null $supplierId
     * @return array
     */
    public function evaluateAll(?int $supplierId = null): array
    {
        $query = Warung::where('partnership_status', 'aktif')->where('is_aktif', true);
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $warungs = $query->get();
        $generatedAlerts = [];

        foreach ($warungs as $warung) {
            $alerts = $this->evaluateWarung($warung);
            if (!empty($alerts)) {
                $generatedAlerts = array_merge($generatedAlerts, $alerts);
            }
        }

        return $generatedAlerts;
    }

    /**
     * Evaluasi satu warung terhadap aturan Churn Prevention dan Data Mining
     *
     * @param Warung $warung
     * @return array<CrmAlert>
     */
    public function evaluateWarung(Warung $warung): array
    {
        $alerts = [];

        // 1. Pemicu Churn: Penjualan menurun 3 periode berturut-turut (X1 > X2 > X3)
        $wmaData = $this->wmaService->calculateForWarung($warung);
        if ($wmaData['data_status'] === 'cukup' && count($wmaData['sales_values']) >= 3) {
            $sales = $wmaData['sales_values'];
            $x1 = $sales[0];
            $x2 = $sales[1];
            $x3 = $sales[2];

            if ($x1 > $x2 && $x2 > $x3) {
                $triggerPeriod = date('Y-m');
                // Cek duplikasi: apakah sudah ada peringatan aktif untuk warung & pemicu ini yang belum selesai
                $existing = CrmAlert::where('warung_id', $warung->id)
                    ->where('category', 'churn_prevention')
                    ->where('trigger_type', 'penurunan_penjualan_3_periode')
                    ->where('status', '!=', 'selesai')
                    ->first();

                if (!$existing) {
                    $alert = CrmAlert::create([
                        'warung_id' => $warung->id,
                        'category' => 'churn_prevention',
                        'trigger_type' => 'penurunan_penjualan_3_periode',
                        'trigger_period' => $triggerPeriod,
                        'description' => "Penjualan aktual di {$warung->nama_warung} mengalami penurunan selama 3 periode berturut-turut ({$x1} pcs -> {$x2} pcs -> {$x3} pcs). Disarankan hubungi pemilik warung ({$warung->nama_pemilik}) untuk mengevaluasi kendala display atau perputaran produk.",
                        'evidence_data' => [
                            'sales_history' => [$x1, $x2, $x3],
                            'last_sales' => $x3,
                            'drop_percentage' => round((($x1 - $x3) / max(1, $x1)) * 100, 1),
                        ],
                        'status' => 'perlu_dihubungi',
                    ]);
                    $alerts[] = $alert;
                }
            }
        }

        // 2. Pemicu Churn: Tidak ada pengisian ulang dalam 2 siklus pengiriman rutin (> 28 hari)
        $deliveryInterval = $warung->delivery_interval_days ?: 14;
        $maxDormantDays = $deliveryInterval * 2; // Default 28 hari

        $lastSesi = $warung->stokPenjualans()->whereNotNull('tanggal_cek')->orderBy('tanggal_cek', 'desc')->first();
        $lastVisitDate = ($lastSesi && $lastSesi->tanggal_cek) ? Carbon::parse($lastSesi->tanggal_cek) : null;

        if ($lastVisitDate) {
            $daysSinceVisit = $lastVisitDate->diffInDays(now());
            if ($daysSinceVisit >= $maxDormantDays) {
                $existingDormant = CrmAlert::where('warung_id', $warung->id)
                    ->where('category', 'churn_prevention')
                    ->where('trigger_type', 'tanpa_restock_2_siklus')
                    ->where('status', '!=', 'selesai')
                    ->first();

                if (!$existingDormant) {
                    $alert = CrmAlert::create([
                        'warung_id' => $warung->id,
                        'category' => 'churn_prevention',
                        'trigger_type' => 'tanpa_restock_2_siklus',
                        'trigger_period' => date('Y-m'),
                        'description' => "Sudah {$daysSinceVisit} hari belum ada pengiriman ulang ke {$warung->nama_warung} (melebihi 2 siklus rutin {$maxDormantDays} hari). Cek apakah warung masih aktif beroperasi atau stok tersisa masih ada.",
                        'evidence_data' => [
                            'last_visit' => $lastVisitDate->format('Y-m-d'),
                            'days_inactive' => $daysSinceVisit,
                            'delivery_interval' => $deliveryInterval,
                        ],
                        'status' => 'perlu_ditinjau',
                    ]);
                    $alerts[] = $alert;
                }
            }
        }

        // 3. Peringatan Reorder: Warung dengan permintaan stok tambahan yang menunggu persetujuan
        $pendingRequests = PengajuanStok::where('warung_id', $warung->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingRequests->isNotEmpty()) {
            $existingReqAlert = CrmAlert::where('warung_id', $warung->id)
                ->where('category', 'reorder_recommendation')
                ->where('trigger_type', 'permintaan_stok_tambahan')
                ->where('status', '!=', 'selesai')
                ->first();

            if (!$existingReqAlert) {
                $totalRequested = $pendingRequests->sum('jumlah_diminta');
                $alert = CrmAlert::create([
                    'warung_id' => $warung->id,
                    'category' => 'reorder_recommendation',
                    'trigger_type' => 'permintaan_stok_tambahan',
                    'trigger_period' => date('Y-m'),
                    'description' => "{$warung->nama_warung} mengajukan permintaan stok tambahan ({$totalRequested} bungkus) di luar jadwal rutin. Periksa dan setujui jumlah yang akan dikirim.",
                    'evidence_data' => [
                        'requests_count' => $pendingRequests->count(),
                        'total_quantity' => $totalRequested,
                    ],
                    'status' => 'perlu_ditinjau',
                ]);
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    /**
     * Catat tindak lanjut atas suatu CRM Alert
     */
    public function recordFollowUp(
        CrmAlert $alert,
        string $contactMethod,
        string $resultNotes,
        ?string $nextAction = null,
        string $newStatus = 'selesai'
    ): CrmFollowUp {
        $followUp = CrmFollowUp::create([
            'crm_alert_id' => $alert->id,
            'supplier_id' => $alert->supplier_id,
            'warung_id' => $alert->warung_id,
            'follow_up_date' => now()->toDateString(),
            'contact_method' => $contactMethod,
            'result_notes' => $resultNotes,
            'next_action' => $nextAction,
            'status' => $newStatus,
        ]);

        $alert->status = $newStatus;
        if ($newStatus === 'selesai') {
            $alert->resolved_at = now();
        }
        $alert->save();

        return $followUp;
    }

    /**
     * Dapatkan ringkasan statistik CRM untuk Pusat CRM
     */
    public function getCrmSummary(?int $supplierId = null): array
    {
        $query = CrmAlert::query();
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $allAlerts = $query->orderBy('created_at', 'desc')->get();

        $activeAlerts = $allAlerts->where('status', '!=', 'selesai');
        $churnAlerts = $allAlerts->where('category', 'churn_prevention')->where('status', '!=', 'selesai');
        $reorderAlerts = $allAlerts->where('category', 'reorder_recommendation')->where('status', '!=', 'selesai');
        $dataMiningAlerts = $allAlerts->where('category', 'data_mining');

        return [
            'total_active_alerts' => $activeAlerts->count(),
            'churn_alerts_count' => $churnAlerts->count(),
            'reorder_alerts_count' => $reorderAlerts->count(),
            'data_mining_count' => $dataMiningAlerts->count(),
            'resolved_count' => $allAlerts->where('status', 'selesai')->count(),
            'latest_alerts' => $allAlerts->take(10),
        ];
    }
}
