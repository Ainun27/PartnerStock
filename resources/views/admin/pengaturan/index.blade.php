@extends('layouts.admin')

@section('title', 'Pengaturan Parameter Sistem')

@section('content')
<div class="space-y-6">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Pengaturan Parameter Sistem</h1>
            <p class="text-[11px] text-slate-400 font-medium">Konfigurasi bobot WMA, mode konsinyasi, dan interval pengiriman</p>
        </div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-800 rounded-xl border border-slate-700 text-xs font-bold text-amber-400">
            <span>Konfigurasi Dinamis</span>
        </div>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-amber-100/70 p-6 shadow-xs">
        <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-5">
            @csrf

            <!-- 1. Konfigurasi Bobot WMA -->
            <div class="space-y-3 pb-5 border-b border-amber-100/70">
                <h2 class="text-sm font-extrabold text-[#2E170B] flex items-center gap-2">
                    <span>🧮</span>
                    <span>Bobot Weighted Moving Average (Total Harus 1.0 / 100%)</span>
                </h2>
                <p class="text-xs text-stone-500 leading-relaxed">
                    WMA memberikan bobot lebih besar pada data penjualan yang lebih baru. Parameter awal default adalah 20% (X₁), 30% (X₂), dan 50% (X₃).
                </p>

                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Periode 1 (Terlama - w₁)</label>
                        <input type="number" step="0.05" min="0.01" max="1" name="w1" value="{{ old('w1', $wmaWeights[0] ?? 0.20) }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">Default: 0.20 (20%)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Periode 2 (Tengah - w₂)</label>
                        <input type="number" step="0.05" min="0.01" max="1" name="w2" value="{{ old('w2', $wmaWeights[1] ?? 0.30) }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">Default: 0.30 (30%)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Periode 3 (Terbaru - w₃)</label>
                        <input type="number" step="0.05" min="0.01" max="1" name="w3" value="{{ old('w3', $wmaWeights[2] ?? 0.50) }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">Default: 0.50 (50%)</span>
                    </div>
                </div>
            </div>

            <!-- 2. Mode Konsinyasi Default -->
            <div class="space-y-3 pb-5 border-b border-amber-100/70">
                <h2 class="text-sm font-extrabold text-[#2E170B] flex items-center gap-2">
                    <span>🔄</span>
                    <span>Mode Penanganan Stok Lama Konsinyasi</span>
                </h2>

                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-amber-200/80 cursor-pointer hover:bg-amber-50/40 transition">
                        <input type="radio" name="consignment_mode" value="A" {{ old('consignment_mode', $consignmentMode) === 'A' ? 'checked' : '' }} class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="font-extrabold text-xs text-stone-800 block">Mode A — Stok Lama Ditarik Seluruhnya & Diganti Baru (R = F)</span>
                            <span class="text-[11px] text-stone-500 block mt-0.5">Semua sisa produk lama ditarik saat kunjungan (sesuai perjanjian retur segar). Rekomendasi stok baru murni mengikuti perkiraan WMA.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-amber-200/80 cursor-pointer hover:bg-amber-50/40 transition">
                        <input type="radio" name="consignment_mode" value="B" {{ old('consignment_mode', $consignmentMode) === 'B' ? 'checked' : '' }} class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="font-extrabold text-xs text-stone-800 block">Mode B — Stok Lama Tetap Dapat Dijual (R = max(0, F - S))</span>
                            <span class="text-[11px] text-stone-500 block mt-0.5">Stok lama yang masih layak jual tetap di toples warung. Rekomendasi tambahan stok adalah selisih antara perkiraan WMA (F) dengan sisa fisik (S).</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 3. Interval & Kuota Awal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Interval Pengiriman Rutin (Hari)</label>
                    <input type="number" name="delivery_interval_days" min="1" max="60" value="{{ old('delivery_interval_days', $deliveryInterval) }}" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                    <span class="text-[10px] text-stone-400 mt-0.5 block">Siklus kunjungan rutin distributor (default 14 hari / 2 minggu).</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Kuota Awal Warung Baru (Bungkus)</label>
                    <input type="number" name="initial_stock_quota" min="5" max="100" value="{{ old('initial_stock_quota', $initialQuota) }}" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                    <span class="text-[10px] text-stone-400 mt-0.5 block">Saran stok penitipan awal saat data historis warung belum cukup.</span>
                </div>
            </div>

            <div class="pt-4 border-t border-amber-100/70 flex justify-end">
                <button type="submit" class="app-btn-primary px-5 py-2 text-xs font-bold shadow-xs">
                    Simpan Parameter Sistem
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
