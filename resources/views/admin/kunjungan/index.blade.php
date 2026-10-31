@extends('layouts.admin')

@section('title', 'Distribusi Stok')

@section('content')
<div class="space-y-4">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Distribusi Stok</h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.kunjungan.create') }}" class="app-btn-primary px-4 py-2 text-xs font-bold shadow-md cursor-pointer">
                + Catat Distribusi
            </a>
        </div>
    </div>

    <!-- 3 Ringkasan Metrik: Rekomendasi Stok Sistem, Permintaan Tambahan Warung, Total Stok yang Harus Dibawa -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- 1. Rekomendasi Stok Sistem -->
        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Rekomendasi Stok Sistem</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black text-blue-700 leading-none">
                {{ number_format($rekomendasiSistem, 0, ',', '.') }}
            </div>
        </div>

        <!-- 2. Permintaan Tambahan Warung -->
        <div class="kpi-card p-3.5">
            <span class="text-xs font-bold text-slate-700 block">Permintaan Tambahan Warung</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black text-amber-600 leading-none">
                {{ number_format($permintaanTambahan, 0, ',', '.') }}
            </div>
        </div>

        <!-- 3. Total Stok yang Harus Dibawa -->
        <div class="kpi-card p-3.5 bg-gradient-to-br from-white to-amber-50/30 border-l-4 border-l-amber-500">
            <span class="text-xs font-bold text-slate-700 block">Total Stok yang Harus Dibawa</span>
            <div class="mt-1 font-heading text-2xl sm:text-3xl font-black text-amber-700 leading-none">
                {{ number_format($totalHarusDibawa, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="app-card p-3 flex items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('admin.kunjungan.index') }}" class="flex items-center gap-2">
            <label for="warung_id" class="text-slate-700 font-bold text-xs">Mitra:</label>
            <select name="warung_id" id="warung_id" onchange="this.form.submit()" 
                    class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold outline-none focus:border-amber-500 bg-slate-50 focus:bg-white text-slate-900">
                <option value="">Semua Mitra Warung</option>
                @foreach($warungs as $w)
                    <option value="{{ $w->id }}" {{ $warungId == $w->id ? 'selected' : '' }}>
                        {{ $w->nama_warung }}
                    </option>
                @endforeach
            </select>
            @if($warungId)
                <a href="{{ route('admin.kunjungan.index') }}" class="text-rose-600 font-bold hover:underline">Reset</a>
            @endif
        </form>

        <span class="text-slate-400 text-xs hidden sm:inline font-medium">
            Total Sesi: <strong class="text-slate-800">{{ $kunjungans->total() }}</strong>
        </span>
    </div>

    <!-- Tabel Bersih Distribusi Stok dengan Susunan Kolom Sesuai Ketentuan -->
    <div class="app-card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/90 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3.5">Tanggal</th>
                        <th class="py-3 px-3.5">Mitra Warung</th>
                        <th class="py-3 px-3.5 text-center">Titip Sebelumnya</th>
                        <th class="py-3 px-3.5 text-center">Sisa</th>
                        <th class="py-3 px-3.5 text-center">Terjual</th>
                        <th class="py-3 px-3.5 text-center">Titip Baru</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kunjungans as $kj)
                        @php 
                            $decision = $kj->keputusan; 
                            $targetTitip = $decision['rekomendasi_titip'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Tanggal -->
                            <td class="py-3 px-3.5 whitespace-nowrap text-slate-700 font-mono text-[11px]">
                                {{ $kj->tanggal_cek ? $kj->tanggal_cek->format('d/m/Y') : ($kj->tanggal_titip ? $kj->tanggal_titip->format('d/m/Y') : $kj->created_at->format('d/m/Y')) }}
                            </td>

                            <!-- Mitra Warung -->
                            <td class="py-3 px-3.5 font-bold text-slate-900 whitespace-nowrap">
                                {{ $kj->warung->nama_warung }}
                            </td>

                            <!-- Titip Sebelumnya (dari riwayat) -->
                            <td class="py-3 px-3.5 text-center font-medium text-slate-700">
                                {{ $kj->stok_sebelumnya }}
                            </td>

                            <!-- Sisa (konfirmasi warung) -->
                            <td class="py-3 px-3.5 text-center font-semibold text-slate-800">
                                {{ $kj->sisa_fisik }}
                            </td>

                            <!-- Terjual (Titip Sebelumnya - Sisa) -->
                            <td class="py-3 px-3.5 text-center font-black text-emerald-600">
                                {{ $kj->jumlah_laku > 0 ? $kj->jumlah_laku : 0 }}
                            </td>

                            <!-- Titip Baru (sinkron dengan angka rekomendasi status) -->
                            <td class="py-3 px-3.5 text-center font-black text-amber-600">
                                {{ $targetTitip > 0 ? '+' . $targetTitip : 0 }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $decision['badge_color'] }}">
                                    {{ $decision['ringkasan'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 text-xs">Belum ada catatan sesi distribusi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kunjungans->hasPages())
            <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                {{ $kunjungans->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
