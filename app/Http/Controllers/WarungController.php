<?php

namespace App\Http\Controllers;

use App\Models\StokPenjualan;
use App\Models\Warung;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarungController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Warung::withCount(['stokPenjualans', 'pengajuanStoks']);

        if ($status && $status !== 'all') {
            $query->where('partnership_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_warung', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%")
                  ->orWhere('nama_pemilik', 'ilike', "%{$search}%")
                  ->orWhere('owner_name', 'ilike', "%{$search}%")
                  ->orWhere('alamat', 'ilike', "%{$search}%")
                  ->orWhere('kode_unik', 'ilike', "%{$search}%");
            });
        }

        $warungs = $query->orderBy('nama_warung', 'asc')->paginate(10)->withQueryString();

        return view('admin.warung.index', compact('warungs', 'search', 'status'));
    }

    public function create()
    {
        $count = Warung::withTrashed()->count() + 1;
        $suggestedCode = 'WRG-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        return view('admin.warung.create', compact('suggestedCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_warung' => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'required|string|max:50',
            'kode_unik' => 'required|string|max:50|unique:warungs,kode_unik',
            'pin' => 'required|string|min:4|max:10',
            'harga_titip' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok_awal' => 'nullable|integer|min:0',
            'partnership_start_date' => 'nullable|date',
            'delivery_interval_days' => 'nullable|integer|min:1|max:60',
            'partnership_status' => 'nullable|in:aktif,nonaktif,ditinjau',
            'catatan' => 'nullable|string',
        ]);

        $stokAwal = (int) ($validated['stok_awal'] ?? 0);

        $warung = Warung::create([
            'supplier_id' => 1,
            'name' => $validated['nama_warung'],
            'nama_warung' => $validated['nama_warung'],
            'owner_name' => $validated['nama_pemilik'],
            'nama_pemilik' => $validated['nama_pemilik'],
            'alamat' => $validated['alamat'],
            'phone' => $validated['kontak'],
            'kontak' => $validated['kontak'],
            'kode_unik' => strtoupper(Str::slug($validated['kode_unik'])),
            'pin' => $validated['pin'],
            'harga_titip' => $validated['harga_titip'],
            'harga_jual' => $validated['harga_jual'],
            'stok_saat_ini' => $stokAwal,
            'partnership_start_date' => $validated['partnership_start_date'] ?? now()->toDateString(),
            'delivery_interval_days' => $validated['delivery_interval_days'] ?? 14,
            'partnership_status' => $validated['partnership_status'] ?? 'aktif',
            'is_aktif' => ($validated['partnership_status'] ?? 'aktif') === 'aktif',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        if ($stokAwal > 0) {
            StokPenjualan::create([
                'warung_id' => $warung->id,
                'tipe_transaksi' => 'titip_awal',
                'tanggal_titip' => now()->toDateString(),
                'jumlah_titip' => $stokAwal,
                'tanggal_cek' => null,
                'stok_sebelumnya' => 0,
                'jumlah_laku' => 0,
                'sisa_stok' => $stokAwal,
                'total_pendapatan' => 0,
                'catatan' => 'Penitipan stok awal pembukaan kemitraan konsinyasi.',
            ]);
        }

        return redirect()->route('admin.warung.show', $warung->id)
            ->with('success', "Warung Mitra '{$warung->nama_warung}' berhasil ditambahkan!");
    }

    public function show($id)
    {
        $warung = Warung::with(['stokPenjualans', 'pengajuanStoks', 'salesRecords', 'restocks', 'stockConfirmations', 'crmAlerts'])->findOrFail($id);

        $riwayatStok = $warung->stokPenjualans()->paginate(10);
        $rekomendasi = $warung->rekomendasi_stok;

        return view('admin.warung.show', compact('warung', 'riwayatStok', 'rekomendasi'));
    }

    public function edit($id)
    {
        $warung = Warung::findOrFail($id);
        return view('admin.warung.edit', compact('warung'));
    }

    public function update(Request $request, $id)
    {
        $warung = Warung::findOrFail($id);

        $validated = $request->validate([
            'nama_warung' => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'required|string|max:50',
            'kode_unik' => 'required|string|max:50|unique:warungs,kode_unik,' . $warung->id,
            'pin' => 'required|string|min:4|max:10',
            'harga_titip' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'partnership_start_date' => 'nullable|date',
            'delivery_interval_days' => 'nullable|integer|min:1|max:60',
            'partnership_status' => 'required|in:aktif,nonaktif,ditinjau',
            'catatan' => 'nullable|string',
        ]);

        $isAktif = ($validated['partnership_status'] === 'aktif');

        $warung->update([
            'name' => $validated['nama_warung'],
            'nama_warung' => $validated['nama_warung'],
            'owner_name' => $validated['nama_pemilik'],
            'nama_pemilik' => $validated['nama_pemilik'],
            'alamat' => $validated['alamat'],
            'phone' => $validated['kontak'],
            'kontak' => $validated['kontak'],
            'kode_unik' => strtoupper(Str::slug($validated['kode_unik'])),
            'pin' => $validated['pin'],
            'harga_titip' => $validated['harga_titip'],
            'harga_jual' => $validated['harga_jual'],
            'partnership_start_date' => $validated['partnership_start_date'],
            'delivery_interval_days' => $validated['delivery_interval_days'] ?? 14,
            'partnership_status' => $validated['partnership_status'],
            'is_aktif' => $isAktif,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('admin.warung.show', $warung->id)
            ->with('success', "Data Warung '{$warung->nama_warung}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $warung = Warung::findOrFail($id);
        $nama = $warung->nama_warung;

        // Gunakan soft delete agar riwayat transaksi tetap terjaga
        $warung->delete();

        return redirect()->route('admin.warung.index')
            ->with('success', "Warung '{$nama}' telah dinonaktifkan (arsip histori transaksi tetap tersimpan).");
    }
}
