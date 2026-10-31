<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Pemasok - B2B PartnerStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-navy min-h-screen flex items-center justify-center p-4 antialiased text-slate-800" style="background-color: #0f172a !important; background-image: none !important;">

    <!-- Ambient Glowing Subtle Circles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-blue-500/10 blur-3xl"></div>
    </div>

    <div class="max-w-sm w-full space-y-4">
        <!-- Crisp White Elevated Card with Matching Navy Header -->
        <div class="bg-white rounded-3xl border border-slate-700/50 shadow-2xl overflow-hidden space-y-0">
            <!-- Header Navy Sesuai Tema Navbar -->
            <div class="bg-[#0f172a] text-white p-5 text-center space-y-1.5 border-b border-slate-800">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-1">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white font-black text-xl flex items-center justify-center shadow-md shadow-amber-500/25">
                        📦
                    </div>
                </a>
                <h1 class="font-heading text-xl font-black text-white tracking-tight">PartnerStock</h1>
                <p class="text-[11px] text-amber-400 font-bold uppercase tracking-wider">Portal Distributor Konsinyasi</p>
            </div>

            <div class="p-6 space-y-4">
                @if(session('info'))
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                        {{ session('info') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4 text-xs" autocomplete="off">
                    @csrf

                    <div>
                        <label for="email" class="block font-bold text-slate-700 mb-1">Email / Username</label>
                        <input type="text" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Masukkan email atau username"
                               required 
                               autocomplete="off"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs text-slate-900 font-bold bg-slate-50 focus:bg-white shadow-2xs">
                    </div>

                    <div>
                        <label for="password" class="block font-bold text-slate-700 mb-1">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               value="" 
                               placeholder="Masukkan password"
                               required 
                               autocomplete="current-password"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs text-slate-900 font-bold bg-slate-50 focus:bg-white shadow-2xs">
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-amber-500 focus:ring-amber-400">
                            <span class="font-medium text-slate-600">Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" class="app-btn-primary w-full py-2.5 text-xs font-black justify-center shadow-md cursor-pointer">
                        Masuk ke Dashboard
                    </button>
                </form>
            </div>
        </div>

        <!-- Button with background for Portal Mitra Warung (No bare arrow) -->
        <div class="text-center pt-1">
            <a href="{{ route('portal.index') }}" class="inline-block px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold transition shadow-sm">
                Portal Warung Mitra
            </a>
        </div>
    </div>

</body>
</html>
