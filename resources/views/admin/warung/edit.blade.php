@extends('layouts.admin')

@section('title', 'Edit ' . $warung->nama_warung)

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Edit Warung Mitra</h1>
        </div>
        <a href="{{ route('admin.warung.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-amber-100/70 p-5 shadow-2xs">
        <form method="POST" action="{{ route('admin.warung.update', $warung->id) }}" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="nama_warung" class="block font-bold text-stone-800 mb-1">Nama Warung</label>
                    <input type="text" 
                           id="nama_warung" 
                           name="nama_warung" 
                           value="{{ old('nama_warung', $warung->nama_warung) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="nama_pemilik" class="block font-bold text-stone-800 mb-1">Nama Pemilik</label>
                    <input type="text" 
                           id="nama_pemilik" 
                           name="nama_pemilik" 
                           value="{{ old('nama_pemilik', $warung->nama_pemilik) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-medium text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="kontak" class="block font-bold text-stone-800 mb-1">Kontak / WhatsApp</label>
                    <input type="text" 
                           id="kontak" 
                           name="kontak" 
                           value="{{ old('kontak', $warung->kontak) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-mono text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="alamat" class="block font-bold text-stone-800 mb-1">Alamat</label>
                    <input type="text" 
                           id="alamat" 
                           name="alamat" 
                           value="{{ old('alamat', $warung->alamat) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-stone-100">
                <div>
                    <label for="kode_unik" class="block font-bold text-stone-800 mb-1">Kode Unik Portal</label>
                    <input type="text" 
                           id="kode_unik" 
                           name="kode_unik" 
                           value="{{ old('kode_unik', $warung->kode_unik) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-mono font-bold uppercase text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="pin" class="block font-bold text-stone-800 mb-1">PIN Portal</label>
                    <input type="text" 
                           id="pin" 
                           name="pin" 
                           value="{{ old('pin', $warung->pin) }}" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-mono font-bold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-2 border-t border-stone-100">
                <div>
                    <label for="harga_titip" class="block font-bold text-stone-800 mb-1">Harga Titip (Rp)</label>
                    <input type="number" 
                           id="harga_titip" 
                           name="harga_titip" 
                           value="{{ old('harga_titip', $warung->harga_titip) }}" 
                           min="0" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-semibold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="harga_jual" class="block font-bold text-stone-800 mb-1">Harga Jual (Rp)</label>
                    <input type="number" 
                           id="harga_jual" 
                           name="harga_jual" 
                           value="{{ old('harga_jual', $warung->harga_jual) }}" 
                           min="0" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-stone-200 font-semibold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div>
                    <label for="is_aktif" class="block font-bold text-stone-800 mb-1">Status Kemitraan</label>
                    <select id="is_aktif" 
                            name="is_aktif" 
                            class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-900 outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                        <option value="1" {{ old('is_aktif', $warung->is_aktif) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_aktif', $warung->is_aktif) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                <button type="button" 
                        onclick="if(confirm('Apakah Anda yakin ingin menghapus warung {{ addslashes($warung->nama_warung) }}?')) { document.getElementById('deleteWarungForm').submit(); }"
                        class="px-3.5 py-2 text-xs font-bold rounded-xl text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 hover:border-rose-600 transition cursor-pointer">
                    Hapus Mitra
                </button>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.warung.index') }}" class="app-btn-secondary px-4 py-2 text-xs font-bold">
                        Batal
                    </a>
                    <button type="submit" class="app-btn-primary px-5 py-2 text-xs font-bold shadow-2xs cursor-pointer">
                        Perbarui Data
                    </button>
                </div>
            </div>
        </form>

        <form id="deleteWarungForm" method="POST" action="{{ route('admin.warung.destroy', $warung->id) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

</div>
@endsection
