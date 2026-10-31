@extends('layouts.admin')

@section('title', 'Form Distribusi Stok')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    <!-- Header Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Input Data Stok &amp; Penjualan</h1>
        </div>
        <a href="{{ route('admin.kunjungan.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
            Kembali
        </a>
    </div>

    @if($pengajuanTerkait)
        <div class="p-3.5 rounded-2xl bg-white border-2 border-orange-200 text-xs text-orange-950 flex items-center justify-between shadow-xs">
            <div>
                <strong>Permintaan Tambahan Warung:</strong> {{ $pengajuanTerkait->warung->nama_warung }} meminta +{{ $pengajuanTerkait->jumlah_diminta }} bungkus.
            </div>
            <span class="text-[10px] bg-orange-500 text-white font-bold px-2.5 py-0.5 rounded-full">PO Aktif</span>
        </div>
    @endif

    <!-- Form Distribusi Stok (Persis 3 Input Sesuai Spesifikasi) -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
        <form method="POST" action="{{ route('admin.kunjungan.store') }}" id="formDistribusi" class="space-y-4">
            @csrf

            <!-- Hidden Fields untuk kompatibilitas data transaksi -->
            <input type="hidden" name="tanggal_cek" value="{{ date('Y-m-d') }}">
            <input type="hidden" name="consignment_mode" value="{{ $consignmentMode ?? 'A' }}">
            <input type="hidden" name="jumlah_laku" id="jumlah_laku" value="0">
            <input type="hidden" name="old_stock_withdrawn" id="old_stock_withdrawn" value="0">
            @if($pengajuanTerkait)
                <input type="hidden" name="pengajuan_id" value="{{ $pengajuanTerkait->id }}">
            @endif

            <!-- INPUT 1: Nama Warung -->
            <div>
                <label for="warung_id" class="block text-xs font-bold text-slate-800 mb-1.5">
                    1. Nama Warung <span class="text-red-500">*</span>
                </label>
                <select id="warung_id" 
                        name="warung_id" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 outline-none focus:border-amber-500 bg-white shadow-2xs">
                    <option value="" disabled {{ !$selectedWarungId ? 'selected' : '' }}>-- Pilih Mitra Warung Terdaftar --</option>
                    @foreach($warungs as $w)
                        @php 
                            $stokLalu = $w->stok_ditaro_sesi_lalu;
                            $saran = $w->rekomendasi_stok['rekomendasi_titip'] ?? 20;
                        @endphp
                        <option value="{{ $w->id }}" 
                                data-stok-lalu="{{ $stokLalu }}"
                                data-saran="{{ $saran }}"
                                {{ (old('warung_id', $selectedWarungId) == $w->id) ? 'selected' : '' }}>
                            {{ $w->nama_warung }} ({{ $w->owner_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- INPUT 2: Barang yang Dititipkan Sebelumnya (Auto Riwayat) -->
            @php
                $initStokLalu = old('stok_ditaro', $selectedWarung ? $selectedWarung->stok_ditaro_sesi_lalu : 0);
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="stok_ditaro" class="text-xs font-bold text-slate-800">
                        2. Barang yang Dititipkan Sebelumnya (Stok yang Dititipkan) <span class="text-red-500">*</span>
                    </label>
                    <span id="badgeAutoStok" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                        Otomatis dari Riwayat
                    </span>
                </div>
                <div class="relative">
                    <input type="number" 
                           id="stok_ditaro" 
                           name="stok_ditaro" 
                           value="{{ $initStokLalu }}" 
                           min="0" 
                           required 
                           readonly
                           class="w-full h-11 px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm font-black text-slate-900 outline-none cursor-not-allowed">
                    <span class="absolute right-3.5 top-3 text-xs text-slate-400 font-bold pointer-events-none">bungkus</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Diambil otomatis dari riwayat distribusi terakhir warung tersebut.</p>
            </div>

            <!-- INPUT 3: Sisa Stok (Input Manual Konfirmasi Warung) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="sisa_fisik" class="text-xs font-bold text-slate-800">
                        3. Sisa Stok yang Ada Sekarang <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                        Konfirmasi Warung
                    </span>
                </div>
                <div class="relative">
                    <input type="number" 
                           id="sisa_fisik" 
                           name="sisa_fisik" 
                           value="{{ old('sisa_fisik', '') }}" 
                           min="0" 
                           required 
                           placeholder="Masukkan sisa stok konfirmasi warung..."
                           class="w-full h-11 px-3.5 py-2 rounded-xl border-2 border-amber-400 bg-white text-base font-black text-slate-900 outline-none focus:ring-2 focus:ring-amber-500/20">
                    <span class="absolute right-3.5 top-3 text-xs text-slate-400 font-bold pointer-events-none">bungkus</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Diisi berdasarkan informasi stok tersisa dari warung.</p>
            </div>

            <!-- Hidden Titip Baru & Penarikan Stok Lama (Otomatis ditarik saat restok) -->
            <input type="hidden" id="jumlah_titip" name="jumlah_titip" value="{{ old('jumlah_titip', 10) }}">
            <span class="hidden">Titip Baru</span>

            <!-- Tombol Simpan -->
            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-200">
                <a href="{{ route('admin.kunjungan.index') }}" class="app-btn-secondary px-4 py-2.5 text-xs font-bold">
                    Batal
                </a>
                <button type="submit" class="app-btn-primary px-6 py-2.5 text-xs font-black shadow-sm cursor-pointer">
                    Simpan Realisasi Distribusi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectWarung = document.getElementById('warung_id');
        const inputDititipkan = document.getElementById('stok_ditaro');
        const inputSisa = document.getElementById('sisa_fisik');
        const inputWithdrawn = document.getElementById('old_stock_withdrawn');
        const inputTerjualHidden = document.getElementById('jumlah_laku');
        const inputTambah = document.getElementById('jumlah_titip');

        function hitungOtomatis() {
            let taro = parseInt(inputDititipkan.value || 0, 10);
            let sisaVal = inputSisa.value.trim();
            if (sisaVal === '') {
                inputTerjualHidden.value = 0;
                if (inputWithdrawn) inputWithdrawn.value = 0;
                return;
            }

            let sisa = parseInt(sisaVal, 10);
            if (isNaN(sisa) || sisa < 0) sisa = 0;
            if (sisa > taro && taro > 0) {
                sisa = taro;
                inputSisa.value = sisa;
            }

            // Pasti barang lama ditarik/diambil seluruhnya saat restok
            if (inputWithdrawn) {
                inputWithdrawn.value = sisa;
            }

            let terjual = Math.max(0, taro - sisa);
            inputTerjualHidden.value = terjual;

            // Hitung persis rekomendasi titip baru sesuai algoritma Periodic Review
            let persen = taro > 0 ? Math.round((terjual / taro) * 100) : 0;
            let titipBaru = 0;
            if (taro <= 0) {
                titipBaru = 30;
            } else if (sisa === 0) {
                titipBaru = Math.max(35, terjual + 10);
            } else if (persen >= 80 || sisa <= 5) {
                titipBaru = Math.max(30, terjual + 8);
            } else if (persen >= 50) {
                titipBaru = Math.max(20, terjual + 5);
            } else if (persen >= 25) {
                titipBaru = Math.max(15, terjual + 3);
            } else {
                titipBaru = 0; // Macet / Tahan: 0 pcs
            }

            inputTambah.value = titipBaru;
        }

        function updateWarung() {
            const opt = selectWarung.options[selectWarung.selectedIndex];
            if (opt && opt.value) {
                const stokLalu = parseInt(opt.getAttribute('data-stok-lalu') || 0, 10);
                const saran = parseInt(opt.getAttribute('data-saran') || 0, 10);
                inputDititipkan.value = stokLalu;
                inputTambah.value = saran;
                inputSisa.value = '';
                inputTerjualHidden.value = 0;
                if (inputWithdrawn) inputWithdrawn.value = 0;
            }
        }

        selectWarung.addEventListener('change', updateWarung);
        inputSisa.addEventListener('input', hitungOtomatis);

        if (inputSisa.value.trim() !== '') {
            hitungOtomatis();
        }
    });
</script>
@endpush
