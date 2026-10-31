<?php

namespace App\Models;

class SalesRecord
{
    public static function create(array $attributes = []): StokPenjualan
    {
        $mapped = [
            'warung_id' => $attributes['warung_id'],
            'product_id' => $attributes['product_id'] ?? null,
            'tipe_transaksi' => 'sesi',
            'tanggal_titip' => $attributes['period_start'] ?? null,
            'tanggal_cek' => $attributes['period_end'] ?? now()->toDateString(),
            'stok_sebelumnya' => $attributes['opening_stock'] ?? ($attributes['distributed_quantity'] ?? 0),
            'sisa_fisik' => $attributes['remaining_stock'] ?? 0,
            'jumlah_laku' => $attributes['sold_quantity'] ?? 0,
            'sisa_stok' => $attributes['remaining_stock'] ?? 0,
            'total_pendapatan' => ($attributes['sold_quantity'] ?? 0) * 12000,
            'keputusan_stok' => 'Rekam Penjualan Konsinyasi',
        ];

        return StokPenjualan::create($mapped);
    }

    public static function __callStatic($method, $parameters)
    {
        return StokPenjualan::$method(...$parameters);
    }
}
