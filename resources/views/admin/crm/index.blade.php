@extends('layouts.admin')

@section('title', 'Peringatan Mitra')

@section('content')
<div class="space-y-5">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-sm border border-slate-800">
        <div>
            <h1 class="font-heading text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Peringatan Mitra (Pusat CRM)</h1>
        </div>

        <a href="{{ route('admin.pengajuan.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition shadow-xs">
            Permintaan Stok (PO)
        </a>
    </div>

    <!-- 4 Ringkasan Angka CRM -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="kpi-card">
            <span class="text-xs font-bold text-slate-500 block">Peringatan Aktif</span>
            <span class="font-heading text-3xl font-black text-slate-900 block mt-1">{{ $summary['total_active_alerts'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Perlu perhatian</span>
        </div>

        <div class="kpi-card">
            <span class="text-xs font-bold text-rose-600 block">Penurunan Penjualan</span>
            <span class="font-heading text-3xl font-black text-rose-600 block mt-1">{{ $summary['churn_alerts_count'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Indikasi churn</span>
        </div>

        <div class="kpi-card">
            <span class="text-xs font-bold text-slate-700 block">Permintaan Stok</span>
            <span class="font-heading text-3xl font-black text-slate-900 block mt-1">{{ $summary['reorder_alerts_count'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">PO mitra warung</span>
        </div>

        <div class="kpi-card">
            <span class="text-xs font-bold text-emerald-600 block">Selesai</span>
            <span class="font-heading text-3xl font-black text-emerald-600 block mt-1">{{ $summary['resolved_count'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Telah ditangani</span>
        </div>
    </div>

    <!-- Tab Navigasi 3 Bagian -->
    <div class="app-card p-1.5 flex gap-1.5 text-xs font-bold">
        <a href="{{ route('admin.crm.index', ['tab' => 'churn_prevention']) }}" 
           class="flex-1 py-2 px-3 rounded-xl text-center transition {{ $activeTab === 'churn_prevention' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
            <span>Penurunan Penjualan (Churn)</span>
            @if($summary['churn_alerts_count'] > 0)
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'churn_prevention' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700' }}">
                    {{ $summary['churn_alerts_count'] }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.crm.index', ['tab' => 'data_mining']) }}" 
           class="flex-1 py-2 px-3 rounded-xl text-center transition {{ $activeTab === 'data_mining' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
            <span>Pola Penjualan &amp; WMA</span>
        </a>

        <a href="{{ route('admin.crm.index', ['tab' => 'reorder_recommendation']) }}" 
           class="flex-1 py-2 px-3 rounded-xl text-center transition {{ $activeTab === 'reorder_recommendation' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
            <span>Rekomendasi &amp; PO</span>
        </a>
    </div>

    <!-- TAB 1: CHURN PREVENTION -->
    @if($activeTab === 'churn_prevention')
    <div class="space-y-3">
        <!-- Filter Status -->
        <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-700">Daftar Peringatan</span>
            <div class="flex items-center gap-1.5">
                <a href="{{ route('admin.crm.index', ['tab' => 'churn_prevention', 'status' => 'all']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Semua</a>
                <a href="{{ route('admin.crm.index', ['tab' => 'churn_prevention', 'status' => 'perlu_dihubungi']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusFilter === 'perlu_dihubungi' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Perlu Dihubungi</a>
                <a href="{{ route('admin.crm.index', ['tab' => 'churn_prevention', 'status' => 'dalam_tindak_lanjut']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusFilter === 'dalam_tindak_lanjut' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Proses</a>
                <a href="{{ route('admin.crm.index', ['tab' => 'churn_prevention', 'status' => 'selesai']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusFilter === 'selesai' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Selesai</a>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($churnAlerts as $alert)
                @php $badge = $alert->status_badge; @endphp
                <div class="app-card p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-900 text-sm">{{ $alert->warung ? $alert->warung->nama_warung : 'Warung Mitra' }}</span>
                            @if(isset($alert->evidence_data['drop_percentage']))
                                <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold text-[10px] border border-rose-200">
                                    Turun -{{ $alert->evidence_data['drop_percentage'] }}%
                                </span>
                            @endif
                            @if(isset($alert->evidence_data['sales_history']))
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]">
                                    Tren: {{ implode(' → ', $alert->evidence_data['sales_history']) }} pcs
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Pemilik: <strong class="text-slate-600">{{ $alert->warung ? $alert->warung->owner_name : '-' }}</strong> &bull; Terdeteksi: {{ $alert->created_at->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                        <button onclick="openFollowUpModal({{ $alert->id }}, '{{ addslashes($alert->warung->nama_warung ?? '') }}', '{{ $alert->status }}')" 
                                class="app-btn-primary px-3 py-1.5 text-xs font-bold shadow-xs">
                            Tindak Lanjut
                        </button>
                    </div>
                </div>
            @empty
                <div class="app-card p-8 text-center text-xs text-slate-400">
                    Tidak ada peringatan penurunan penjualan yang aktif saat ini.
                </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- TAB 2: DATA MINING -->
    @if($activeTab === 'data_mining')
    <div class="app-card overflow-hidden">
        <div class="p-3 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Pola Penjualan 3 Periode</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Warung Mitra</th>
                        <th class="py-3 px-4">3 Periode (X₁, X₂, X₃)</th>
                        <th class="py-3 px-4">Tren</th>
                        <th class="py-3 px-4 text-center">WMA</th>
                        <th class="py-3 px-4 text-center">Rekomendasi</th>
                        <th class="py-3 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($dataMiningList as $dm)
                        @php
                            $an = $dm['analysis'];
                            $rec = $dm['recommendation'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $dm['warung']->nama_warung }}
                            </td>
                            <td class="py-3 px-4 font-mono font-medium text-slate-700">
                                @if(count($an['sales_values']) > 0)
                                    {{ implode(', ', $an['sales_values']) }} pcs
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                    {{ $an['trend'] === 'meningkat' ? 'bg-emerald-50 text-emerald-700' : ($an['trend'] === 'menurun' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $an['trend_label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-black text-amber-700">
                                {{ $an['forecast'] }} pcs
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                {{ $rec['recommended_quantity'] }} pcs
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $rec['status_badge']['color'] }}">
                                    {{ $rec['status_badge']['label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- TAB 3: REORDER & PO -->
    @if($activeTab === 'reorder_recommendation')
    <div class="space-y-4">
        <!-- Card Muatan Ringkas -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-5 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Total Muatan Rutin</span>
                <span class="font-heading text-3xl font-black text-white block mt-1">
                    {{ $reorderRecs['total_approved'] ?: $reorderRecs['total_recommended'] }} <span class="text-base font-medium text-slate-300">bungkus</span>
                </span>
                <span class="text-xs text-slate-400 mt-0.5 block">
                    Mode Aktif: <strong>{{ $reorderRecs['mode'] === 'A' ? 'Mode A (Tarik Stok Lama)' : 'Mode B (Stok Lama Dijual)' }}</strong>
                </span>
            </div>

            <a href="{{ route('admin.rekomendasi.index') }}" class="app-btn-secondary px-4 py-2 text-xs font-bold">
                Rencana Pengiriman
            </a>
        </div>

        <!-- Tabel PO Permintaan Stok Menunggu Persetujuan -->
        <div class="app-card overflow-hidden">
            <div class="p-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Permintaan Stok Tambahan Menunggu</h3>
                <a href="{{ route('admin.pengajuan.index') }}" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[11px] font-bold transition">
                    Semua PO
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Warung Mitra</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4">Alasan</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingStockRequests as $req)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $req->warung ? $req->warung->nama_warung : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-black text-amber-700">
                                    {{ $req->requested_quantity }} bungkus
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $req->reason ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                                    {{ $req->requested_at ? $req->requested_at->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.pengajuan.index') }}" class="app-btn-primary px-3 py-1 text-xs">
                                        Proses
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Tidak ada permintaan stok yang tertunda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Modal Catat Tindak Lanjut CRM -->
<div id="followUpModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-3.5">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <h3 class="font-heading text-sm font-black text-slate-900">Catat Tindak Lanjut</h3>
                <p id="modalWarungName" class="text-xs text-slate-500 font-semibold mt-0.5"></p>
            </div>
            <button onclick="closeFollowUpModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form id="followUpForm" method="POST" action="" class="space-y-3 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-800 mb-1">Metode Kontak</label>
                <select name="contact_method" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-900 outline-none focus:border-amber-500" required>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="telepon">Panggilan Telepon</option>
                    <option value="kunjungan_langsung">Kunjungan Langsung</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-800 mb-1">Catatan Komunikasi <span class="text-red-500">*</span></label>
                <textarea name="result_notes" rows="2" placeholder="Hasil kontak mitra..." 
                          class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white outline-none focus:border-amber-500" required></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-800 mb-1">Rencana Berikutnya (Opsional)</label>
                <input type="text" name="next_action" placeholder="Contoh: Cek saat kunjungan"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block font-bold text-slate-800 mb-1">Status Penanganan</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-900 outline-none focus:border-amber-500" required>
                    <option value="dalam_tindak_lanjut">Dalam Tindak Lanjut</option>
                    <option value="sudah_dihubungi">Sudah Dihubungi</option>
                    <option value="selesai">Selesai</option>
                    <option value="perlu_dihubungi">Perlu Dihubungi Ulang</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeFollowUpModal()" class="px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="app-btn-primary px-4 py-1.5 text-xs font-bold">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openFollowUpModal(alertId, warungName, currentStatus) {
        const modal = document.getElementById('followUpModal');
        const form = document.getElementById('followUpForm');
        const nameEl = document.getElementById('modalWarungName');

        form.action = `/admin/crm/${alertId}/follow-up`;
        nameEl.textContent = `Mitra: ${warungName}`;
        modal.classList.remove('hidden');
    }

    function closeFollowUpModal() {
        document.getElementById('followUpModal').classList.add('hidden');
    }
</script>
@endpush
