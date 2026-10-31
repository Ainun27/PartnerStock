<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'name',
        'unit',
        'agreed_price',
        'cost_price',
        'is_active',
    ];

    protected $casts = [
        'agreed_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function stokPenjualans(): HasMany
    {
        return $this->hasMany(StokPenjualan::class, 'product_id');
    }
}
