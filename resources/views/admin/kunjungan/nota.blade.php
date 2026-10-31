@extends('layouts.admin')

@section('title', 'Bukti Sesi Konsinyasi #' . $kunjungan->id)

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    <!-- Top Action Bar (Hidden when printing) -->
    <div class="no-print flex items-center justify-between pb-2 border-b border-slate-200">
        <a href="{{ route('admin.kunjungan.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            Riwayat Sesi
        </a>

        <div class="flex items-center gap-2">
            @php
                $decision = $kunjungan->keputusan;
                $tgl = $kunjungan->tanggal_cek ? $kunjungan->tanggal_cek->format('d/m/Y') : date('d/m/Y');
                $waText = urlencode("Halo {$kunjungan->warung->nama_pemilik} ({$kunjungan->warung->nama_warung}), berikut rangkuman Sesi Konsinyasi:\n" .
                    "Tanggal: {$tgl}\n" .
                    "Stok Ditaro (Awal): {$kunjungan->stok_sebelumnya} pcs\n" .
                    "Sisa Fisik Dicek: {$kunjungan->sisa_fisik} pcs\n" .
                    "Terjual Sesi Ini: {$kunjungan->jumlah_laku} pcs\n" .
                    "Titip Baru: +{$kunjungan->jumlah_titip} pcs\n" .
                    "Total Stok Aktif di Warung: {$kunjungan->sisa_stok} pcs\n" .
                    "Keputusan Stok: {$decision['ringkasan']}\n" .
                    "Terima kasih!");
                $waPhone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $kunjungan->warung->kontak));
            @endphp
            <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" 
               target="_blank" 
               class="app-btn-secondary px-3 py-1.5 text-xs text-emerald-800">
                Kirim WhatsApp
            </a>

            <button onclick="window.print()" class="app-btn-primary px-3 py-1.5 text-xs font-bold">
                Cetak Bukti Sesi
            </button>
        </div>
    </div>

    <!-- Printable Receipt Card -->
    <div class="app-card p-5 sm:p-6 text-xs text-stone-700 bg-white">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-stone-200 pb-3.5 mb-3.5">
            <div>
                <h2 class="font-heading text-base font-black text-[#1C1917] tracking-tight">PARTNERSTOCK</h2>
                <span class="text-[11px] text-amber-700 font-bold block">Bukti Sesi Konsinyasi</span>
            </div>
            <div class="text-right">
                <span class="font-mono text-stone-800 font-bold block">#SESI-{{ str_pad($kunjungan->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[11px] text-stone-400">{{ $tgl }}</span>
            </div>
        </div>

        <!-- Warung Info -->
        <div class="grid grid-cols-2 gap-4 pb-3.5 mb-3.5 border-b border-stone-100 text-xs">
            <div>
                <span class="text-stone-400 block text-[10px] uppercase font-bold">Warung Mitra:</span>
                <strong class="text-stone-900 text-sm">{{ $kunjungan->warung->nama_warung }}</strong>
                <span class="text-stone-500 block">{{ $kunjungan->warung->nama_pemilik }}</span>
            </div>
            <div class="text-right">
                <span class="text-stone-400 block text-[10px] uppercase font-bold">Kontak &amp; Alamat:</span>
                <span class="text-stone-600 block truncate">{{ $kunjungan->warung->alamat }}</span>
                <span class="text-stone-500">{{ $kunjungan->warung->kontak }}</span>
            </div>
        </div>

        <!-- Sesi Breakdown Table -->
        <table class="w-full text-xs text-stone-700 mb-3.5">
            <thead class="bg-amber-50/60 border-y border-amber-200/80 text-stone-900 text-[11px] font-bold">
                <tr>
                    <th class="py-2.5 px-2 text-left">Produk</th>
                    <th class="py-2.5 px-2 text-center">Taro (Awal)</th>
                    <th class="py-2.5 px-2 text-center">Sisa (Dicek)</th>
                    <th class="py-2.5 px-2 text-center">Terjual</th>
                    <th class="py-2.5 px-2 text-center">Titip Baru</th>
                    <th class="py-2.5 px-2 text-center">Stok Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                <tr>
                    <td class="py-3 px-2 font-bold text-stone-900">Produk Konsinyasi</td>
                    <td class="py-3 px-2 text-center font-mono font-medium">{{ $kunjungan->stok_sebelumnya }}</td>
                    <td class="py-3 px-2 text-center font-mono font-bold text-stone-900">{{ $kunjungan->sisa_fisik }}</td>
                    <td class="py-3 px-2 text-center font-mono font-black text-emerald-600">{{ $kunjungan->jumlah_laku }}</td>
                    <td class="py-3 px-2 text-center font-mono font-black text-orange-600">+{{ $kunjungan->jumlah_titip }}</td>
                    <td class="py-3 px-2 text-center font-mono font-black text-stone-900">{{ $kunjungan->sisa_stok }} pcs</td>
                </tr>
            </tbody>
        </table>

        <!-- Keputusan Stok Box -->
        <div class="p-3 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50/60 border border-amber-200 text-xs mb-3.5">
            <div class="flex items-center justify-between pb-1 mb-1 border-b border-amber-200/60">
                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Keputusan Stok:</span>
                <span class="font-black text-orange-600 text-xs">{{ $decision['ringkasan'] }}</span>
            </div>
            <p class="text-[11px] text-stone-600 leading-relaxed">
                {{ $decision['alasan'] }}
            </p>
        </div>

        @if($kunjungan->catatan)
            <div class="p-2 rounded-lg bg-stone-50 border border-stone-100 text-xs text-stone-500 italic mb-3.5">
                Catatan: {{ $kunjungan->catatan }}
            </div>
        @endif

        <!-- Tanda Tangan -->
        <div class="grid grid-cols-2 gap-6 text-center text-xs text-stone-400 mt-6 pt-3 border-t border-stone-200">
            <div>
                <span class="block mb-8 text-[11px]">Penerima (Warung Mitra)</span>
                <span class="font-bold text-stone-900 block border-b border-stone-300 pb-1 max-w-[130px] mx-auto">
                    {{ $kunjungan->warung->nama_pemilik }}
                </span>
            </div>
            <div>
                <span class="block mb-8 text-[11px]">Pemasok / Distributor</span>
                <span class="font-bold text-stone-900 block border-b border-stone-300 pb-1 max-w-[130px] mx-auto">
                    Distributor
                </span>
            </div>
        </div>
    </div>

</div>
@endsection
