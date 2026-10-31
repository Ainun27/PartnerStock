<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'crm_alert_id',
        'supplier_id',
        'warung_id',
        'follow_up_date',
        'contact_method',
        'result_notes',
        'next_action',
        'status',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(CrmAlert::class, 'crm_alert_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class);
    }
}
