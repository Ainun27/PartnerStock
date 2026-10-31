<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Distribusi dan Konsinyasi Keripik Pisang &amp; Produk Konsinyasi B2B">
    <title>PartnerStock - Sistem Konsinyasi Produk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-navy min-h-screen flex items-center justify-center p-4 text-slate-800 antialiased" style="background-color: #0f172a !important; background-image: none !important;">

    <!-- Ambient Subtle Glowing Circles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl"></div>
    </div>

    <!-- Main Container Card with Matching Navy Header -->
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-700/50 shadow-2xl overflow-hidden space-y-0">
        
        <!-- Header Navy Sesuai Tema Navbar -->
        <div class="bg-[#0f172a] text-white p-6 text-center space-y-2 border-b border-slate-800">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white font-black text-2xl flex items-center justify-center mx-auto shadow-lg shadow-amber-500/25">
                📦
            </div>

            <div>
                <h1 class="font-heading text-2xl font-black text-white tracking-tight">
                    PartnerStock
                </h1>
                <p class="text-xs text-amber-400 font-bold mt-1 uppercase tracking-wider">
                    Sistem Distribusi &amp; Konsinyasi Produk
                </p>
            </div>
        </div>

        <div class="p-6 space-y-5">
            <!-- 2 Role Cards: Wadah Pilihan Peran Bersih & Tegas -->
            <div class="space-y-3">
                <!-- Pemasok -->
                <a href="{{ route('login') }}" 
                   class="p-4 rounded-2xl border border-slate-200 hover:border-amber-400 flex items-center justify-between hover:shadow-md transition-all duration-150 block group bg-slate-50/60 hover:bg-white shadow-2xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-200 text-amber-600 flex items-center justify-center text-xl font-bold">
                            🚚
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block group-hover:text-amber-600 transition">
                                Pemasok / Distributor
                            </span>
                            <span class="text-[11px] text-slate-500 font-semibold">Manajemen Stok &amp; Pemantauan Mitra</span>
                        </div>
                    </div>
                    <span class="app-btn-primary px-3.5 py-1.5 text-xs font-black shadow-xs">
                        Masuk
                    </span>
                </a>

                <!-- Warung Mitra -->
                <a href="{{ route('portal.index') }}" 
                   class="p-4 rounded-2xl border border-slate-200 hover:border-amber-400 flex items-center justify-between hover:shadow-md transition-all duration-150 block group bg-slate-50/60 hover:bg-white shadow-2xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-slate-800 text-amber-400 flex items-center justify-center text-xl font-bold">
                            🏪
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block group-hover:text-amber-600 transition">
                                Warung Mitra
                            </span>
                            <span class="text-[11px] text-slate-500 font-semibold">Cek Sisa Stok &amp; Pengajuan Tambahan</span>
                        </div>
                    </div>
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-black transition shadow-xs">
                        Masuk
                    </span>
                </a>
            </div>

            <!-- 3 Stat Cards (Wadah Angka Rapi & Cerah) -->
            @php
                $totalWarung = \App\Models\Warung::where('is_aktif', true)->count();
                $totalStok = \App\Models\Warung::where('is_aktif', true)->sum('stok_saat_ini');
                $totalTerjual = \App\Models\StokPenjualan::sum('jumlah_laku');
            @endphp
            <div class="grid grid-cols-3 gap-2.5 pt-2 border-t border-slate-100">
                <div class="p-3 text-center rounded-2xl bg-slate-50 border border-slate-200/70 shadow-2xs">
                    <span class="font-heading text-xl font-black text-slate-900 block">{{ $totalWarung }}</span>
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Mitra</span>
                </div>
                <div class="p-3 text-center rounded-2xl bg-slate-50 border border-slate-200/70 shadow-2xs">
                    <span class="font-heading text-xl font-black text-amber-600 block">{{ $totalStok }}</span>
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Total Stok</span>
                </div>
                <div class="p-3 text-center rounded-2xl bg-slate-50 border border-slate-200/70 shadow-2xs">
                    <span class="font-heading text-xl font-black text-emerald-600 block">{{ $totalTerjual }}</span>
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Terjual</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
