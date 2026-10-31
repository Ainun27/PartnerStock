<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warung_id',
        'product_id',
        'forecast_quantity',
        'recommended_quantity',
        'approved_quantity',
        'consignment_mode',
        'source_periods',
        'weights_used',
        'stock_confirmation_id',
        'calculation_date',
        'data_status',
        'adjustment_reason',
    ];

    protected $casts = [
        'forecast_quantity' => 'decimal:2',
        'recommended_quantity' => 'integer',
        'approved_quantity' => 'integer',
        'source_periods' => 'array',
        'weights_used' => 'array',
        'calculation_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockConfirmation(): BelongsTo
    {
        return $this->belongsTo(StockConfirmation::class);
    }
}
