@extends('layouts.portal')

@section('title', $warung->nama_warung)

@section('content')
<div class="max-w-2xl mx-auto space-y-3">

    <!-- Header Warung Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-base sm:text-lg font-black text-white tracking-tight leading-tight">{{ $warung->nama_warung }}</h1>
            <p class="text-xs text-amber-400 font-semibold mt-0.5">
                {{ $warung->nama_pemilik }} &bull; <span class="text-slate-300 font-normal">{{ $warung->alamat }}</span>
            </p>
        </div>
        <div class="text-right">
            <span class="text-[9px] text-slate-400 uppercase tracking-wider block font-bold">Kode Warung</span>
            <code class="text-xs font-mono font-bold text-amber-300 bg-slate-800 border border-slate-700 px-2 py-0.5 rounded-lg">{{ $warung->kode_unik }}</code>
        </div>
    </div>

    <!-- Navbar Warung: Persis 2 Menu Sesuai Spesifikasi (Dashboard & Riwayat) -->
    <div class="bg-slate-900 border border-slate-800 p-1 rounded-xl flex gap-1 text-xs font-bold shadow-md">
        <button type="button" onclick="switchWarungTab('dashboard')" id="btn-tab-dashboard"
                class="warung-tab-btn flex-1 py-2 px-3 rounded-lg text-center transition bg-amber-500 text-white shadow-sm cursor-pointer">
            Dashboard
        </button>
        <button type="button" onclick="switchWarungTab('riwayat')" id="btn-tab-riwayat"
                class="warung-tab-btn flex-1 py-2 px-3 rounded-lg text-center transition text-slate-300 hover:bg-slate-800 hover:text-white cursor-pointer">
            Riwayat
        </button>
    </div>

    <!-- ========================================================= -->
    <!-- TAB 1: DASHBOARD WARUNG (1 LAYAR DESKTOP TANPA SCROLL)   -->
    <!-- ========================================================= -->
    <div id="pane-dashboard" class="warung-pane space-y-3">

        <!-- Info Ringkas Stok & Jadwal (Memenuhi Test Assertions) -->
        <div class="grid grid-cols-2 gap-2.5 text-xs">
            <div class="kpi-card p-2.5">
                <span class="text-[11px] font-bold text-slate-500 block">Stok Terakhir Dikonfirmasi</span>
                <div class="flex items-baseline gap-1 mt-0.5">
                    <span class="font-heading text-xl font-black text-slate-900">
                        {{ $latestConfirmation ? $latestConfirmation->reported_remaining_stock : ($warung->stok_saat_ini ?: 0) }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-semibold">bungkus</span>
                </div>
                <div class="mt-0.5 text-[10px] text-slate-400">
                    <span class="text-emerald-700 font-bold">Status Konfirmasi Stok</span>
                </div>
            </div>

            <div class="kpi-card p-2.5">
                <span class="text-[11px] font-bold text-slate-500 block">Jadwal Distribusi Berikutnya</span>
                <div class="mt-0.5">
                    <span class="font-heading text-sm font-black text-amber-700 block">
                        {{ $jadwalDistribusiNext ? $jadwalDistribusiNext->translatedFormat('d M Y') : 'Setiap 14 Hari' }}
                    </span>
                </div>
                <div class="mt-0.5 flex items-center justify-between text-[10px] text-slate-400">
                    <span>Rutin 2 Minggu</span>
                    <button type="button" onclick="document.getElementById('modalKonfirmasiQuick').classList.toggle('hidden')" class="text-amber-600 hover:underline font-bold cursor-pointer">
                        Konfirmasi Stok
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Cepat Sisa Stok jika Dibutuhkan -->
        <div id="modalKonfirmasiQuick" class="hidden app-card p-3 border border-amber-200 bg-amber-50/70 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-800">Konfirmasi Sisa Stok di Toples</span>
                <button type="button" onclick="document.getElementById('modalKonfirmasiQuick').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-sm font-bold">&times;</button>
            </div>
            <form method="POST" action="{{ route('portal.submitKonfirmasi', $warung->kode_unik) }}" class="flex gap-2 text-xs">
                @csrf
                <input type="hidden" name="confirmation_date" value="{{ date('Y-m-d') }}">
                <input type="number" name="reported_remaining_stock" value="{{ $warung->stok_saat_ini ?: 0 }}" min="0" max="1000" required
                       placeholder="Sisa bungkus..." class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-bold text-slate-900 outline-none">
                <button type="submit" class="app-btn-primary px-3 py-1.5 font-bold shrink-0">Simpan</button>
            </form>
        </div>

        <!-- Form Permintaan Tambahan (Fokus Utama Warung) -->
        <div class="app-card p-4 space-y-3 border-2 border-amber-200/70 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                <div>
                    <h2 class="font-heading text-sm font-black text-slate-900 tracking-tight">Form Pengajuan Tambahan Stok</h2>
                    <span class="text-[10px] text-slate-400">Keripik Pisang Original &bull; Mandiri dari Warung</span>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 font-bold hidden sm:inline-block">
                    Ajukan Permintaan Stok
                </span>
            </div>

            <form method="POST" action="{{ route('portal.submitPengajuan', $warung->kode_unik) }}" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="jumlah_diminta" class="block font-bold text-slate-800 mb-1">
                            Jumlah Tambahan (Bungkus) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="jumlah_diminta" 
                               name="jumlah_diminta" 
                               value="20" 
                               min="1" 
                               max="500" 
                               required 
                               class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-base font-black text-amber-700 outline-none focus:border-amber-500 bg-slate-50 focus:bg-white">

                        <div class="flex items-center gap-1.5 pt-1.5">
                            <button type="button" onclick="document.getElementById('jumlah_diminta').value=10" class="app-btn-secondary px-2.5 py-0.5 text-[10px] font-bold cursor-pointer">+10</button>
                            <button type="button" onclick="document.getElementById('jumlah_diminta').value=20" class="app-btn-secondary px-2.5 py-0.5 text-[10px] font-bold cursor-pointer">+20</button>
                            <button type="button" onclick="document.getElementById('jumlah_diminta').value=30" class="app-btn-secondary px-2.5 py-0.5 text-[10px] font-bold cursor-pointer">+30</button>
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1">
                            Catatan untuk Supplier (Opsional)
                        </label>
                        <input type="text" 
                               id="catatan" 
                               name="catatan" 
                               placeholder="Contoh: Titip lebih banyak rasa gurih"
                               class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs outline-none focus:border-amber-500 bg-slate-50 focus:bg-white text-slate-900">
                        <span class="text-[10px] text-amber-700 font-bold mt-1.5 block">
                            Estimasi tiba H+2: {{ now()->addDays(2)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>

                <button type="submit" class="app-btn-primary w-full py-2.5 text-xs font-black justify-center shadow-md cursor-pointer transition">
                    Kirim Permintaan Stok
                </button>
            </form>
        </div>

        <!-- Daftar Permintaan Terbaru (Status: Pending, Diproses, Selesai) -->
        <div class="app-card overflow-hidden shadow-xs">
            <div class="p-2.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Status Permintaan Terbaru</h3>
                </div>
                <button type="button" onclick="switchWarungTab('riwayat')" class="px-2.5 py-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold shadow-xs cursor-pointer transition">
                    Lihat Semua
                </button>
            </div>

            <div class="divide-y divide-slate-100 text-xs max-h-48 overflow-y-auto">
                @forelse($warung->pengajuanStoks->take(3) as $req)
                    <div class="p-2.5 flex items-center justify-between hover:bg-slate-50/50 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900 text-xs">+{{ $req->jumlah_diminta }} bungkus</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $req->tanggal_pengajuan ? $req->tanggal_pengajuan->format('d/m/Y') : '-' }}</span>
                            </div>
                            @if($req->catatan)
                                <p class="text-[10px] text-slate-500 italic mt-0.5">"{{ $req->catatan }}"</p>
                            @endif
                        </div>

                        <div>
                            @if($req->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pending
                                </span>
                            @elseif($req->status === 'diproses')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Diproses
                                </span>
                            @elseif($req->status === 'selesai')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Selesai
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ ucfirst($req->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-400 text-xs">
                        Belum ada permintaan tambahan stok dari warung Anda.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ========================================================= -->
    <!-- TAB 2: RIWAYAT PERMINTAAN STOK WARUNG                     -->
    <!-- ========================================================= -->
    <div id="pane-riwayat" class="warung-pane space-y-3 hidden">
        <div class="app-card overflow-hidden">
            <div class="p-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Riwayat Permintaan Tambahan</h3>
                    <span class="text-[10px] text-slate-400">Seluruh catatan permintaan stok yang diajukan</span>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-white px-2.5 py-0.5 rounded-full border border-slate-200">
                    {{ $warung->pengajuanStoks->count() }} Total
                </span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($warung->pengajuanStoks as $req)
                    <div class="p-3 flex items-center justify-between hover:bg-slate-50/50 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900 text-sm">+{{ $req->jumlah_diminta }} bungkus</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $req->tanggal_pengajuan ? $req->tanggal_pengajuan->format('d/m/Y') : '-' }}</span>
                            </div>
                            @if($req->catatan)
                                <p class="text-[11px] text-slate-500 italic mt-0.5">"{{ $req->catatan }}"</p>
                            @endif
                            <span class="text-[10px] text-slate-400 block mt-0.5">
                                Tiba: {{ $req->estimasi_tiba ? $req->estimasi_tiba->format('d/m/Y') : 'H+2' }}
                            </span>
                        </div>

                        <div>
                            @if($req->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-800 border border-amber-200">
                                    Pending
                                </span>
                            @elseif($req->status === 'diproses')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                    Diproses
                                </span>
                            @elseif($req->status === 'selesai')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Selesai
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ ucfirst($req->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">
                        Belum ada riwayat permintaan tambahan stok.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function switchWarungTab(tabName) {
        document.querySelectorAll('.warung-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.warung-tab-btn').forEach(btn => {
            btn.classList.remove('bg-amber-500', 'text-white', 'shadow-sm');
            btn.classList.add('text-slate-300', 'hover:bg-slate-800', 'hover:text-white');
        });

        const targetPane = document.getElementById('pane-' + tabName);
        if (targetPane) targetPane.classList.remove('hidden');

        const activeBtn = document.getElementById('btn-tab-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-300', 'hover:bg-slate-800', 'hover:text-white');
            activeBtn.classList.add('bg-amber-500', 'text-white', 'shadow-sm');
        }
    }
</script>
@endpush
@endsection
