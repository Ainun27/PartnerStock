@extends('layouts.admin')

@section('title', 'Permintaan Tambahan')

@section('content')
<div class="space-y-4">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Permintaan Tambahan Warung</h1>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 text-xs">
            <a href="{{ route('admin.pengajuan.index') }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition {{ !$status ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700' }}">
                Semua ({{ $counts['semua'] }})
            </a>
            <a href="{{ route('admin.pengajuan.index', ['status' => 'pending']) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition {{ $status === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700' }}">
                Pending ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.pengajuan.index', ['status' => 'diproses']) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition {{ $status === 'diproses' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700' }}">
                Diproses ({{ $counts['diproses'] }})
            </a>
            <a href="{{ route('admin.pengajuan.index', ['status' => 'selesai']) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition {{ $status === 'selesai' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700' }}">
                Selesai ({{ $counts['selesai'] }})
            </a>
        </div>
    </div>

    <!-- Tabel Permintaan Stok -->
    <div class="bg-white rounded-2xl border border-amber-100/70 overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-600">
                <thead class="bg-stone-50/80 text-stone-700 font-bold text-[11px] border-b border-stone-100">
                    <tr>
                        <th class="py-3 px-4">Tanggal Pesan</th>
                        <th class="py-3 px-4">Tiba (H+2)</th>
                        <th class="py-3 px-4">Warung Mitra</th>
                        <th class="py-3 px-4 text-center">Jumlah</th>
                        <th class="py-3 px-4">Catatan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($pengajuans as $item)
                        <tr class="hover:bg-amber-50/20 transition">
                            <td class="py-3 px-4 text-slate-700 font-mono text-[11px] whitespace-nowrap font-bold">
                                {{ $item->tanggal_pengajuan->format('d/m/Y') }}
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-amber-900">
                                {{ $item->estimasi_tiba ? $item->estimasi_tiba->format('d/m/Y') : '-' }}
                            </td>

                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $item->warung->nama_warung }}
                                <span class="text-[10px] text-slate-500 block font-normal">{{ $item->warung->nama_pemilik }}</span>
                            </td>

                            <td class="py-3 px-4 text-center font-black text-amber-900 text-sm">
                                +{{ $item->jumlah_diminta }}
                            </td>

                            <td class="py-3 px-4 text-slate-700 max-w-xs truncate font-medium" title="{{ $item->catatan }}">
                                {{ $item->catatan ?: '-' }}
                            </td>

                            <!-- Kolom Status Tunggal (Dapat Langsung Dipilih Statusnya) -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.pengajuan.updateStatus', $item->id) }}" class="inline-block">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" 
                                            class="text-xs px-2.5 py-1.5 rounded-xl border font-black outline-none cursor-pointer shadow-xs transition {{ $item->status === 'pending' ? 'bg-amber-100 text-amber-900 border-amber-400' : ($item->status === 'diproses' ? 'bg-blue-100 text-blue-900 border-blue-400' : 'bg-emerald-100 text-emerald-900 border-emerald-400') }}">
                                        <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="diproses" {{ $item->status === 'diproses' ? 'selected' : '' }}>Diproses</option>
                                        <option value="selesai" {{ $item->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-500 font-medium">
                                Tidak ada data permintaan stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
