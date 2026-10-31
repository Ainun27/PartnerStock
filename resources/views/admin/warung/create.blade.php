@extends('layouts.admin')

@section('title', 'Tambah Warung Mitra')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Tambah Warung Mitra</h1>
        </div>
        <a href="{{ route('admin.warung.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-amber-100/70 p-5 shadow-2xs">
        <form method="POST" action="{{ route('admin.warung.store') }}" class="space-y-3.5 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="nama_warung" class="block font-bold text-stone-800 mb-1">
                        Nama Warung <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama_warung" 
                           name="nama_warung" 
                           value="{{ old('nama_warung') }}" 
                           required 
                           placeholder="Contoh: Warung Bu Siti"
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 text-stone-900 font-bold outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="nama_pemilik" class="block font-bold text-stone-800 mb-1">
                        Nama Pemilik <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama_pemilik" 
                           name="nama_pemilik" 
                           value="{{ old('nama_pemilik') }}" 
                           required 
                           placeholder="Contoh: Bu Siti Aminah"
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 text-stone-900 font-medium outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="kontak" class="block font-bold text-stone-800 mb-1">
                        Kontak / WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="kontak" 
                           name="kontak" 
                           value="{{ old('kontak') }}" 
                           required 
                           placeholder="081234567890"
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white font-mono">
                </div>

                <div>
                    <label for="alamat" class="block font-bold text-stone-800 mb-1">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="alamat" 
                           name="alamat" 
                           value="{{ old('alamat') }}" 
                           required 
                           placeholder="Jl. Kaliurang Km 5"
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-stone-100">
                <div>
                    <label for="kode_unik" class="block font-bold text-stone-800 mb-1">
                        Kode Unik Portal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="kode_unik" 
                           name="kode_unik" 
                           value="{{ old('kode_unik', $suggestedCode) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-mono font-bold uppercase text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="pin" class="block font-bold text-stone-800 mb-1">
                        PIN Akses Portal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="pin" 
                           name="pin" 
                           value="{{ old('pin', '1234') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-mono font-bold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-2 border-t border-stone-100">
                <div>
                    <label for="stok_awal" class="block font-bold text-stone-800 mb-1">
                        Titip Awal (pcs)
                    </label>
                    <input type="number" 
                           id="stok_awal" 
                           name="stok_awal" 
                           value="{{ old('stok_awal', 20) }}" 
                           min="0" 
                           class="w-full px-3 py-2 rounded-xl border border-amber-200 font-black text-amber-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="harga_titip" class="block font-bold text-stone-800 mb-1">
                        Harga Titip (Rp)
                    </label>
                    <input type="number" 
                           id="harga_titip" 
                           name="harga_titip" 
                           value="{{ old('harga_titip', 12000) }}" 
                           min="0" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-semibold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="harga_jual" class="block font-bold text-stone-800 mb-1">
                        Harga Jual (Rp)
                    </label>
                    <input type="number" 
                           id="harga_jual" 
                           name="harga_jual" 
                           value="{{ old('harga_jual', 15000) }}" 
                           min="0" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-semibold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="pt-3 border-t border-stone-100 flex items-center justify-end gap-2">
                <a href="{{ route('admin.warung.index') }}" class="app-btn-secondary px-4 py-2 text-xs font-bold">
                    Batal
                </a>
                <button type="submit" class="app-btn-primary px-5 py-2 text-xs font-bold shadow-2xs cursor-pointer">
                    Simpan Warung
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
