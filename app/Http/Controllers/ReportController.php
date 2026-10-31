<?php

namespace App\Http\Controllers;

use App\Models\CrmAlert;
use App\Models\StokPenjualan;
use App\Models\Warung;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan Halaman Laporan dan Rekapitulasi (Pusat Transaksi Stok Konsinyasi)
     */
    public function index(Request $request)
    {
        $warungs = Warung::orderBy('nama_warung')->get();

        $selectedWarungId = $request->query('warung_id');
        $startDate = $request->query('start_date', Carbon::now()->subMonths(2)->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $reportType = $request->query('type', 'rekap'); // rekap, distribusi, penjualan, penarikan, crm

        // Query Penjualan Aktual (stok_penjualans dengan tanggal cek fisik)
        $salesQuery = StokPenjualan::with(['warung', 'product'])
            ->whereNotNull('tanggal_cek')
            ->whereBetween('tanggal_cek', [$startDate, $endDate]);

        if ($selectedWarungId) {
            $salesQuery->where('warung_id', $selectedWarungId);
        }
        $salesRecords = $salesQuery->orderBy('tanggal_cek', 'desc')->get();

        // Query Distribusi & Penarikan Stok (stok_penjualans dengan tanggal titip)
        $restockQuery = StokPenjualan::with(['warung', 'product'])
            ->whereNotNull('tanggal_titip')
            ->whereBetween('tanggal_titip', [$startDate, $endDate]);

        if ($selectedWarungId) {
            $restockQuery->where('warung_id', $selectedWarungId);
        }
        $restocks = $restockQuery->orderBy('tanggal_titip', 'desc')->get();

        // Query CRM Alerts (Data Mining & Churn)
        $crmQuery = CrmAlert::with('warung')
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);

        if ($selectedWarungId) {
            $crmQuery->where('warung_id', $selectedWarungId);
        }
        $crmAlerts = $crmQuery->get();

        // Ringkasan Rekapitulasi Matematis Konsinyasi
        $totalDititipkan = (int) $restocks->sum('jumlah_titip');
        $totalDitarik = (int) $restocks->sum('old_stock_withdrawn');
        $totalTerjual = (int) $salesRecords->sum('jumlah_laku');

        // Pendapatan supplier dihitung dari kalkulasi data: terjual x harga titip
        $totalPendapatanSupplier = (int) $salesRecords->sum('total_pendapatan');
        if ($totalPendapatanSupplier === 0) {
            $totalPendapatanSupplier = $salesRecords->sum(function ($s) {
                $harga = ($s->warung && $s->warung->harga_titip > 0) ? $s->warung->harga_titip : 12000;
                return $s->jumlah_laku * $harga;
            });
        }
        $totalOmset = $totalTerjual * 15000;

        return view('admin.laporan.index', compact(
            'warungs',
            'selectedWarungId',
            'startDate',
            'endDate',
            'reportType',
            'salesRecords',
            'restocks',
            'crmAlerts',
            'totalDititipkan',
            'totalDitarik',
            'totalTerjual',
            'totalOmset',
            'totalPendapatanSupplier'
        ));
    }
}
