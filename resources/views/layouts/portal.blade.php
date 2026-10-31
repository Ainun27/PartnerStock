<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Mitra') - PartnerStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col justify-between bg-[#0f172a] text-slate-800 antialiased">

    <!-- Top Header: Sleek Dark Bar -->
    <header class="bg-[#0f172a] text-white border-b border-slate-800 sticky top-0 z-30 shadow-md">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('portal.index') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white font-black text-xs flex items-center justify-center shadow-xs">
                    🍌
                </div>
                <div>
                    <span class="font-extrabold text-white text-sm block leading-tight">PartnerStock</span>
                    <span class="text-[10px] text-amber-400 font-semibold">Portal Warung Mitra</span>
                </div>
            </a>

            <div class="flex items-center gap-2 text-xs">
                @if(session('portal_warung_id'))
                    <a href="{{ route('portal.logout') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-bold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </a>
                @else
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 hover:text-white font-bold transition shadow-xs">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Keluar</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-2xl w-full mx-auto px-4 py-5 flex-1">
        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold shadow-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold ml-2 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 p-3.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold flex items-center justify-between shadow-xs">
                <span>{{ session('info') }}</span>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-900 font-bold ml-2 cursor-pointer">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Clean Footer -->
    <footer class="border-t border-slate-800 bg-[#0b1329] py-3 px-4 text-center text-xs text-slate-500 font-medium">
        B2B PartnerStock &bull; Portal Warung Mitra
    </footer>

    @stack('scripts')
</body>
</html>
