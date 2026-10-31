@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
<div class="space-y-5">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Laporan &amp; Rekapitulasi</h1>
        </div>
        <button onclick="window.print()" class="app-btn-primary px-4 py-2 text-xs font-bold shadow-md cursor-pointer print:hidden">
            Cetak Laporan
        </button>
    </div>

    <!-- Filter Bar (Hidden saat Cetak) -->
    <div class="app-card p-3.5 print:hidden">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Warung Mitra</label>
                <select name="warung_id" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white font-bold text-slate-900 outline-none focus:border-amber-500">
                    <option value="">Semua Warung</option>
                    @foreach($warungs as $w)
                        <option value="{{ $w->id }}" {{ $selectedWarungId == $w->id ? 'selected' : '' }}>
                            {{ $w->nama_warung }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white font-medium text-slate-900 outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white font-medium text-slate-900 outline-none focus:border-amber-500">
            </div>

            <div class="flex items-end gap-1.5">
                <button type="submit" class="flex-1 app-btn-primary py-1.5 text-xs font-bold">
                    Filter
                </button>
                <a href="{{ route('admin.laporan.index') }}" class="px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 4 Ringkasan Angka Rekapitulasi (Omset Warung Dihilangkan Sesuai Arahan) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Total Dititipkan</span>
            <span class="font-heading text-2xl font-black text-slate-900 block mt-1">{{ number_format($totalDititipkan) }}</span>
            <span class="text-[11px] font-bold text-slate-600">bungkus baru</span>
        </div>

        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-emerald-700 block">Total Terjual</span>
            <span class="font-heading text-2xl font-black text-emerald-700 block mt-1">{{ number_format($totalTerjual) }}</span>
            <span class="text-[11px] font-bold text-slate-600">bungkus laku</span>
        </div>

        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Total Ditarik</span>
            <span class="font-heading text-2xl font-black text-slate-800 block mt-1">{{ number_format($totalDitarik) }}</span>
            <span class="text-[11px] font-bold text-slate-600">stok ditarik</span>
        </div>

        <div class="kpi-card p-3.5 bg-gradient-to-br from-white to-amber-50/40 border-l-4 border-l-amber-500">
            <span class="text-xs font-bold text-amber-900 block">Pendapatan Supplier</span>
            <span class="font-heading text-xl font-black text-amber-800 block mt-1">Rp {{ number_format($totalPendapatanSupplier, 0, ',', '.') }}</span>
            <span class="text-[11px] font-bold text-slate-600">Kalkulasi data: terjual &times; harga titip</span>
        </div>
    </div>

    <!-- 1. TABEL RINCIAN PENJUALAN AKTUAL -->
    <div class="app-card overflow-hidden">
        <div class="p-3 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Rincian Penjualan Aktual (Cek Fisik)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Warung Mitra</th>
                        <th class="py-3 px-4 text-center">Stok Awal</th>
                        <th class="py-3 px-4 text-center">Sisa Fisik</th>
                        <th class="py-3 px-4 text-center">Jumlah Terjual</th>
                        <th class="py-3 px-4 text-right">Pendapatan Supplier</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($salesRecords as $sr)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-700">
                                {{ $sr->period_end ? $sr->period_end->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $sr->warung ? $sr->warung->nama_warung : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center text-slate-700">{{ $sr->opening_stock }}</td>
                            <td class="py-3 px-4 text-center text-slate-700">{{ $sr->remaining_stock }}</td>
                            <td class="py-3 px-4 text-center font-black text-emerald-600">{{ $sr->sold_quantity }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900 text-right">
                                Rp {{ number_format($sr->sold_quantity * (($sr->warung && $sr->warung->harga_titip > 0) ? $sr->warung->harga_titip : 12000), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Tidak ada riwayat penjualan pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. TABEL RINCIAN DISTRIBUSI & PENARIKAN (TERPISAH) -->
    <div class="app-card overflow-hidden">
        <div class="p-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Rincian Distribusi &amp; Penarikan Stok</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Warung Mitra</th>
                        <th class="py-3 px-4 text-center">Titip Baru</th>
                        <th class="py-3 px-4 text-center">Stok Lama Ditarik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($restocks as $rst)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-700">
                                {{ $rst->distribution_date ? $rst->distribution_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $rst->warung ? $rst->warung->nama_warung : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-black text-amber-700">
                                {{ $rst->quantity_delivered }} bungkus
                            </td>
                            <td class="py-3 px-4 text-center font-black text-slate-700">
                                {{ $rst->old_stock_withdrawn }} bungkus
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Tidak ada riwayat pengiriman pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
