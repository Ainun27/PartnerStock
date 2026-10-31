<?php

namespace App\Http\Controllers;

use App\Models\CrmAlert;
use App\Models\Restock;
use App\Models\SalesRecord;
use App\Models\StockConfirmation;
use App\Models\StockRequest;
use App\Models\Warung;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan Halaman Laporan dan Rekapitulasi
     */
    public function index(Request $request)
    {
        $warungs = Warung::orderBy('nama_warung')->get();

        $selectedWarungId = $request->query('warung_id');
        $startDate = $request->query('start_date', Carbon::now()->subMonths(2)->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $reportType = $request->query('type', 'rekap'); // rekap, distribusi, penjualan, penarikan, crm

        // Query Sales Records
        $salesQuery = SalesRecord::with(['warung', 'product'])
            ->whereBetween('period_end', [$startDate, $endDate]);

        if ($selectedWarungId) {
            $salesQuery->where('warung_id', $selectedWarungId);
        }
        $salesRecords = $salesQuery->orderBy('period_end', 'desc')->get();

        // Query Restocks (Distribusi & Penarikan dicatat terpisah!)
        $restockQuery = Restock::with(['warung', 'product'])
            ->whereBetween('distribution_date', [$startDate, $endDate]);

        if ($selectedWarungId) {
            $restockQuery->where('warung_id', $selectedWarungId);
        }
        $restocks = $restockQuery->orderBy('distribution_date', 'desc')->get();

        // Query CRM Alerts
        $crmQuery = CrmAlert::with('warung')
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);

        if ($selectedWarungId) {
            $crmQuery->where('warung_id', $selectedWarungId);
        }
        $crmAlerts = $crmQuery->orderBy('created_at', 'desc')->get();

        // Ringkasan Rekapitulasi Matematis
        $totalDititipkan = $restocks->sum('quantity_delivered');
        $totalDitarik = $restocks->sum('old_stock_withdrawn');
        $totalTerjual = $salesRecords->sum('sold_quantity');
        
        // Pendapatan supplier dihitung dari kalkulasi data: terjual x harga titip
        $totalPendapatanSupplier = $salesRecords->sum(function ($s) {
            $harga = ($s->warung && $s->warung->harga_titip > 0) ? $s->warung->harga_titip : 12000;
            return $s->sold_quantity * $harga;
        });
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
