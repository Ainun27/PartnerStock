<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warung extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warungs';

    protected $fillable = [
        'supplier_id',
        'user_id',
        'name',
        'owner_name',
        'phone',
        'partnership_start_date',
        'delivery_interval_days',
        'partnership_status',
        'nama_warung',
        'nama_pemilik',
        'alamat',
        'kontak',
        'kode_unik',
        'pin',
        'stok_saat_ini',
        'harga_titip',
        'harga_jual',
        'is_aktif',
        'catatan',
    ];

    protected $casts = [
        'stok_saat_ini' => 'integer',
        'harga_titip' => 'integer',
        'harga_jual' => 'integer',
        'is_aktif' => 'boolean',
        'partnership_start_date' => 'date',
        'delivery_interval_days' => 'integer',
    ];

    // Synonyms and accessors for smooth compatibility with both specs
    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ?? $this->attributes['nama_warung'] ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        if (!isset($this->attributes['nama_warung'])) {
            $this->attributes['nama_warung'] = $value;
        }
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->attributes['owner_name'] ?? $this->attributes['nama_pemilik'] ?? '';
    }

    public function setOwnerNameAttribute($value): void
    {
        $this->attributes['owner_name'] = $value;
        if (!isset($this->attributes['nama_pemilik'])) {
            $this->attributes['nama_pemilik'] = $value;
        }
    }

    public function getPhoneAttribute(): string
    {
        return $this->attributes['phone'] ?? $this->attributes['kontak'] ?? '';
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value;
        if (!isset($this->attributes['kontak'])) {
            $this->attributes['kontak'] = $value;
        }
    }

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stockConfirmations(): HasMany
    {
        return $this->hasMany(StockConfirmation::class)->orderBy('confirmation_date', 'desc');
    }

    public function salesRecords(): HasMany
    {
        return $this->hasMany(SalesRecord::class)->orderBy('period_end', 'desc');
    }

    public function restocks(): HasMany
    {
        return $this->hasMany(Restock::class)->orderBy('distribution_date', 'desc');
    }

    public function stockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class)->orderBy('created_at', 'desc');
    }

    public function stockRecommendations(): HasMany
    {
        return $this->hasMany(StockRecommendation::class)->orderBy('calculation_date', 'desc');
    }

    public function crmAlerts(): HasMany
    {
        return $this->hasMany(CrmAlert::class)->orderBy('created_at', 'desc');
    }

    public function crmFollowUps(): HasMany
    {
        return $this->hasMany(CrmFollowUp::class)->orderBy('follow_up_date', 'desc');
    }

    public function stokPenjualans(): HasMany
    {
        return $this->hasMany(StokPenjualan::class, 'warung_id')->orderBy('created_at', 'desc');
    }

    public function pengajuanStoks(): HasMany
    {
        return $this->hasMany(PengajuanStok::class, 'warung_id')->orderBy('created_at', 'desc');
    }

    /**
     * Konfirmasi stok terbaru yang dilaporkan melalui WA
     */
    public function getLatestStockConfirmationAttribute(): ?StockConfirmation
    {
        return $this->stockConfirmations()->orderBy('confirmation_date', 'desc')->first();
    }

    /**
     * Status apakah warung ini perlu konfirmasi stok pra-kunjungan
     */
    public function getNeedsStockConfirmationAttribute(): bool
    {
        $latest = $this->latest_stock_confirmation;
        if (!$latest) {
            return true;
        }

        // Jika konfirmasi terakhir lebih dari 10 hari yang lalu
        return $latest->confirmation_date->diffInDays(now()) >= 10;
    }

    /**
     * Hitung rata-rata produk terjual per kunjungan
     */
    public function getRataRataLakuAttribute(): float
    {
        // Utamakan sales_records jika ada
        $salesAvg = $this->salesRecords()->avg('sold_quantity');
        if ($salesAvg !== null && $this->salesRecords()->count() > 0) {
            return round((float) $salesAvg, 1);
        }

        $riwayatLaku = $this->stokPenjualans()
            ->whereNotNull('tanggal_cek')
            ->where('jumlah_laku', '>', 0)
            ->pluck('jumlah_laku');

        if ($riwayatLaku->isEmpty()) {
            return 0.0;
        }

        return round($riwayatLaku->avg(), 1);
    }

    /**
     * Total produk terjual sepanjang masa di warung ini
     */
    public function getTotalTerjualAttribute(): int
    {
        $salesSum = (int) $this->salesRecords()->sum('sold_quantity');
        if ($salesSum > 0) {
            return $salesSum;
        }
        return (int) $this->stokPenjualans()->sum('jumlah_laku');
    }

    public function getSesiTerakhirAttribute(): ?StokPenjualan
    {
        return $this->stokPenjualans()->orderBy('created_at', 'desc')->first();
    }

    public function getStokDitaroSesiLaluAttribute(): int
    {
        $sesi = $this->sesi_terakhir;
        if (!$sesi) {
            return $this->stok_saat_ini > 0 ? (int) $this->stok_saat_ini : 0;
        }

        if ($sesi->jumlah_titip > 0 && $sesi->sisa_stok > 0) {
            return (int) $sesi->sisa_stok;
        }

        return (int) ($sesi->stok_sebelumnya ?: ($this->stok_saat_ini ?: 0));
    }

    /**
     * Rekomendasi stok pra-kunjungan
     */
    public function getRekomendasiStokAttribute(): array
    {
        $sesi = $this->sesi_terakhir;
        $stokSekarang = (int) $this->stok_saat_ini;
        $rataLaku = $this->rata_rata_laku;

        $taro = $this->stok_ditaro_sesi_lalu;
        $sisa = $stokSekarang;

        $keputusan = StokPenjualan::hitungKeputusan($taro, $sisa);

        $urgensi = match ($keputusan['status']) {
            'habis', 'laris' => 'kritis',
            'stabil' => 'aman',
            'lambat' => 'sedang',
            default => $stokSekarang <= 3 ? 'kritis' : 'aman',
        };

        $riwayatSesi = $this->stokPenjualans()
            ->whereNotNull('tanggal_cek')
            ->orderBy('tanggal_cek', 'desc')
            ->take(3)
            ->get();

        $riwayatLakuList = $riwayatSesi->map(function ($s) {
            $taro = (int) $s->stok_sebelumnya;
            $laku = (int) $s->jumlah_laku;
            $sisaFisik = (int) $s->sisa_fisik;
            return [
                'tanggal' => $s->tanggal_cek ? $s->tanggal_cek->format('d/m') : '-',
                'stok_ditaro' => $taro,
                'sisa_fisik' => $sisaFisik,
                'jumlah_laku' => $laku,
                'persen' => $taro > 0 ? (int) round(($laku / $taro) * 100) : 0,
            ];
        })->reverse()->values()->all();

        $poPending = (int) $this->pengajuanStoks()
            ->whereIn('status', ['pending', 'diproses'])
            ->sum('jumlah_diminta');

        $terakhirSesi = $riwayatSesi->first();
        $terakhirLaku = $terakhirSesi ? (int) $terakhirSesi->jumlah_laku : 0;

        if ($terakhirLaku > 0 || $rataLaku > 0) {
            $baseKebutuhan = max($terakhirLaku, (int) round($rataLaku));
            $tambahanPO = ($poPending > 0) ? min(4, (int) round($poPending * 0.15)) : 0;
            $estimasiBawa = $baseKebutuhan + $tambahanPO;
            $targetIdeal = $estimasiBawa;
        } else {
            $targetIdeal = 25;
            $estimasiBawa = 25;
        }

        $alasanPraProduksi = ($rataLaku > 0)
            ? "Penjualan sesi lalu: {$terakhirLaku} pcs (rata-rata {$rataLaku} pcs). Disarankan bawa {$estimasiBawa} bungkus."
            : "Warung baru. Kuota awal disarankan {$estimasiBawa} bungkus.";

        return [
            'rata_rata_laku' => $rataLaku,
            'target_ideal' => $taro,
            'rekomendasi_titip' => $keputusan['rekomendasi_titip'],
            'estimasi_bawa' => $estimasiBawa,
            'status_urgensi' => $urgensi,
            'keterangan' => $keputusan['alasan'],
            'ringkasan' => $keputusan['ringkasan'],
            'label' => $keputusan['label'],
            'badge_color' => $keputusan['badge_color'],
            'taro_terakhir' => $taro,
            'sisa_terakhir' => $sisa,
            'laku_terakhir' => $sesi ? (int) $sesi->jumlah_laku : 0,
            'riwayat_laku_list' => $riwayatLakuList,
            'po_pending' => $poPending,
            'alasan_pra_produksi' => $alasanPraProduksi,
        ];
    }
}
