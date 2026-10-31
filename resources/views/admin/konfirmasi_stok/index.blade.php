@extends('layouts.admin')

@section('title', 'Konfirmasi Sisa Stok (H-2)')

@section('content')
<div class="space-y-5">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Konfirmasi Sisa Stok (H-2)</h1>
            <p class="text-[11px] text-slate-400 font-medium">Pencatatan sisa stok hasil konfirmasi via WhatsApp H-2 sebelum kunjungan fisik</p>
        </div>

        <a href="{{ route('admin.kunjungan.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition shadow-xs">
            Distribusi Stok
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Form Input Sisa Stok Bersih (To-the-point) -->
        <div class="app-card p-5 space-y-4 lg:col-span-1 h-fit">
            <div class="border-b border-slate-100 pb-2">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Catat Sisa Stok Mitra</h2>
                <p class="text-[11px] text-slate-400">Pilih warung dan masukkan sisa stok yang dilaporkan</p>
            </div>

            <form method="POST" action="{{ route('admin.konfirmasi-stok.store') }}" id="formKonfirmasi" class="space-y-3.5">
                @csrf
                <input type="hidden" name="confirmation_date" value="{{ date('Y-m-d') }}">

                <!-- 1. Pilih Warung Mitra -->
                <div>
                    <label for="warung_id" class="block text-xs font-bold text-slate-700 mb-1">
                        Pilih Warung Mitra <span class="text-red-500">*</span>
                    </label>
                    <select name="warung_id" id="warung_id" required 
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-900 font-bold outline-none focus:border-amber-500 shadow-2xs">
                        <option value="" disabled {{ !$selectedWarungId ? 'selected' : '' }}>-- Pilih Warung --</option>
                        @foreach($warungs as $w)
                            @php
                                $stokLalu = $w->stok_ditaro_sesi_lalu;
                            @endphp
                            <option value="{{ $w->id }}" 
                                    data-stok-lalu="{{ $stokLalu }}" 
                                    data-sisa="{{ $w->stok_saat_ini }}"
                                    {{ (old('warung_id', $selectedWarungId) == $w->id) ? 'selected' : '' }}>
                                {{ $w->nama_warung }} ({{ $w->owner_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Info Stok Titip Lalu (Auto Display) -->
                <div id="boxStokLalu" class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                    <span class="text-slate-500 font-semibold">Stok Dititipkan Sesi Lalu:</span>
                    <strong id="valStokLalu" class="text-slate-900 font-black text-sm">--</strong>
                </div>

                <!-- 2. Masukkan Sisa Stok Sekarang -->
                <div>
                    <label for="reported_remaining_stock" class="block text-xs font-bold text-slate-700 mb-1">
                        Sisa Stok Sekarang (Bungkus) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="reported_remaining_stock" 
                               name="reported_remaining_stock" 
                               min="0" 
                               max="500" 
                               placeholder="Contoh: 4" 
                               required
                               class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white font-black text-slate-900 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 shadow-2xs">
                        <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-bold pointer-events-none">bungkus</span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full app-btn-primary py-2.5 text-xs font-black shadow-md cursor-pointer justify-center">
                        Simpan Sisa Stok
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Status Konfirmasi Mitra -->
        <div class="app-card overflow-hidden lg:col-span-2">
            <div class="p-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Status Konfirmasi Mitra Warung</h2>
                    <p class="text-[11px] text-slate-400">Hubungi via WhatsApp H-2 untuk menanyakan sisa stok</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                    Total: {{ $warungs->count() }} Mitra
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3.5">Warung Mitra</th>
                            <th class="py-3 px-3.5 text-center">Titip Lalu</th>
                            <th class="py-3 px-3.5 text-center">Sisa Stok</th>
                            <th class="py-3 px-3.5 text-center">Status</th>
                            <th class="py-3 px-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($warungs as $warung)
                            @php
                                $latestConf = $warung->latest_stock_confirmation;
                                $isNeedConf = $warung->needs_stock_confirmation;
                                $stokLalu = $warung->stok_ditaro_sesi_lalu;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3.5">
                                    <div class="font-bold text-slate-900">{{ $warung->nama_warung }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $warung->owner_name }} &bull; {{ $warung->phone ?: '-' }}</div>
                                </td>
                                <td class="py-3 px-3.5 text-center font-bold text-slate-700">
                                    {{ $stokLalu ?: 0 }} pcs
                                </td>
                                <td class="py-3 px-3.5 text-center font-black text-amber-700">
                                    {{ $latestConf ? $latestConf->reported_remaining_stock : $warung->stok_saat_ini }} pcs
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @if($isNeedConf)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Perlu Konfirmasi
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Terkonfirmasi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if($warung->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $warung->phone) }}?text=Halo%20{{ urlencode($warung->nama_pemilik) }}%20dari%20Distributor.%20Untuk%20jadwal%20pengiriman%20mendatang,%20saat%20ini%20stok%20sisa%20berapa%20bungkus%20ya?" 
                                               target="_blank" 
                                               class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] transition shadow-2xs">
                                                Chat WA
                                            </a>
                                        @endif
                                        <button type="button" 
                                                onclick="selectWarung({{ $warung->id }})" 
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] transition border border-slate-200 cursor-pointer">
                                            Catat
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Riwayat Konfirmasi Terakhir -->
    <div class="app-card overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Riwayat Konfirmasi Sisa Stok</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3.5">Tanggal</th>
                        <th class="py-2.5 px-3.5">Warung Mitra</th>
                        <th class="py-2.5 px-3.5 text-center">Sisa Dilaporkan</th>
                        <th class="py-2.5 px-3.5 text-center">Cek Fisik Kunjungan</th>
                        <th class="py-2.5 px-3.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentConfirmations as $conf)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-3.5 font-mono text-[11px]">
                                {{ $conf->confirmation_date ? $conf->confirmation_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-3.5 font-bold text-slate-900">
                                {{ $conf->warung ? $conf->warung->nama_warung : '-' }}
                            </td>
                            <td class="py-2.5 px-3.5 text-center font-black text-amber-700">
                                {{ $conf->reported_remaining_stock }} bungkus
                            </td>
                            <td class="py-2.5 px-3.5 text-center font-bold text-slate-700">
                                {{ $conf->physically_checked_stock !== null ? $conf->physically_checked_stock . ' bungkus' : 'Menunggu Kunjungan' }}
                            </td>
                            <td class="py-2.5 px-3.5 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $conf->data_status === 'sesuai' ? 'bg-emerald-50 text-emerald-700' : ($conf->data_status === 'selisih' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ ucfirst(str_replace('_', ' ', $conf->data_status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat konfirmasi stok tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentConfirmations->hasPages())
            <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                {{ $recentConfirmations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    function updateStokLalu() {
        const select = document.getElementById('warung_id');
        const selected = select.options[select.selectedIndex];
        const valEl = document.getElementById('valStokLalu');
        
        if (selected && selected.dataset.stokLalu) {
            valEl.textContent = selected.dataset.stokLalu + ' bungkus';
        } else {
            valEl.textContent = '--';
        }
    }

    function selectWarung(warungId) {
        const select = document.getElementById('warung_id');
        select.value = warungId;
        updateStokLalu();
        document.getElementById('reported_remaining_stock').focus();
    }

    document.getElementById('warung_id').addEventListener('change', updateStokLalu);
    document.addEventListener('DOMContentLoaded', updateStokLalu);
</script>
@endpush
