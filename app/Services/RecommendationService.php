<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockConfirmation;
use App\Models\StockRecommendation;
use App\Models\SystemSetting;
use App\Models\Warung;

class RecommendationService
{
    protected WmaService $wmaService;

    public function __construct(WmaService $wmaService)
    {
        $this->wmaService = $wmaService;
    }

    /**
     * Dapatkan mode konsinyasi default dari konfigurasi sistem (default 'A')
     */
    public function getDefaultConsignmentMode(): string
    {
        return (string) SystemSetting::get('consignment_mode', 'A');
    }

    /**
     * Hitung rekomendasi stok untuk satu warung
     *
     * @param Warung $warung
     * @param string|null $mode 'A' atau 'B' (jika null, gunakan default sistem)
     * @return array
     */
    public function getRecommendationForWarung(Warung $warung, ?string $mode = null): array
    {
        $mode = $mode ?: $this->getDefaultConsignmentMode();
        $wmaResult = $this->wmaService->calculateForWarung($warung);

        // Ambil data konfirmasi stok via WA terakhir
        $latestConfirmation = $warung->latest_stock_confirmation;
        $isConfirmedRecently = $latestConfirmation && $latestConfirmation->confirmation_date->diffInDays(now()) <= 7;
        $remainingStock = $latestConfirmation ? (int) $latestConfirmation->reported_remaining_stock : (int) $warung->stok_saat_ini;

        $forecast = (int) $wmaResult['forecast'];
        $dataStatus = $wmaResult['data_status'];

        if (!$isConfirmedRecently && $latestConfirmation === null && $warung->stokPenjualans()->count() > 0) {
            $dataStatus = 'perlu_konfirmasi';
        }

        // Jika warung belum memiliki riwayat sama sekali, gunakan kuota awal konsinyasi (misal 20 bungkus)
        if ($wmaResult['periods_count'] === 0) {
            $initialQuota = (int) SystemSetting::get('initial_stock_quota', 20);
            $recommendedQuantity = $initialQuota;
            $explanation = "Warung baru (belum ada riwayat penjualan). Disarankan kuota awal standar konsinyasi sebanyak {$initialQuota} bungkus.";
        } elseif ($mode === 'A') {
            // Mode A: Stok lama ditarik seluruhnya dan diganti baru.
            // R = F
            $recommendedQuantity = $forecast;
            $explanation = "Mode A (Stok lama ditarik & diganti baru): Rekomendasi stok baru sebanyak {$forecast} bungkus sesuai peramalan WMA. Stok lama di warung ({$remainingStock} bungkus) akan ditarik saat kunjungan.";
        } else {
            // Mode B: Stok lama tetap dapat dijual.
            // R = max(0, F - S)
            $recommendedQuantity = max(0, $forecast - $remainingStock);
            $explanation = "Mode B (Stok lama tetap dijual): Tambahan stok dihitung dari perkiraan WMA ({$forecast} bungkus) dikurangi stok tersisa ({$remainingStock} bungkus) = {$recommendedQuantity} bungkus.";
        }

        // Cek apakah ada stock recommendation yang sudah tersimpan / disetujui sebelumnya
        $existingRec = StockRecommendation::where('warung_id', $warung->id)
            ->whereDate('calculation_date', now()->toDateString())
            ->first();

        $approvedQuantity = $existingRec ? $existingRec->approved_quantity : $recommendedQuantity;

        return [
            'warung_id' => $warung->id,
            'warung_name' => $warung->nama_warung,
            'forecast_quantity' => $wmaResult['raw_forecast'],
            'forecast_rounded' => $forecast,
            'recommended_quantity' => $recommendedQuantity,
            'approved_quantity' => $approvedQuantity,
            'consignment_mode' => $mode,
            'mode_label' => ($mode === 'A') ? 'Mode A (Tarik Stok Lama & Ganti Baru)' : 'Mode B (Stok Lama Tetap Dijual)',
            'remaining_stock' => $remainingStock,
            'is_confirmed' => (bool) $isConfirmedRecently,
            'latest_confirmation_date' => $latestConfirmation ? $latestConfirmation->confirmation_date->format('d/m/Y') : null,
            'data_status' => $dataStatus,
            'status_badge' => match ($dataStatus) {
                'cukup' => ['label' => 'Data Cukup', 'color' => 'bg-emerald-100 text-emerald-800'],
                'belum_cukup' => ['label' => 'Data Belum Cukup', 'color' => 'bg-amber-100 text-amber-800'],
                'perlu_konfirmasi' => ['label' => 'Perlu Konfirmasi Stok', 'color' => 'bg-red-100 text-red-800'],
                default => ['label' => 'Draft', 'color' => 'bg-slate-100 text-slate-800'],
            },
            'explanation' => $explanation,
            'wma_details' => $wmaResult,
        ];
    }

    /**
     * Dapatkan rekomendasi untuk seluruh warung aktif dan hitung total persiapan pengiriman
     *
     * @param int|null $supplierId
     * @param string|null $mode
     * @return array
     */
    public function getAllRecommendations(?int $supplierId = null, ?string $mode = null): array
    {
        $mode = $mode ?: $this->getDefaultConsignmentMode();

        $query = Warung::where('partnership_status', 'aktif')
            ->where('is_aktif', true);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $warungs = $query->orderBy('nama_warung')->get();
        $items = [];
        $totalRecommended = 0;
        $totalApproved = 0;
        $totalPendingConfirmation = 0;

        foreach ($warungs as $warung) {
            $rec = $this->getRecommendationForWarung($warung, $mode);
            $items[] = $rec;
            $totalRecommended += $rec['recommended_quantity'];
            $totalApproved += $rec['approved_quantity'];

            if ($rec['data_status'] === 'perlu_konfirmasi') {
                $totalPendingConfirmation++;
            }
        }

        return [
            'mode' => $mode,
            'items' => $items,
            'total_warung' => count($items),
            'total_recommended' => $totalRecommended,
            'total_approved' => $totalApproved,
            'total_pending_confirmation' => $totalPendingConfirmation,
        ];
    }

    /**
     * Simpan atau perbarui rekomendasi stok yang disetujui oleh supplier
     */
    public function saveApproval(Warung $warung, int $approvedQuantity, string $consignmentMode, ?string $reason = null): StockRecommendation
    {
        $rec = $this->getRecommendationForWarung($warung, $consignmentMode);

        $defaultProduct = Product::first();

        return StockRecommendation::updateOrCreate(
            [
                'warung_id' => $warung->id,
                'calculation_date' => now()->toDateString(),
            ],
            [
                'supplier_id' => $warung->supplier_id ?: 1,
                'product_id' => $defaultProduct ? $defaultProduct->id : null,
                'forecast_quantity' => $rec['forecast_quantity'],
                'recommended_quantity' => $rec['recommended_quantity'],
                'approved_quantity' => $approvedQuantity,
                'consignment_mode' => $consignmentMode,
                'source_periods' => $rec['wma_details']['sales_values'],
                'weights_used' => $rec['wma_details']['weights_used'],
                'data_status' => $rec['data_status'],
                'adjustment_reason' => $reason,
            ]
        );
    }
}
