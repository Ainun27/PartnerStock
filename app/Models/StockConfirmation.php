<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warung_id',
        'confirmation_date',
        'reported_remaining_stock',
        'physically_checked_stock',
        'data_status',
        'notes',
    ];

    protected $casts = [
        'confirmation_date' => 'date',
        'reported_remaining_stock' => 'integer',
        'physically_checked_stock' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class);
    }
}
