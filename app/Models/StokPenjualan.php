<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokPenjualan extends Model
{
    use HasFactory;

    protected $table = 'stok_penjualans';

    protected $fillable = [
        'warung_id',
        'product_id',
        'tipe_transaksi',
        'tanggal_titip',
        'jumlah_titip',
        'old_stock_withdrawn',
        'tanggal_cek',
        'stok_sebelumnya',
        'sisa_fisik',
        'jumlah_laku',
        'sisa_stok',
        'total_pendapatan',
        'catatan',
        'keputusan_stok',
    ];

    protected $casts = [
        'tanggal_titip' => 'date',
        'tanggal_cek' => 'date',
        'jumlah_titip' => 'integer',
        'old_stock_withdrawn' => 'integer',
        'stok_sebelumnya' => 'integer',
        'sisa_fisik' => 'integer',
        'jumlah_laku' => 'integer',
        'sisa_stok' => 'integer',
        'total_pendapatan' => 'integer',
    ];

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class, 'warung_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Hitung sisa fisik: gunakan kolom sisa_fisik jika ada, atau hitung selisih
     */
    public function getSisaFisikAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }
        return max(0, (int) $this->stok_sebelumnya - (int) $this->jumlah_laku);
    }

    // Accessor kompatibilitas laporan konsinyasi (terintegrasi ke 1 tabel stok_penjualans)
    public function getPeriodEndAttribute()
    {
        return $this->tanggal_cek;
    }

    public function getPeriodStartAttribute()
    {
        return $this->tanggal_titip ?: $this->tanggal_cek;
    }

    public function getOpeningStockAttribute(): int
    {
        return (int) $this->stok_sebelumnya;
    }

    public function getRemainingStockAttribute(): int
    {
        return (int) $this->sisa_fisik;
    }

    public function getSoldQuantityAttribute(): int
    {
        return (int) $this->jumlah_laku;
    }

    public function getDistributionDateAttribute()
    {
        return $this->tanggal_titip ?: $this->tanggal_cek;
    }

    public function getQuantityDeliveredAttribute(): int
    {
        return (int) $this->jumlah_titip;
    }

    /**
     * Algoritma Keputusan Stok Konsinyasi: Periodic Review Inventory Model (Siklus 14 Hari / 2 Minggu)
     * Pak Aceng berkunjung rutin 2 minggu sekali (Review Period T = 14 hari).
     *
     * Parameter:
     * - Taro ($S_prev): Stok yang dititipkan pada kunjungan 14 hari lalu.
     * - Sisa ($S_curr): Sisa fisik yang dihitung hari ini.
     * - Terjual ($D): Total konsumsi keripik selama 14 hari (D = Taro - Sisa).
     * - Laju Penjualan Harian ($d): d = D / 14 pcs/hari.
     * - Safety Stock ($SS): Cadangan lonjakan permintaan agar tidak kehabisan sebelum 14 hari berikutnya.
     * - Rekomendasi Kuota 2 Minggu ($S_target): Total stok yang harus ditaro untuk 14 hari ke depan.
     */
    public static function hitungKeputusan(int $taro, int $sisa, int $siklusHari = 14): array
    {
        $taro = max(0, $taro);
        $sisa = max(0, $sisa);
        $laku = max(0, $taro - $sisa);
        $persen = $taro > 0 ? (int) round(($laku / $taro) * 100) : 0;

        if ($taro <= 0) {
            return [
                'status' => 'awal',
                'label' => 'Sesi Perdana',
                'badge_color' => 'bg-slate-200 text-slate-900 border border-slate-400 font-bold',
                'rekomendasi_titip' => 30,
                'safety_stock' => 5,
                'siklus_hari' => $siklusHari,
                'metode' => 'Periodic Review (Siklus 14 Hari)',
                'ringkasan' => 'Titip Awal: 30 pcs',
                'alasan' => 'Belum ada data riwayat siklus 14 hari sebelumnya. Disarankan mulai kuota standar 30 pcs untuk periode 2 minggu pertama.',
            ];
        }

        // Kasus 1: Habis Total (Stockout sebelum siklus 14 hari berakhir)
        if ($sisa === 0) {
            $safetyStock = 10;
            $saran = max(35, $laku + $safetyStock);
            return [
                'status' => 'habis',
                'label' => 'Habis Total (Stockout)',
                'badge_color' => 'bg-emerald-100 text-emerald-950 border border-emerald-500 font-bold',
                'rekomendasi_titip' => $saran,
                'safety_stock' => $safetyStock,
                'siklus_hari' => $siklusHari,
                'metode' => 'Periodic Review (Siklus 14 Hari)',
                'ringkasan' => "Naikkan Titip: {$saran} pcs",
                'alasan' => "Stok habis total tanpa sisa ({$laku} pcs terjual). Permintaan sangat tinggi, naikkan kuota jadi {$saran} pcs agar tidak kehabisan sebelum kunjungan Pak Aceng berikutnya.",
            ];
        }

        // Kasus 2: Laris Manis (Terjual >= 80% atau sisa <= 5 pcs dalam 14 hari)
        if ($persen >= 80 || $sisa <= 5) {
            $safetyStock = 8;
            $saran = max(30, $laku + $safetyStock);
            return [
                'status' => 'laris',
                'label' => 'Laris Manis',
                'badge_color' => 'bg-emerald-100 text-emerald-950 border border-emerald-500 font-bold',
                'rekomendasi_titip' => $saran,
                'safety_stock' => $safetyStock,
                'siklus_hari' => $siklusHari,
                'metode' => 'Periodic Review (Siklus 14 Hari)',
                'ringkasan' => "Naikkan Titip: {$saran} pcs",
                'alasan' => "Terjual {$laku} dari {$taro} pcs dalam 2 minggu ({$persen}% terjual, sisa {$sisa} pcs). Disarankan menaikkan kuota 2 minggu ke {$saran} pcs untuk memenuhi permintaan.",
            ];
        }

        // Kasus 3: Stabil & Cukup (50% <= Terjual < 80%)
        // Jika kejual 20 dari 40 pcs, sarankan kuota pas 25 pcs (bukan 40 pcs)
        if ($persen >= 50) {
            $safetyStock = 5;
            $saran = max(20, $laku + $safetyStock);
            return [
                'status' => 'stabil',
                'label' => 'Stabil & Cukup',
                'badge_color' => 'bg-blue-100 text-blue-950 border border-blue-500 font-bold',
                'rekomendasi_titip' => $saran,
                'safety_stock' => $safetyStock,
                'siklus_hari' => $siklusHari,
                'metode' => 'Periodic Review (Siklus 14 Hari)',
                'ringkasan' => "Pertahankan: {$saran} pcs",
                'alasan' => "Terjual {$laku} dari {$taro} pcs dalam 2 minggu (sisa {$sisa} pcs). Disarankan menyesuaikan kuota ke {$saran} pcs agar pas dengan konsumsi warung dan keripik tetap renyah.",
            ];
        }

        // Kasus 4: Perputaran Lambat (25% <= Terjual < 50%)
        if ($persen >= 25) {
            $safetyStock = 3;
            $saran = max(15, $laku + $safetyStock);
            return [
                'status' => 'lambat',
                'label' => 'Perputaran Lambat',
                'badge_color' => 'bg-amber-100 text-amber-950 border border-amber-500 font-bold',
                'rekomendasi_titip' => $saran,
                'safety_stock' => $safetyStock,
                'siklus_hari' => $siklusHari,
                'metode' => 'Periodic Review (Siklus 14 Hari)',
                'ringkasan' => "Kurangi Titip: {$saran} pcs",
                'alasan' => "Sisa masih {$sisa} pcs (baru terjual {$laku} pcs dalam 2 minggu). Kurangi kuota jadi {$saran} pcs agar produk tidak terlalu lama di toples warung.",
            ];
        }

        // Kasus 5: Stok Menumpuk (< 25%)
        return [
            'status' => 'macet',
            'label' => 'Stok Menumpuk',
            'badge_color' => 'bg-rose-100 text-rose-950 border border-rose-500 font-bold',
            'rekomendasi_titip' => 0,
            'safety_stock' => 0,
            'siklus_hari' => $siklusHari,
            'metode' => 'Periodic Review (Siklus 14 Hari)',
            'ringkasan' => "Tahan / Evaluasi: 0 pcs",
            'alasan' => "Hanya terjual {$laku} dari {$taro} pcs dalam 2 minggu. Tahan penambahan stok baru dan periksa penempatan toples di warung.",
        ];
    }

    /**
     * Keputusan stok untuk baris transaksi sesi ini
     */
    public function getKeputusanAttribute(): array
    {
        $taro = (int) $this->stok_sebelumnya;
        $sisa = $this->sisa_fisik;
        return self::hitungKeputusan($taro, $sisa);
    }
}
