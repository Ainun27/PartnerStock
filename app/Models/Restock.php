<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Restock extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warung_id',
        'product_id',
        'distribution_date',
        'quantity_delivered',
        'old_stock_withdrawn',
        'approved_quantity',
        'notes',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'quantity_delivered' => 'integer',
        'old_stock_withdrawn' => 'integer',
        'approved_quantity' => 'integer',
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
