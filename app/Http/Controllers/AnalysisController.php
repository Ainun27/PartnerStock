<?php

namespace App\Http\Controllers;

use App\Models\Warung;
use App\Services\WmaService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    protected WmaService $wmaService;

    public function __construct(WmaService $wmaService)
    {
        $this->wmaService = $wmaService;
    }

    /**
     * Tampilkan halaman Analisis Penjualan & Hasil Perhitungan WMA
     */
    public function index(Request $request)
    {
        $warungs = Warung::where('partnership_status', 'aktif')
            ->where('is_aktif', true)
            ->orderBy('nama_warung')
            ->get();

        $selectedWarungId = $request->query('warung_id', $warungs->first()?->id);
        $selectedWarung = $warungs->firstWhere('id', $selectedWarungId) ?: $warungs->first();

        $wmaAnalysis = null;
        $salesHistory = [];

        if ($selectedWarung) {
            $wmaAnalysis = $this->wmaService->calculateForWarung($selectedWarung);

            // Riwayat penjualan lengkap untuk tabel & chart
            $salesRecords = $selectedWarung->salesRecords()->orderBy('period_end', 'asc')->get();
            if ($salesRecords->isNotEmpty()) {
                $salesHistory = $salesRecords->map(function ($r) {
                    return [
                        'periode' => $r->period_end ? $r->period_end->format('d/m/Y') : '-',
                        'terjual' => (int) $r->sold_quantity,
                        'dititipkan' => (int) $r->distributed_quantity,
                        'sisa' => (int) $r->remaining_stock,
                    ];
                })->all();
            } else {
                $stokPenjualans = $selectedWarung->stokPenjualans()->whereNotNull('tanggal_cek')->orderBy('tanggal_cek', 'asc')->get();
                $salesHistory = $stokPenjualans->map(function ($s) {
                    return [
                        'periode' => $s->tanggal_cek ? $s->tanggal_cek->format('d/m/Y') : '-',
                        'terjual' => (int) $s->jumlah_laku,
                        'dititipkan' => (int) $s->stok_sebelumnya,
                        'sisa' => (int) $s->sisa_fisik,
                    ];
                })->all();
            }
        }

        return view('admin.analisis.index', compact('warungs', 'selectedWarung', 'wmaAnalysis', 'salesHistory'));
    }
}
