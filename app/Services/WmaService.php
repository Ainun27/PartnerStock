<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\Warung;

class WmaService
{
    /**
     * Dapatkan bobot WMA yang berlaku dari konfigurasi atau default [0.20, 0.30, 0.50]
     *
     * @return array<float> [w1_terlama, w2_kedua, w3_terbaru]
     */
    public function getWeights(): array
    {
        $weights = SystemSetting::get('wma_weights', [0.20, 0.30, 0.50]);
        if (!is_array($weights) || count($weights) !== 3) {
            return [0.20, 0.30, 0.50];
        }
        return array_map('floatval', $weights);
    }

    /**
     * Hitung Weighted Moving Average (WMA) berdasarkan riwayat penjualan aktual
     *
     * @param array<int> $salesValues Urutan dari terlama ke terbaru: [X1, X2, X3]
     * @param array<float>|null $customWeights Bobot kustom jika ada
     * @return array Hasil analisis WMA lengkap
     */
    public function calculate(array $salesValues, ?array $customWeights = null): array
    {
        $weights = $customWeights ?: $this->getWeights();
        $count = count($salesValues);

        if ($count === 0) {
            return [
                'raw_forecast' => 0.0,
                'forecast' => 0,
                'periods_count' => 0,
                'sales_values' => [],
                'weights_used' => [],
                'data_status' => 'belum_cukup',
                'status_message' => 'Belum ada data penjualan tercatat. Memerlukan minimal 3 periode konsinyasi untuk peramalan WMA.',
                'trend' => 'belum_cukup',
                'trend_label' => 'Data Belum Cukup',
                'formula_text' => 'Tidak dapat dihitung (data kosong)',
            ];
        }

        // Ambil maksimal 3 periode terakhir
        $recentSales = array_slice($salesValues, -3);
        $count = count($recentSales);

        if ($count === 1) {
            $x1 = (int) $recentSales[0];
            return [
                'raw_forecast' => (float) $x1,
                'forecast' => $x1,
                'periods_count' => 1,
                'sales_values' => [$x1],
                'weights_used' => [1.0],
                'data_status' => 'belum_cukup',
                'status_message' => 'Hanya tersedia 1 periode riwayat penjualan. Menggunakan nilai aktual periode tunggal (bobot dinormalisasi 100%).',
                'trend' => 'belum_cukup',
                'trend_label' => 'Data Belum Cukup (1 Periode)',
                'formula_text' => "(1.0 × {$x1}) = {$x1}",
            ];
        }

        if ($count === 2) {
            $x1 = (int) $recentSales[0];
            $x2 = (int) $recentSales[1];
            // Normalisasi bobot untuk 2 periode: 40% dan 60%
            $normWeights = [0.40, 0.60];
            $raw = ($normWeights[0] * $x1) + ($normWeights[1] * $x2);
            $rounded = (int) ceil($raw);

            $trend = ($x2 > $x1) ? 'meningkat' : (($x2 < $x1) ? 'menurun' : 'stabil');
            $trendLabel = match ($trend) {
                'meningkat' => 'Tren Penjualan Meningkat (Awal)',
                'menurun' => 'Tren Penjualan Menurun (Awal)',
                default => 'Tren Penjualan Stabil',
            };

            return [
                'raw_forecast' => round($raw, 2),
                'forecast' => $rounded,
                'periods_count' => 2,
                'sales_values' => [$x1, $x2],
                'weights_used' => $normWeights,
                'data_status' => 'belum_cukup',
                'status_message' => 'Tersedia 2 periode riwayat penjualan. Bobot dinormalisasi 40% (periode 1) & 60% (periode 2).',
                'trend' => $trend,
                'trend_label' => $trendLabel,
                'formula_text' => "({$normWeights[0]} × {$x1}) + ({$normWeights[1]} × {$x2}) = " . round($raw, 2),
            ];
        }

        // Tepat 3 periode (X1 terlama, X2 tengah, X3 terbaru)
        $x1 = (int) $recentSales[0];
        $x2 = (int) $recentSales[1];
        $x3 = (int) $recentSales[2];

        $w1 = $weights[0];
        $w2 = $weights[1];
        $w3 = $weights[2];

        $raw = ($w1 * $x1) + ($w2 * $x2) + ($w3 * $x3);
        $rounded = (int) ceil($raw);

        // Analisis tren penjualan
        if ($x3 > $x2 && $x2 > $x1) {
            $trend = 'meningkat';
            $trendLabel = 'Tren Penjualan Meningkat';
        } elseif ($x3 < $x2 && $x2 < $x1) {
            $trend = 'menurun';
            $trendLabel = 'Tren Penjualan Menurun';
        } else {
            $trend = 'stabil';
            $trendLabel = 'Tren Penjualan Stabil';
        }

        return [
            'raw_forecast' => round($raw, 2),
            'forecast' => $rounded,
            'periods_count' => 3,
            'sales_values' => [$x1, $x2, $x3],
            'weights_used' => [$w1, $w2, $w3],
            'data_status' => 'cukup',
            'status_message' => 'Data historis 3 periode lengkap dan terverifikasi.',
            'trend' => $trend,
            'trend_label' => $trendLabel,
            'formula_text' => "({$w1} × {$x1}) + ({$w2} × {$x2}) + ({$w3} × {$x3}) = " . round($raw, 2),
        ];
    }

    /**
     * Hitung WMA untuk warung tertentu berdasarkan riwayat penjualan di database
     *
     * @param Warung $warung
     * @return array
     */
    public function calculateForWarung(Warung $warung): array
    {
        // Ambil riwayat penjualan langsung dari stok_penjualans (urutan kronologis terlama ke terbaru)
        $sesiList = $warung->stokPenjualans()
            ->whereNotNull('tanggal_cek')
            ->reorder()
            ->orderBy('tanggal_cek', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $salesValues = $sesiList->pluck('jumlah_laku')->all();
        $periodMeta = $sesiList->map(function ($s) {
            return [
                'id' => $s->id,
                'start' => $s->tanggal_titip ? $s->tanggal_titip->format('d/m/Y') : ($s->tanggal_cek ? $s->tanggal_cek->format('d/m/Y') : null),
                'end' => $s->tanggal_cek ? $s->tanggal_cek->format('d/m/Y') : null,
                'sold' => (int) $s->jumlah_laku,
                'distributed' => (int) $s->stok_sebelumnya,
            ];
        })->all();

        $result = $this->calculate($salesValues);
        $result['period_meta'] = array_slice($periodMeta, -3);

        return $result;
    }
}
