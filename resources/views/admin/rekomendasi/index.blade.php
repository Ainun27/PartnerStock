@extends('layouts.admin')

@section('title', 'Rekomendasi Stok & PO')

@section('content')
<div class="space-y-5">

    <!-- Header Hero Card Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold">
                <span class="text-amber-400">Rekomendasi Stok &amp; PO</span>
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span class="text-[11px] font-bold text-amber-300">Pra-Kunjungan</span>
            </div>
            <h1 class="text-lg sm:text-xl font-black text-white tracking-tight">
                Rencana Bawa Stok Hari Ini
            </h1>
            <p class="text-xs text-slate-400">
                Mode aktif: <strong class="text-amber-400">{{ $mode === 'A' ? 'Mode A (Tarik Stok Lama & Ganti Baru)' : 'Mode B (Stok Lama Tetap Dijual)' }}</strong>
            </p>
        </div>

        <!-- Big Highlight Counter Total Muatan Motor -->
        <div class="bg-slate-900/90 p-3.5 rounded-xl border border-slate-700 shadow-md flex items-center gap-4 flex-shrink-0">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Total Muatan Motor</span>
                <div class="flex items-baseline gap-1 mt-0.5">
                    <span class="font-heading text-3xl font-black text-amber-400 tracking-tight leading-none">
                        +{{ $totalHarusDibawa }}
                    </span>
                    <span class="text-xs font-bold text-slate-300">bungkus</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-0.5">
                    {{ $totalPcsDisarankan }} (Sistem) + {{ $totalPOAktif }} (Warung)
                </span>
            </div>
            <div class="border-l border-slate-700 pl-3 space-y-1.5">
                <button onclick="window.print()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs block w-full text-center cursor-pointer font-bold transition">
                    Cetak Muatan
                </button>
                <a href="{{ route('admin.kunjungan.create') }}" class="app-btn-primary px-3 py-1.5 text-xs block w-full text-center font-black shadow-xs">
                    + Distribusi
                </a>
            </div>
        </div>
    </div>

    <!-- 3 Bagian Pemisahan Jelas Sesuai Spesifikasi: A. Rekomendasi, B. Permintaan Tambahan, C. Total -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <!-- A. Rekomendasi Stok Sistem -->
        <div class="kpi-card p-4 border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">A. Rekomendasi Stok Sistem</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">WMA Sistem</span>
            </div>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="font-heading text-3xl font-black text-blue-700">{{ $totalPcsDisarankan }}</span>
                <span class="text-xs font-bold text-slate-400">bungkus</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                Jumlah stok berdasarkan hasil analisis penjualan WMA
            </p>
        </div>

        <!-- B. Permintaan Tambahan Warung -->
        <div class="kpi-card p-4 border-l-4 border-l-orange-500">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">B. Permintaan Tambahan Warung</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200">Mandiri Warung</span>
            </div>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="font-heading text-3xl font-black text-orange-600">+{{ $totalPOAktif }}</span>
                <span class="text-xs font-bold text-slate-400">bungkus</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                Jumlah stok tambahan yang diminta secara mandiri oleh warung
            </p>
        </div>

        <!-- C. Total Stok yang Harus Dibawa -->
        <div class="kpi-card p-4 border-l-4 border-l-emerald-500 bg-gradient-to-br from-white to-emerald-50/40">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">C. Total Stok yang Harus Dibawa</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Rumus: A + B</span>
            </div>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="font-heading text-3xl font-black text-emerald-600">{{ $totalHarusDibawa }}</span>
                <span class="text-xs font-bold text-slate-400">bungkus</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                Total Stok yang Harus Dibawa = Rekomendasi ({{ $totalPcsDisarankan }}) + Tambahan Warung ({{ $totalPOAktif }})
            </p>
        </div>
    </div>

    <!-- Toggle Mode Konsinyasi -->
    <div class="app-card p-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <span class="font-bold text-slate-700 px-2">Mode Konsinyasi yang Berlaku:</span>
        <div class="flex items-center gap-1.5 font-bold">
            <a href="{{ route('admin.rekomendasi.index', ['mode' => 'A']) }}" 
               class="px-3.5 py-1.5 rounded-xl transition flex items-center gap-2 {{ $mode === 'A' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                <span>🔄</span>
                <span>Mode A (Tarik Stok Lama &amp; Ganti Baru)</span>
            </a>
            <a href="{{ route('admin.rekomendasi.index', ['mode' => 'B']) }}" 
               class="px-3.5 py-1.5 rounded-xl transition flex items-center gap-2 {{ $mode === 'B' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                <span>➕</span>
                <span>Mode B (Stok Lama Tetap Dijual)</span>
            </a>
        </div>
    </div>

    <!-- TABEL REKOMENDASI STOK & PO PER MITRA WARUNG -->
    <div class="app-card overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Daftar Rekomendasi Stok &amp; PO Warung</h2>
                <span class="text-[11px] text-slate-400">Total Stok = Rekomendasi Stok Sistem + Permintaan Tambahan Warung</span>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-white border border-slate-200 px-2.5 py-0.5 rounded-full">
                {{ $rekomendasiData->count() }} Mitra Warung
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Mitra Warung</th>
                        <th class="py-3 px-4">Riwayat Penjualan</th>
                        <th class="py-3 px-4 text-center">Rekomendasi Stok Sistem</th>
                        <th class="py-3 px-4 text-center">Permintaan Tambahan Warung</th>
                        <th class="py-3 px-4 text-center">Total Stok yang Harus Dibawa</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekomendasiData as $item)
                        @php
                            $w = $item['warung'];
                            $salesVals = $item['wma_details']['sales_values'] ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Nama Mitra Warung -->
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="text-xs">{{ $w->nama_warung }}</div>
                                <span class="text-[10px] text-slate-400 font-normal">{{ $w->owner_name }} &bull; {{ $w->alamat }}</span>
                            </td>

                            <!-- Riwayat Penjualan -->
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                @if(count($salesVals) > 0)
                                    <span>{{ implode(', ', $salesVals) }} pcs</span>
                                    <span class="text-[10px] text-slate-400 block font-sans">Tren: {{ $item['label'] }}</span>
                                @else
                                    <span class="text-slate-400 italic">Warung baru</span>
                                @endif
                            </td>

                            <!-- Rekomendasi Stok Sistem -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200 text-xs">
                                    {{ $item['estimasi_bawa'] }} bungkus
                                </span>
                            </td>

                            <!-- Permintaan Tambahan Warung -->
                            <td class="py-3.5 px-4 text-center">
                                @if($item['po_tambahan'] > 0)
                                    <span class="font-black text-orange-700 bg-orange-50 px-2.5 py-1 rounded-lg border border-orange-200 text-xs">
                                        +{{ $item['po_tambahan'] }} bungkus
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium text-xs">0</span>
                                @endif
                            </td>

                            <!-- Total Stok yang Harus Dibawa -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-heading text-sm font-black text-emerald-700 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200 shadow-2xs">
                                    {{ $item['total_harus_dibawa'] }} bungkus
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.kunjungan.create', ['warung_id' => $w->id]) }}" 
                                   class="app-btn-primary px-3 py-1.5 text-xs font-bold shadow-xs">
                                    + Distribusi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                                Belum ada data warung mitra aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
