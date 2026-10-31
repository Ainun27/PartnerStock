<?php

namespace App\Http\Controllers;

use App\Models\PengajuanStok;
use App\Models\Product;
use App\Models\Restock;
use App\Models\SalesRecord;
use App\Models\StockConfirmation;
use App\Models\StockRequest;
use App\Models\StokPenjualan;
use App\Models\SystemSetting;
use App\Models\Warung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $warungId = $request->query('warung_id');
        $query = StokPenjualan::with('warung')->orderBy('created_at', 'desc');

        if ($warungId) {
            $query->where('warung_id', $warungId);
        }

        $kunjungans = $query->paginate(15)->withQueryString();
        $warungs = Warung::where('partnership_status', 'aktif')->where('is_aktif', true)->orderBy('nama_warung', 'asc')->get();

        // Ringkasan metrik riwayat
        $totalLaku = StokPenjualan::when($warungId, fn($q) => $q->where('warung_id', $warungId))->sum('jumlah_laku');
        $totalTitip = StokPenjualan::when($warungId, fn($q) => $q->where('warung_id', $warungId))->sum('jumlah_titip');
        $totalUang = StokPenjualan::when($warungId, fn($q) => $q->where('warung_id', $warungId))->sum('total_pendapatan');

        // 3 Metrik Utama: Rekomendasi Stok Sistem, Permintaan Tambahan Warung, Total Stok yang Harus Dibawa
        $recSummary = app(\App\Services\RecommendationService::class)->getAllRecommendations();
        if ($warungId) {
            $item = collect($recSummary['items'])->firstWhere('warung_id', (int) $warungId);
            $rekomendasiSistem = $item ? ($item['approved_quantity'] ?: $item['recommended_quantity']) : 0;
            $permintaanTambahan = (int) PengajuanStok::where('warung_id', $warungId)->whereIn('status', ['pending', 'diproses'])->sum('jumlah_diminta');
        } else {
            $rekomendasiSistem = $recSummary['total_approved'] ?: $recSummary['total_recommended'];
            $permintaanTambahan = (int) PengajuanStok::whereIn('status', ['pending', 'diproses'])->sum('jumlah_diminta');
        }
        $totalHarusDibawa = $rekomendasiSistem + $permintaanTambahan;

        return view('admin.kunjungan.index', compact(
            'kunjungans', 
            'warungs', 
            'warungId', 
            'totalLaku', 
            'totalTitip', 
            'totalUang',
            'rekomendasiSistem',
            'permintaanTambahan',
            'totalHarusDibawa'
        ));
    }

    public function create(Request $request)
    {
        $selectedWarungId = $request->query('warung_id');
        $pengajuanId = $request->query('pengajuan_id');

        $warungs = Warung::where('partnership_status', 'aktif')->where('is_aktif', true)->orderBy('nama_warung', 'asc')->get();

        $selectedWarung = null;
        $pengajuanTerkait = null;

        if ($selectedWarungId) {
            $selectedWarung = Warung::find($selectedWarungId);
        }

        if ($pengajuanId) {
            $pengajuanTerkait = PengajuanStok::find($pengajuanId);
            if ($pengajuanTerkait && !$selectedWarung) {
                $selectedWarung = $pengajuanTerkait->warung;
                $selectedWarungId = $selectedWarung->id;
            }
        }

        $consignmentMode = SystemSetting::get('consignment_mode', 'A');

        return view('admin.kunjungan.create', compact('warungs', 'selectedWarungId', 'selectedWarung', 'pengajuanTerkait', 'consignmentMode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warung_id' => 'required|exists:warungs,id',
            'tanggal_cek' => 'required|date',
            'stok_ditaro' => 'nullable|integer|min:0',
            'sisa_fisik' => 'nullable|integer|min:0',
            'jumlah_laku' => 'nullable|integer|min:0',
            'old_stock_withdrawn' => 'nullable|integer|min:0',
            'jumlah_titip' => 'nullable|integer|min:0',
            'consignment_mode' => 'nullable|in:A,B',
            'catatan' => 'nullable|string',
            'pengajuan_id' => 'nullable|exists:pengajuan_stoks,id',
        ]);

        $warung = Warung::findOrFail($validated['warung_id']);
        $product = Product::first();
        $consignmentMode = $validated['consignment_mode'] ?? SystemSetting::get('consignment_mode', 'A');

        return DB::transaction(function () use ($validated, $warung, $product, $consignmentMode) {
            // Stok ditaro awal sesi (default dari stok warung saat ini jika tidak diinput)
            $taro = isset($validated['stok_ditaro']) && $validated['stok_ditaro'] !== null
                ? (int) $validated['stok_ditaro']
                : (int) $warung->stok_saat_ini;

            // Hitung sisa fisik dan jumlah laku
            if (isset($validated['sisa_fisik']) && $validated['sisa_fisik'] !== null) {
                $sisaFisik = max(0, (int) $validated['sisa_fisik']);
                $jumlahLaku = max(0, $taro - $sisaFisik);
            } elseif (isset($validated['jumlah_laku']) && $validated['jumlah_laku'] !== null) {
                $jumlahLaku = max(0, (int) $validated['jumlah_laku']);
                $sisaFisik = max(0, $taro - $jumlahLaku);
            } else {
                $sisaFisik = 0;
                $jumlahLaku = $taro;
            }

            // Hitung keputusan stok per sesi
            $decision = StokPenjualan::hitungKeputusan($taro, $sisaFisik);
            $keputusanText = $decision['ringkasan'] . ' (' . $decision['label'] . ')';

            $jumlahTitip = isset($validated['jumlah_titip']) && $validated['jumlah_titip'] !== null && $validated['jumlah_titip'] !== ''
                ? (int) $validated['jumlah_titip']
                : max(10, (int) $decision['rekomendasi_titip']);

            // Jumlah stok lama yang ditarik (hanya jika diisi spesifik di form)
            $oldStockWithdrawn = isset($validated['old_stock_withdrawn']) && $validated['old_stock_withdrawn'] !== null
                ? (int) $validated['old_stock_withdrawn']
                : 0;

            // Stok akhir di warung setelah kunjungan
            $stokAkhir = max(0, $sisaFisik - $oldStockWithdrawn) + $jumlahTitip;

            $totalPendapatan = $jumlahLaku * $warung->harga_titip;

            // Simpan ke StokPenjualan (Pusat Transaksi Distribusi & Penjualan Konsinyasi)
            $kunjungan = StokPenjualan::create([
                'warung_id' => $warung->id,
                'product_id' => $product ? $product->id : null,
                'tipe_transaksi' => 'sesi',
                'tanggal_cek' => $validated['tanggal_cek'],
                'tanggal_titip' => $jumlahTitip > 0 ? $validated['tanggal_cek'] : null,
                'stok_sebelumnya' => $taro,
                'sisa_fisik' => $sisaFisik,
                'jumlah_laku' => $jumlahLaku,
                'jumlah_titip' => $jumlahTitip,
                'old_stock_withdrawn' => $oldStockWithdrawn,
                'sisa_stok' => $stokAkhir,
                'total_pendapatan' => $totalPendapatan,
                'catatan' => $validated['catatan'] ?? null,
                'keputusan_stok' => $keputusanText,
            ]);

            // 5. Perbarui sisa stok di warung mitra
            $warung->update([
                'stok_saat_ini' => $stokAkhir,
            ]);

            // 6. Tangani pengajuan jika ada
            if (!empty($validated['pengajuan_id'])) {
                $pengajuan = PengajuanStok::find($validated['pengajuan_id']);
                if ($pengajuan) {
                    $pengajuan->update([
                        'status' => 'selesai',
                        'catatan_pemasok' => 'Stok telah dikirim dan diserahterimakan pada sesi tanggal ' . $validated['tanggal_cek'],
                        'tanggal_diproses' => now(),
                    ]);
                }
            }

            // Tangani pengajuan stok pending untuk warung ini
            PengajuanStok::where('warung_id', $warung->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'selesai',
                    'tanggal_diproses' => now(),
                    'catatan_pemasok' => 'Dipenuhi saat kunjungan distribusi konsinyasi.',
                ]);

            return redirect()->route('admin.kunjungan.index')
                ->with('success', "Distribusi untuk {$warung->nama_warung} berhasil dicatat! Terjual: {$jumlahLaku} pcs, Titip Baru: {$jumlahTitip} pcs.");
        });
    }

    public function nota($id)
    {
        $kunjungan = StokPenjualan::with('warung')->findOrFail($id);
        return view('admin.kunjungan.nota', compact('kunjungan'));
    }

    /**
     * Update catatan keputusan distribusi secara inline pada tabel
     */
    public function updateNote(Request $request, $id)
    {
        $sesi = StokPenjualan::findOrFail($id);

        if ($request->has('catatan') || $request->has('note')) {
            $sesi->catatan = $request->input('catatan', $request->input('note', ''));
        }

        if ($request->filled('jumlah_titip')) {
            $newTitip = max(0, (int) $request->input('jumlah_titip'));
            $sesi->jumlah_titip = $newTitip;
            $sesi->sisa_stok = $sesi->sisa_fisik + $newTitip;
        }

        $sesi->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'note' => $sesi->catatan,
                'jumlah_titip' => $sesi->jumlah_titip,
                'sisa_stok' => $sesi->sisa_stok,
            ]);
        }

        return back()->with('success', "Catatan keputusan distribusi untuk {$sesi->warung->nama_warung} berhasil disimpan.");
    }
}
