<?php

namespace App\Http\Controllers;

use App\Models\StockConfirmation;
use App\Models\Warung;
use Illuminate\Http\Request;

class StockConfirmationController extends Controller
{
    /**
     * Tampilkan daftar konfirmasi stok pra-kunjungan via WhatsApp
     */
    public function index(Request $request)
    {
        $selectedWarungId = $request->query('warung_id');
        $warungs = Warung::where('partnership_status', 'aktif')
            ->where('is_aktif', true)
            ->with(['stockConfirmations' => function ($q) {
                $q->orderBy('confirmation_date', 'desc');
            }])
            ->orderBy('nama_warung')
            ->get();

        $recentConfirmations = StockConfirmation::with('warung')
            ->orderBy('confirmation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.konfirmasi_stok.index', compact('warungs', 'recentConfirmations', 'selectedWarungId'));
    }

    /**
     * Simpan hasil konfirmasi stok via WhatsApp H-2 sebelum kunjungan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warung_id' => 'required|exists:warungs,id',
            'confirmation_date' => 'nullable|date',
            'reported_remaining_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $warung = Warung::findOrFail($validated['warung_id']);
        $confDate = $validated['confirmation_date'] ?? date('Y-m-d');

        $confirmation = StockConfirmation::create([
            'supplier_id' => $warung->supplier_id ?: 1,
            'warung_id' => $warung->id,
            'confirmation_date' => $confDate,
            'reported_remaining_stock' => $validated['reported_remaining_stock'],
            'physically_checked_stock' => null,
            'data_status' => 'menunggu_pemeriksaan',
            'notes' => $validated['notes'] ?: 'Konfirmasi stok via WhatsApp H-2 sebelum kunjungan.',
        ]);

        // Perbarui sisa stok estimasi di warung
        $warung->stok_saat_ini = $validated['reported_remaining_stock'];
        $warung->save();

        return redirect()->route('admin.konfirmasi-stok.index')
            ->with('success', "Konfirmasi stok untuk {$warung->nama_warung} ({$validated['reported_remaining_stock']} bungkus) berhasil disimpan.");
    }
}
