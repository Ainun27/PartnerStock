<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanStok extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_stoks';

    protected $fillable = [
        'warung_id',
        'tanggal_pengajuan',
        'jumlah_diminta',
        'status',
        'catatan',
        'catatan_pemasok',
        'tanggal_diproses',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'jumlah_diminta' => 'integer',
        'tanggal_diproses' => 'datetime',
    ];

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class, 'warung_id');
    }

    public function getEstimasiTibaAttribute()
    {
        return $this->tanggal_pengajuan ? $this->tanggal_pengajuan->copy()->addDays(2) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'diproses' => 'bg-blue-100 text-blue-800 border-blue-300',
            'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'ditolak' => 'bg-red-100 text-red-800 border-red-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }
}
