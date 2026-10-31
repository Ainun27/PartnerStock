<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - B2B PartnerStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- Mobile Top Bar (< md) -->
        <header class="md:hidden bg-[#0f172a] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-40 border-b border-slate-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-sm font-black shadow-sm">
                    📦
                </div>
                <div>
                    <span class="font-extrabold text-white text-sm block leading-tight">PartnerStock</span>
                    <span class="text-[10px] text-amber-400 font-semibold">Distributor</span>
                </div>
            </a>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 text-slate-100 hover:text-white" title="Keluar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Sidebar Supplier: Sleek Modern Dark Sidebar with Exactly 6 Menus -->
        <aside class="w-64 bg-[#0f172a] text-slate-300 flex-shrink-0 hidden md:flex flex-col justify-between p-4 sticky top-0 h-screen z-30 border-r border-slate-800 shadow-xl">
            <div>
                <!-- Brand Header -->
                <div class="pb-4 mb-2 border-b border-slate-800">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-xl font-black shadow-md shadow-amber-500/20">
                            📦
                        </div>
                        <div>
                            <span class="font-heading text-base font-black text-white tracking-tight block leading-tight">PartnerStock</span>
                            <span class="text-[11px] text-amber-400 font-semibold tracking-wider uppercase block">Distributor</span>
                        </div>
                    </a>
                </div>

                <!-- 6 Menu Utama Supplier Sesuai Ketentuan Revisi -->
                <nav class="space-y-1.5 text-xs font-bold">
                    <!-- 1. Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/25' : 'text-slate-100 hover:bg-slate-800 hover:text-amber-400' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Mitra Warung -->
                    <a href="{{ route('admin.warung.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('admin.warung.*') ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/25' : 'text-slate-100 hover:bg-slate-800 hover:text-amber-400' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.warung.*') ? 'text-white' : 'text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Mitra Warung</span>
                    </a>

                    <!-- 3. Distribusi Stok -->
                    <a href="{{ route('admin.kunjungan.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ (request()->routeIs('admin.kunjungan.*') || request()->routeIs('admin.konfirmasi-stok.*')) ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/25' : 'text-slate-100 hover:bg-slate-800 hover:text-amber-400' }}">
                        <svg class="w-4 h-4 shrink-0 {{ (request()->routeIs('admin.kunjungan.*') || request()->routeIs('admin.konfirmasi-stok.*')) ? 'text-white' : 'text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Distribusi Stok</span>
                    </a>

                    <!-- 4. Permintaan Tambahan -->
                    <a href="{{ route('admin.pengajuan.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('admin.pengajuan.*') ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/25' : 'text-slate-100 hover:bg-slate-800 hover:text-amber-400' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.pengajuan.*') ? 'text-white' : 'text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Permintaan Tambahan</span>
                        </div>
                        @php
                            $pendingReqCount = \App\Models\PengajuanStok::where('status', 'pending')->count();
                        @endphp
                        @if($pendingReqCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full {{ request()->routeIs('admin.pengajuan.*') ? 'bg-white text-amber-900' : 'bg-amber-500 text-white shadow-xs' }}">
                                {{ $pendingReqCount }}
                            </span>
                        @endif
                    </a>

                    <!-- 5. Laporan -->
                    <a href="{{ route('admin.laporan.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('admin.laporan.*') ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/25' : 'text-slate-100 hover:bg-slate-800 hover:text-amber-400' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.laporan.*') ? 'text-white' : 'text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <!-- Footer Sidebar: Portal & Logout -->
            <div class="pt-3 border-t border-slate-800 space-y-1 text-xs">
                <a href="{{ route('portal.index') }}" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-100 hover:bg-slate-800 hover:text-amber-400 transition font-bold">
                    <span>🏪</span>
                    <span>Portal Mitra Warung</span>
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-100 hover:bg-rose-500/20 hover:text-rose-300 transition text-left font-bold cursor-pointer">
                        <svg class="w-4 h-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Right Content Area -->
        <div class="flex-1 min-w-0 flex flex-col justify-between bg-slate-100">
            <main class="max-w-6xl w-full mx-auto p-4 sm:p-6 lg:p-6 flex-1">
                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <span>✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold ml-2 cursor-pointer">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <span>⚠️</span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold ml-2 cursor-pointer">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">
                        @foreach($errors->all() as $error)
                            <div>&bull; {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Minimalist Compact Footer -->
            <footer class="border-t border-slate-200/80 bg-white py-2 px-6 text-center text-[11px] text-slate-400 font-medium">
                B2B PartnerStock &bull; Sistem Distribusi &amp; Konsinyasi Produk
            </footer>
        </div>

    </div>

    @stack('scripts')
</body>
</html>
