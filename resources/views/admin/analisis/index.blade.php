@extends('layouts.admin')

@section('title', 'Analisis Penjualan')

@section('content')
<div class="space-y-5">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Analisis Penjualan</h1>
            <p class="text-[11px] text-slate-400 font-medium">Perhitungan peramalan WMA dan tren performa per warung</p>
        </div>

        <a href="{{ route('admin.rekomendasi.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition shadow-xs">
            Rencana Muatan (WMA)
        </a>
    </div>

    <!-- Pemilih Warung Mitra -->
    <div class="app-card p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.analisis.index') }}" class="flex items-center gap-2.5 w-full sm:w-auto">
            <label for="warung_id" class="text-xs font-bold text-slate-700 shrink-0">Mitra:</label>
            <select name="warung_id" id="warung_id" onchange="this.form.submit()" 
                    class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white font-bold text-slate-900 outline-none focus:border-amber-500">
                @foreach($warungs as $w)
                    <option value="{{ $w->id }}" {{ $selectedWarung && $selectedWarung->id === $w->id ? 'selected' : '' }}>
                        {{ $w->nama_warung }} ({{ $w->owner_name }})
                    </option>
                @endforeach
            </select>
        </form>

        @if($selectedWarung)
            <div class="text-xs text-slate-500 font-medium">
                Status: <span class="font-bold text-slate-800">{{ ucfirst($selectedWarung->partnership_status) }}</span> &bull; 
                Siklus: <span class="font-bold text-slate-800">{{ $selectedWarung->delivery_interval_days ?: 14 }} Hari</span>
            </div>
        @endif
    </div>

    @if($selectedWarung && $wmaAnalysis)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Kartu Hasil Forecast WMA -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Peramalan WMA</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-white">
                        {{ $wmaAnalysis['data_status'] === 'cukup' ? 'Data Cukup' : 'Data Terbatas' }}
                    </span>
                </div>

                <div class="my-3">
                    <div class="font-heading text-4xl font-black text-amber-400">
                        {{ $wmaAnalysis['forecast'] }} <span class="text-base font-medium text-slate-300">bungkus</span>
                    </div>
                    <span class="text-xs text-slate-400 block mt-1">
                        Tren: <strong class="text-white">{{ $wmaAnalysis['trend_label'] }}</strong>
                    </span>
                </div>

                <div class="pt-3 border-t border-slate-700 space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Sampel Riwayat:</span>
                        <span class="font-bold text-white">{{ $wmaAnalysis['periods_count'] }} Periode</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-700 text-[11px] text-slate-400">
                Model estimasi bobot bergerak 3 periode
            </div>
        </div>

        <!-- Tabel Riwayat Penjualan -->
        <div class="app-card p-5 lg:col-span-2 space-y-3">
            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Riwayat Penjualan Mitra</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-700 font-bold">
                            <th class="py-2 px-3">Periode</th>
                            <th class="py-2 px-3 text-center">Dititipkan</th>
                            <th class="py-2 px-3 text-center">Sisa Fisik</th>
                            <th class="py-2 px-3 text-center">Penjualan Aktual</th>
                            <th class="py-2 px-3 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($salesHistory as $sh)
                            @php
                                $persen = $sh['dititipkan'] > 0 ? round(($sh['terjual'] / $sh['dititipkan']) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-3 font-bold text-slate-800">{{ $sh['periode'] }}</td>
                                <td class="py-2.5 px-3 text-center font-medium text-slate-700">{{ $sh['dititipkan'] }}</td>
                                <td class="py-2.5 px-3 text-center font-medium text-slate-700">{{ $sh['sisa'] }}</td>
                                <td class="py-2.5 px-3 text-center font-black text-amber-700">{{ $sh['terjual'] }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $persen >= 75 ? 'bg-emerald-50 text-emerald-700' : ($persen >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $persen }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">Belum ada riwayat penjualan tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Grafik Tren & Peramalan WMA (Chart.js) -->
    <div class="app-card p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Tren Penjualan &amp; Estimasi WMA</h2>
        </div>

        <div class="h-60 w-full">
            <canvas id="salesForecastChart"></canvas>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesForecastChart');
        if (!ctx) return;

        const historyData = @json($salesHistory ?? []);
        const forecastVal = @json($wmaAnalysis['forecast'] ?? 0);

        const labels = historyData.map(item => item.periode);
        const actualSales = historyData.map(item => item.terjual);

        // Tambahkan peramalan WMA untuk periode berikutnya
        labels.push('Berikutnya (WMA)');
        const forecastDataset = new Array(actualSales.length).fill(null);
        forecastDataset.push(forecastVal);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan Aktual (bungkus)',
                        data: actualSales.concat([null]),
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#d97706',
                    },
                    {
                        label: 'Estimasi WMA (bungkus)',
                        data: forecastDataset,
                        borderColor: '#059669',
                        backgroundColor: '#059669',
                        pointRadius: 6,
                        pointBackgroundColor: '#059669',
                        pointStyle: 'circle',
                        showLine: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 11, weight: 'bold' }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5 }
                    }
                }
            }
        });
    });
</script>
@endpush
