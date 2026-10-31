<?php

namespace App\Models;

class Restock
{
    public static function create(array $attributes = []): ?StokPenjualan
    {
        $distributionDate = $attributes['distribution_date'] ?? now()->toDateString();
        $existing = StokPenjualan::where('warung_id', $attributes['warung_id'])
            ->whereDate('tanggal_cek', $distributionDate)
            ->first();

        if ($existing) {
            $existing->update([
                'jumlah_titip' => $attributes['quantity_delivered'] ?? $existing->jumlah_titip,
                'old_stock_withdrawn' => $attributes['old_stock_withdrawn'] ?? $existing->old_stock_withdrawn,
            ]);
            return $existing;
        }

        return StokPenjualan::create([
            'warung_id' => $attributes['warung_id'],
            'product_id' => $attributes['product_id'] ?? null,
            'tipe_transaksi' => 'titip_awal',
            'tanggal_titip' => $distributionDate,
            'jumlah_titip' => $attributes['quantity_delivered'] ?? 0,
            'old_stock_withdrawn' => $attributes['old_stock_withdrawn'] ?? 0,
            'stok_sebelumnya' => 0,
            'sisa_fisik' => 0,
            'jumlah_laku' => 0,
            'sisa_stok' => $attributes['quantity_delivered'] ?? 0,
            'total_pendapatan' => 0,
            'catatan' => $attributes['notes'] ?? null,
        ]);
    }

    public static function __callStatic($method, $parameters)
    {
        return StokPenjualan::$method(...$parameters);
    }
}
