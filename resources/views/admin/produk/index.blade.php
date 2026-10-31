@extends('layouts.admin')

@section('title', 'Manajemen Produk')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Data Produk</h1>
            <p class="text-[11px] text-slate-400 font-medium">Pengelolaan produk konsinyasi distributor.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-800 rounded-xl border border-slate-700 text-xs font-bold text-amber-400">
            <span>📦</span>
            <span>Produk Tunggal Konsinyasi</span>
        </div>
    </div>

    <!-- Aturan Bisnis Banner -->
    <div class="app-card p-4 text-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 text-sm">
                i
            </div>
            <div class="space-y-1">
                <h2 class="font-bold text-slate-900 text-sm">Aturan Bisnis Produk Konsinyasi</h2>
                <p class="text-slate-600 leading-relaxed">
                    Sesuai sistem konsinyasi, distributor <strong>mengelola satu produk utama konsinyasi</strong>. Sistem secara konsisten menjaga keabsahan data historis dan peramalan WMA tanpa varian fiktif.
                </p>
            </div>
        </div>
    </div>

    @if($singleProduct)
    <div class="bg-white rounded-2xl border border-amber-100/70 p-6 shadow-xs max-w-2xl">
        <h2 class="text-sm font-extrabold text-[#2E170B] mb-4 flex items-center gap-2">
            <span>⚙️</span>
            <span>Pengaturan Parameter Produk</span>
        </h2>

        <form method="POST" action="{{ route('admin.produk.update', $singleProduct->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Kode Produk</label>
                    <input type="text" name="product_code" value="{{ old('product_code', $singleProduct->product_code) }}" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono font-bold" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Satuan Produk</label>
                    <input type="text" name="unit" value="{{ old('unit', $singleProduct->unit) }}" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 mb-1">Nama Produk</label>
                <input type="text" name="name" value="{{ old('name', $singleProduct->name) }}" 
                       class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required>
                <p class="text-[11px] text-stone-400 mt-1">Harus mengandung "Keripik Pisang Original".</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Harga Titip / Pokok Supplier (Rp)</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', (int)$singleProduct->cost_price) }}" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required min="0">
                    <p class="text-[11px] text-stone-400 mt-1">Pendapatan distributor per bungkus terjual (default Rp 12.000).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Harga Jual Eceran Warung (Rp)</label>
                    <input type="number" name="agreed_price" value="{{ old('agreed_price', (int)$singleProduct->agreed_price) }}" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200/80 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold" required min="0">
                    <p class="text-[11px] text-stone-400 mt-1">Harga jual ke konsumen akhir di warung (default Rp 15.000).</p>
                </div>
            </div>

            <div class="pt-4 border-t border-amber-100/70 flex justify-end">
                <button type="submit" class="app-btn-primary px-5 py-2 text-xs font-bold shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection
