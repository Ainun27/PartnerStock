<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warung_id',
        'product_id',
        'period_start',
        'period_end',
        'distributed_quantity',
        'opening_stock',
        'remaining_stock',
        'sold_quantity',
        'verified_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'distributed_quantity' => 'integer',
        'opening_stock' => 'integer',
        'remaining_stock' => 'integer',
        'sold_quantity' => 'integer',
        'verified_at' => 'datetime',
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
}
