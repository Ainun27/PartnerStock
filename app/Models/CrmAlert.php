<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warung_id',
        'category',
        'trigger_type',
        'trigger_period',
        'description',
        'evidence_data',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_data' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(CrmFollowUp::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'data_mining' => 'Data Mining',
            'churn_prevention' => 'Churn Prevention',
            'reorder_recommendation' => 'Reorder Recommendation',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'perlu_ditinjau' => ['label' => 'Perlu Ditinjau', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'perlu_dihubungi' => ['label' => 'Perlu Dihubungi', 'class' => 'bg-red-100 text-red-800 border-red-200'],
            'sudah_dihubungi' => ['label' => 'Sudah Dihubungi', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'dalam_tindak_lanjut' => ['label' => 'Dalam Tindak Lanjut', 'class' => 'bg-purple-100 text-purple-800 border-purple-200'],
            'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-slate-100 text-slate-800 border-slate-200'],
        };
    }
}
