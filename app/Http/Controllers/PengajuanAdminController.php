<?php

namespace App\Http\Controllers;

use App\Models\PengajuanStok;
use Illuminate\Http\Request;

class PengajuanAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = PengajuanStok::with('warung')->orderBy('created_at', 'desc');

        if ($status && in_array($status, ['pending', 'diproses', 'selesai', 'ditolak'])) {
            $query->where('status', $status);
        }

        $pengajuans = $query->paginate(12)->withQueryString();

        $counts = [
            'semua' => PengajuanStok::count(),
            'pending' => PengajuanStok::where('status', 'pending')->count(),
            'diproses' => PengajuanStok::where('status', 'diproses')->count(),
            'selesai' => PengajuanStok::where('status', 'selesai')->count(),
            'ditolak' => PengajuanStok::where('status', 'ditolak')->count(),
        ];

        return view('admin.pengajuan.index', compact('pengajuans', 'status', 'counts'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pengajuan = PengajuanStok::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,diproses,selesai,ditolak',
            'catatan_pemasok' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'catatan_pemasok' => $validated['catatan_pemasok'] ?? $pengajuan->catatan_pemasok,
        ];

        if ($validated['status'] === 'diproses' && !$pengajuan->tanggal_diproses) {
            $updateData['tanggal_diproses'] = now();
        }

        $pengajuan->update($updateData);

        return back()->with('success', "Status pengajuan dari '{$pengajuan->warung->nama_warung}' berhasil diubah menjadi: " . $pengajuan->status_label);
    }
}
