@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-3">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-base sm:text-lg font-black text-white tracking-tight leading-tight">Dashboard Supplier</h1>
        </div>

        <div class="text-xs font-bold text-slate-800 bg-white px-3.5 py-1.5 rounded-xl border border-slate-200 shadow-sm">
            Periode: <strong class="text-amber-600 font-black">{{ now()->translatedFormat('F Y') }}</strong>
        </div>
    </div>

    <!-- 4 Ringkasan Angka Utama Sesuai Ketentuan Revisi -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        
        <!-- 1. Total Mitra -->
        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Total Mitra</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black text-slate-900 leading-none">
                {{ $totalWarung }}
            </div>
        </div>

        <!-- 2. Distribusi Periode Ini -->
        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Distribusi Periode Ini</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black text-amber-600 leading-none">
                {{ number_format($distribusiBerjalan) }}
            </div>
        </div>

        <!-- 3. Permintaan Tambahan Pending -->
        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Permintaan Tambahan Pending</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black {{ $pengajuanPending->count() > 0 ? 'text-orange-600' : 'text-slate-900' }} leading-none">
                {{ $pengajuanPending->count() }}
            </div>
            <span class="hidden">Pengajuan</span>
        </div>

        <!-- 4. Total Seluruh Produk Terjual Selama Ini -->
        <div class="kpi-card p-3.5 bg-gradient-to-br from-white to-emerald-50/40 border-l-4 border-l-emerald-600">
            <span class="text-xs font-bold text-slate-700 block">Total Seluruh Produk Terjual</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black text-emerald-700 leading-none">
                {{ number_format($totalTerjual) }}
            </div>
        </div>

    </div>

    <!-- Statistik Grafik Ringkas (Full Width, Bersih & Pas 1 Layar Desktop) -->
    <div class="app-card p-4 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
            <div>
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Statistik Penjualan &amp; Estimasi WMA</h2>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <button type="button" onclick="toggleDataset(0)" id="legend-0" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 cursor-pointer text-[11px] transition">
                    Produk Terjual: {{ number_format(array_sum($chartTerjual)) }}
                </button>
                <button type="button" onclick="toggleDataset(1)" id="legend-1" 
                        class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 cursor-pointer text-[11px] transition">
                    Sisa Stok: {{ number_format(array_sum($chartStok)) }}
                </button>
                <button type="button" onclick="toggleDataset(2)" id="legend-2" 
                        class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200 cursor-pointer text-[11px] transition">
                    Saran WMA: {{ number_format(array_sum($chartWma)) }}
                </button>
            </div>
        </div>

        <div class="h-64 sm:h-72 w-full relative">
            <canvas id="dashboardStatsChart"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('dashboardStatsChart');
        if (!ctx) return;

        const labels = @json($chartLabels);
        const dataTerjual = @json($chartTerjual);
        const dataStok = @json($chartStok);
        const dataWma = @json($chartWma);

        window.myDashboardChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Produk Terjual',
                        data: dataTerjual,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Sisa Stok Mitra',
                        data: dataStok,
                        backgroundColor: '#f59e0b',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Saran Muatan WMA',
                        data: dataWma,
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.parsed.y} bungkus`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, weight: '600' },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            color: '#94a3b8',
                            precision: 0
                        }
                    }
                }
            }
        });
    });

    function toggleDataset(index) {
        if (!window.myDashboardChart) return;
        const chart = window.myDashboardChart;
        const isVisible = chart.isDatasetVisible(index);
        chart.setDatasetVisibility(index, !isVisible);
        chart.update();

        const btn = document.getElementById('legend-' + index);
        if (btn) {
            if (isVisible) {
                btn.classList.add('opacity-40', 'line-through');
            } else {
                btn.classList.remove('opacity-40', 'line-through');
            }
        }
    }
</script>
@endpush
