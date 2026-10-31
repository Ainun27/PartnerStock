<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Tampilkan Pengaturan Parameter Sistem
     */
    public function index()
    {
        $wmaWeights = SystemSetting::get('wma_weights', [0.20, 0.30, 0.50]);
        $consignmentMode = SystemSetting::get('consignment_mode', 'A');
        $deliveryInterval = SystemSetting::get('delivery_interval_days', 14);
        $initialQuota = SystemSetting::get('initial_stock_quota', 20);

        return view('admin.pengaturan.index', compact(
            'wmaWeights',
            'consignmentMode',
            'deliveryInterval',
            'initialQuota'
        ));
    }

    /**
     * Simpan pembaruan parameter sistem
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'w1' => 'required|numeric|min:0.01|max:1',
            'w2' => 'required|numeric|min:0.01|max:1',
            'w3' => 'required|numeric|min:0.01|max:1',
            'consignment_mode' => 'required|in:A,B',
            'delivery_interval_days' => 'required|integer|min:1|max:60',
            'initial_stock_quota' => 'required|integer|min:5|max:100',
        ]);

        $sumWeights = round($validated['w1'] + $validated['w2'] + $validated['w3'], 2);
        if ($sumWeights != 1.0) {
            return back()->with('error', "Total bobot WMA harus berjumlah 100% (1.0). Saat ini: {$sumWeights}")->withInput();
        }

        SystemSetting::set('wma_weights', [
            (float) $validated['w1'],
            (float) $validated['w2'],
            (float) $validated['w3'],
        ], 'Bobot peramalan WMA');

        SystemSetting::set('consignment_mode', $validated['consignment_mode'], 'Mode Konsinyasi: A (tarik & ganti), B (stok lama tetap dijual)');
        SystemSetting::set('delivery_interval_days', (int) $validated['delivery_interval_days'], 'Interval pengiriman rutin (hari)');
        SystemSetting::set('initial_stock_quota', (int) $validated['initial_stock_quota'], 'Kuota stok awal warung baru');

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan sistem konsinyasi & WMA berhasil disimpan.');
    }
}
