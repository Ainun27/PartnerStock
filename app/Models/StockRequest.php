<?php

namespace App\Models;

class StockRequest
{
    public static function create(array $attributes): PengajuanStok
    {
        return PengajuanStok::create([
            'warung_id' => $attributes['warung_id'],
            'tanggal_pengajuan' => $attributes['requested_at'] ?? now()->toDateString(),
            'jumlah_diminta' => $attributes['requested_quantity'] ?? 0,
            'status' => 'pending',
            'catatan' => $attributes['reason'] ?? null,
        ]);
    }

    public static function firstOrCreate(array $attributes, array $values = []): PengajuanStok
    {
        return PengajuanStok::firstOrCreate(
            ['warung_id' => $attributes['warung_id']],
            [
                'tanggal_pengajuan' => $values['requested_at'] ?? now()->toDateString(),
                'jumlah_diminta' => $attributes['requested_quantity'] ?? 10,
                'status' => 'pending',
                'catatan' => $values['reason'] ?? null,
            ]
        );
    }

    public static function updateOrCreate(array $attributes, array $values = []): PengajuanStok
    {
        return PengajuanStok::updateOrCreate(
            ['warung_id' => $attributes['warung_id']],
            [
                'tanggal_pengajuan' => $values['requested_at'] ?? now()->toDateString(),
                'jumlah_diminta' => $values['requested_quantity'] ?? 10,
                'status' => 'pending',
                'catatan' => $values['reason'] ?? null,
            ]
        );
    }

    public static function where(string $col, mixed $op = null, mixed $val = null)
    {
        if ($col === 'status') {
            $statusVal = ($val === null) ? $op : $val;
            if ($statusVal === 'menunggu') {
                $statusVal = 'pending';
            }
            return PengajuanStok::where('status', $statusVal);
        }
        return PengajuanStok::where($col, $op, $val);
    }

    public static function with(mixed $relations)
    {
        return PengajuanStok::with($relations);
    }
}
