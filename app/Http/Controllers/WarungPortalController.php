<?php

namespace App\Http\Controllers;

use App\Models\PengajuanStok;
use App\Models\Product;
use App\Models\StockConfirmation;
use App\Models\StockRequest;
use App\Models\Warung;
use App\Services\CrmService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WarungPortalController extends Controller
{
    public function index()
    {
        $warungs = Warung::where('partnership_status', 'aktif')->where('is_aktif', true)->orderBy('nama_warung', 'asc')->get();

        return view('portal.index', compact('warungs'));
    }

    public function authByPin(Request $request)
    {
        $validated = $request->validate([
            'warung_id' => 'required',
            'pin' => 'required|string',
        ]);

        if (is_numeric($validated['warung_id'])) {
            $warung = Warung::find($validated['warung_id']);
        } else {
            $warung = Warung::where('kode_unik', strtoupper($validated['warung_id']))
                ->orWhere('nama_warung', 'ILIKE', '%' . $validated['warung_id'] . '%')
                ->first();
        }

        if (!$warung) {
            return back()->withErrors(['warung_id' => 'Warung tidak ditemukan. Pilih warung yang terdaftar.'])->withInput();
        }

        if ($warung->pin !== $validated['pin']) {
            return back()->withErrors(['pin' => 'PIN salah! Silakan periksa kembali PIN warung Anda.'])->withInput();
        }

        session(['portal_warung_id' => $warung->id]);

        return redirect()->route('portal.show', $warung->kode_unik)
            ->with('success', "Selamat datang di Portal Mitra, {$warung->nama_pemilik} ({$warung->nama_warung})!");
    }

    public function show($kode_unik)
    {
        $warung = Warung::with(['pengajuanStoks' => function($q) {
            $q->orderBy('created_at', 'desc');
        }, 'stokPenjualans' => function($q) {
            $q->orderBy('created_at', 'desc')->limit(5);
        }, 'stockConfirmations' => function($q) {
            $q->orderBy('confirmation_date', 'desc')->limit(5);
        }, 'salesRecords', 'restocks'])->where('kode_unik', strtoupper($kode_unik))->firstOrFail();

        // Wajibkan autentikasi PIN kecuali jika admin login
        if (session('portal_warung_id') != $warung->id && !auth()->check()) {
            return redirect()->route('portal.index')
                ->with('selected_warung_id', $warung->id)
                ->withErrors(['pin' => "Masukkan PIN warung terlebih dahulu untuk mengakses data {$warung->nama_warung}."]);
        }

        $pengajuanAktif = $warung->pengajuanStoks()
            ->whereIn('status', ['pending', 'diproses'])
            ->latest()
            ->first();

        $daftarPengajuan = $warung->pengajuanStoks()->paginate(5);
        $kunjunganTerakhir = $warung->stokPenjualans->first();
        $singleProduct = Product::first();

        $latestConfirmation = $warung->stockConfirmations->first();
        $isConfirmedRecently = $latestConfirmation && Carbon::parse($latestConfirmation->confirmation_date)->diffInDays(now()) <= 7;

        // Jadwal distribusi berikutnya (siklus 14 hari)
        if ($kunjunganTerakhir && $kunjunganTerakhir->tanggal_cek) {
            $jadwalDistribusiNext = Carbon::parse($kunjunganTerakhir->tanggal_cek)->addDays(14);
        } else {
            $jadwalDistribusiNext = now()->addDays(7);
        }

        $daftarKonfirmasi = $warung->stockConfirmations()->latest('confirmation_date')->limit(5)->get();
        $daftarDistribusi = $warung->stokPenjualans()->latest('created_at')->limit(5)->get();

        return view('portal.show', compact(
            'warung', 
            'pengajuanAktif', 
            'daftarPengajuan', 
            'kunjunganTerakhir', 
            'singleProduct',
            'latestConfirmation',
            'isConfirmedRecently',
            'jadwalDistribusiNext',
            'daftarKonfirmasi',
            'daftarDistribusi'
        ));
    }

    public function submitKonfirmasi(Request $request, $kode_unik)
    {
        $warung = Warung::where('kode_unik', strtoupper($kode_unik))->firstOrFail();

        $validated = $request->validate([
            'confirmation_date' => 'required|date',
            'reported_remaining_stock' => 'required|integer|min:0|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);

        StockConfirmation::create([
            'supplier_id' => $warung->supplier_id ?: 1,
            'warung_id' => $warung->id,
            'confirmation_date' => $validated['confirmation_date'],
            'reported_remaining_stock' => $validated['reported_remaining_stock'],
            'physically_checked_stock' => null,
            'data_status' => 'menunggu_pemeriksaan',
            'notes' => $validated['notes'] ?: 'Konfirmasi mandiri via Portal Warung Mitra.',
        ]);

        $warung->stok_saat_ini = $validated['reported_remaining_stock'];
        $warung->save();

        return redirect()->route('portal.show', $warung->kode_unik)
            ->with('success', "Konfirmasi sisa stok ({$validated['reported_remaining_stock']} bungkus) berhasil dikirimkan!");
    }

    public function submitPengajuan(Request $request, $kode_unik)
    {
        $warung = Warung::where('kode_unik', strtoupper($kode_unik))->firstOrFail();

        $validated = $request->validate([
            'tanggal_pengajuan' => 'required|date',
            'jumlah_diminta' => 'required|integer|min:1|max:500',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $defaultProduct = Product::first();

        // 1. Simpan ke PengajuanStok (backward compatible)
        $pengajuan = PengajuanStok::create([
            'warung_id' => $warung->id,
            'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
            'jumlah_diminta' => $validated['jumlah_diminta'],
            'status' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // 2. Simpan ke StockRequest resmi
        StockRequest::create([
            'supplier_id' => $warung->supplier_id ?: 1,
            'warung_id' => $warung->id,
            'product_id' => $defaultProduct ? $defaultProduct->id : null,
            'requested_quantity' => $validated['jumlah_diminta'],
            'approved_quantity' => null,
            'reason' => $validated['catatan'] ?? 'Permintaan stok tambahan dari portal warung mitra.',
            'status' => 'menunggu',
            'requested_at' => now(),
        ]);

        // Evaluasi aturan CRM (Reorder alert)
        app(CrmService::class)->evaluateWarung($warung);

        return redirect()->route('portal.show', $warung->kode_unik)
            ->with('success', "Permintaan tambahan stok sebanyak {$pengajuan->jumlah_diminta} bungkus keripik pisang berhasil dikirimkan ke Pak Aceng!");
    }

    public function logout()
    {
        session()->forget('portal_warung_id');
        return redirect()->route('portal.index')->with('info', 'Anda telah keluar dari Portal Mitra Warung.');
    }
}
