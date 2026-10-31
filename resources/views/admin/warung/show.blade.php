@extends('layouts.admin')

@section('title', $warung->nama_warung)

@section('content')
<div class="space-y-4">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm border border-slate-800">
        <div>
            <div class="flex items-center gap-2 mb-1 text-xs">
                <a href="{{ route('admin.warung.index') }}" class="text-slate-400 hover:text-white font-semibold">Mitra Warung</a>
                <span class="text-slate-600">/</span>
                <span class="text-amber-400 font-bold">{{ $warung->nama_warung }}</span>
            </div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">{{ $warung->nama_warung }}</h1>
            <p class="text-xs text-slate-300 mt-0.5">
                {{ $warung->nama_pemilik }} &bull; {{ $warung->alamat }} &bull; 
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $warung->kontak)) }}" target="_blank" class="text-emerald-400 hover:underline font-bold">
                    WA: {{ $warung->kontak }}
                </a>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.warung.edit', $warung->id) }}" class="app-btn-secondary px-3 py-1.5 text-xs font-bold shadow-2xs">
                Edit Warung
            </a>
        </div>
    </div>

    <!-- 3 Ringkasan Metrik Singkat -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-amber-100/70 shadow-2xs">
            <span class="text-[11px] font-bold text-stone-500 block">Sisa Stok Fisik</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="font-heading text-2xl font-black {{ $warung->stok_saat_ini <= 3 ? 'text-red-600' : 'text-stone-900' }}">
                    {{ $warung->stok_saat_ini }}
                </span>
                <span class="text-xs text-stone-400 font-semibold">pcs</span>
            </div>
            <span class="text-[10px] text-stone-400 mt-0.5 block">Status: {{ strtoupper($rekomendasi['status_urgensi']) }}</span>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-amber-100/70 shadow-2xs">
            <span class="text-[11px] font-bold text-stone-500 block">Rata-rata Terjual</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="font-heading text-2xl font-black text-stone-900">{{ $warung->rata_rata_laku }}</span>
                <span class="text-xs text-stone-400 font-semibold">pcs / sesi</span>
            </div>
            <span class="text-[10px] text-stone-400 mt-0.5 block">Tren penjualan mitra</span>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-amber-100/70 shadow-2xs">
            <span class="text-[11px] font-bold text-stone-500 block">Total Terjual</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="font-heading text-2xl font-black text-emerald-600">{{ $warung->total_terjual }}</span>
                <span class="text-xs text-stone-400 font-semibold">pcs</span>
            </div>
            <span class="text-[10px] text-stone-400 mt-0.5 block">Akumulasi penjualan</span>
        </div>
    </div>

    <!-- Rekomendasi Restock & Info Portal -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
        <div class="bg-white rounded-2xl border border-amber-100/70 p-4 shadow-2xs space-y-1">
            <div class="flex items-center gap-2">
                <span class="font-bold text-stone-800">Saran Tambah Stok:</span>
                <span class="font-black text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded text-xs">
                    +{{ $rekomendasi['rekomendasi_titip'] }} pcs
                </span>
            </div>
            <p class="text-[11px] text-stone-500">{{ $rekomendasi['keterangan'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-amber-100/70 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="font-bold text-stone-800 block">Portal Mitra Warung:</span>
                <span class="text-[11px] text-stone-500">Kode: <code class="font-bold text-amber-800">{{ $warung->kode_unik }}</code> &bull; PIN: <code class="font-bold">{{ $warung->pin }}</code></span>
            </div>
            <a href="{{ route('portal.show', $warung->kode_unik) }}" target="_blank" class="app-btn-secondary px-3.5 py-2 text-xs font-bold shadow-2xs">
                Buka Portal
            </a>
        </div>
    </div>

    <!-- Riwayat Sesi Kunjungan -->
    <div class="bg-white rounded-2xl border border-amber-100/70 overflow-hidden shadow-2xs">
        <div class="p-3 border-b border-stone-100 bg-stone-50/50">
            <h2 class="text-xs font-black text-[#2E170B] uppercase tracking-wider">Riwayat Distribusi Warung Ini</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-600">
                <thead class="bg-stone-50/80 text-stone-700 font-bold text-[11px] border-b border-stone-100">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-center">Taro</th>
                        <th class="py-3 px-4 text-center">Sisa</th>
                        <th class="py-3 px-4 text-center">Terjual</th>
                        <th class="py-3 px-4 text-center">Titip Baru</th>
                        <th class="py-3 px-4 text-center">Stok Akhir</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Nota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($riwayatStok as $log)
                        @php $decision = $log->keputusan; @endphp
                        <tr class="hover:bg-amber-50/20 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-stone-700 font-mono text-[11px]">
                                {{ $log->tanggal_cek ? $log->tanggal_cek->format('d/m/Y') : ($log->tanggal_titip ? $log->tanggal_titip->format('d/m/Y') : $log->created_at->format('d/m/Y')) }}
                            </td>
                            <td class="py-3 px-4 text-center font-medium">{{ $log->stok_sebelumnya }}</td>
                            <td class="py-3 px-4 text-center font-medium text-stone-700">{{ $log->sisa_fisik }}</td>
                            <td class="py-3 px-4 text-center font-black text-emerald-600">
                                {{ $log->jumlah_laku > 0 ? $log->jumlah_laku : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-amber-800">
                                {{ $log->jumlah_titip > 0 ? '+' . $log->jumlah_titip : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-black text-stone-900">
                                {{ $log->sisa_stok }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $decision['badge_color'] }}">
                                    {{ $decision['ringkasan'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.kunjungan.nota', $log->id) }}" class="app-btn-secondary px-2.5 py-1 text-[11px] font-bold">
                                    Nota
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-stone-400">Belum ada catatan sesi konsinyasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayatStok->hasPages())
            <div class="p-3 border-t border-stone-100 bg-stone-50/50">
                {{ $riwayatStok->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
