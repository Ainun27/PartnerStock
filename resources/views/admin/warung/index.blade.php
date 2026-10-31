@extends('layouts.admin')

@section('title', 'Mitra Warung')

@section('content')
<div class="space-y-5">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Mitra Warung</h1>
        </div>

        <a href="{{ route('admin.warung.create') }}" class="app-btn-primary px-4 py-2 text-xs font-bold shadow-md cursor-pointer">
            + Warung Baru
        </a>
    </div>

    <!-- Search & Summary Bar -->
    <div class="app-card p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.warung.index') }}" class="flex items-center gap-2 w-full sm:w-80">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Cari warung atau pemilik..."
                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:border-amber-500 bg-white font-bold">
            <button type="submit" class="app-btn-secondary px-3.5 py-2 text-xs font-bold whitespace-nowrap cursor-pointer">
                Cari
            </button>
            @if($search)
                <a href="{{ route('admin.warung.index') }}" class="text-xs text-rose-600 font-bold hover:underline">Reset</a>
            @endif
        </form>

        <span class="text-xs text-slate-700 font-bold px-2">
            Total: <strong class="text-slate-900 font-black">{{ $warungs->total() }}</strong> warung
        </span>
    </div>

    <!-- Grid Kotak-Kotak Mitra Warung (Luminous Pure White Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($warungs as $warung)
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-md shadow-slate-200/60 hover:shadow-lg hover:border-amber-400/60 transition-all duration-200 flex flex-col justify-between gap-3.5">
                
                <!-- Baris Atas: Nama Warung, Status & Tombol Edit / Hapus -->
                <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                    <h2 class="font-heading font-black text-slate-900 text-sm sm:text-base leading-snug">
                        {{ $warung->nama_warung }}
                    </h2>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $warung->is_aktif ? 'bg-emerald-100 text-emerald-950 border border-emerald-400' : 'bg-slate-200 text-slate-800 border border-slate-300' }}">
                            {{ $warung->is_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <a href="{{ route('admin.warung.edit', $warung->id) }}" 
                           class="app-btn-secondary px-2.5 py-1 text-xs font-bold cursor-pointer">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.warung.destroy', $warung->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mitra {{ addslashes($warung->nama_warung) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    title="Hapus Mitra Warung"
                                    class="px-2.5 py-1 text-xs font-bold rounded-xl text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 hover:border-rose-600 transition cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Bagian Tengah: Pemilik, Ikon WhatsApp (Hanya Simbol WA Murni), dan Alamat -->
                <div class="py-0.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-slate-900 font-extrabold text-xs block truncate">
                                {{ $warung->nama_pemilik }}
                            </span>
                            <p class="text-slate-600 text-xs font-medium leading-relaxed line-clamp-2 mt-0.5" title="{{ $warung->alamat }}">
                                {{ $warung->alamat ?: '-' }}
                            </p>
                        </div>
                        @if($warung->kontak)
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $warung->kontak)) }}" 
                               target="_blank" 
                               class="w-9 h-9 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white flex items-center justify-center shadow-xs hover:scale-105 transition-all shrink-0 cursor-pointer">
                                <!-- Simbol WhatsApp Murni Saja Tanpa Judul/Teks -->
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Baris Bawah: PIN Mitra (Background Gradien Sesuai [+ Warung Baru]) -->
                <div class="pt-2.5 border-t border-slate-100">
                    <div class="rounded-xl py-2 px-3.5 shadow-xs flex items-center justify-between" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
                        <span class="text-xs font-black text-white/95">PIN Mitra</span>
                        <span class="font-mono text-base font-black text-white tracking-widest drop-shadow-xs">
                            {{ $warung->pin ?? '1234' }}
                        </span>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-600 font-bold text-xs shadow-sm">
                Tidak ada data mitra warung ditemukan.
            </div>
        @endforelse
    </div>

    @if($warungs->hasPages())
        <div class="pt-2">
            {{ $warungs->links() }}
        </div>
    @endif

</div>
@endsection
