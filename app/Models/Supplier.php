<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'phone',
        'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warungs(): HasMany
    {
        return $this->hasMany(Warung::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockConfirmations(): HasMany
    {
        return $this->hasMany(StockConfirmation::class);
    }

    public function salesRecords(): HasMany
    {
        return $this->hasMany(SalesRecord::class);
    }

    public function restocks(): HasMany
    {
        return $this->hasMany(Restock::class);
    }

    public function stockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class);
    }

    public function stockRecommendations(): HasMany
    {
        return $this->hasMany(StockRecommendation::class);
    }

    public function crmAlerts(): HasMany
    {
        return $this->hasMany(CrmAlert::class);
    }

    public function crmFollowUps(): HasMany
    {
        return $this->hasMany(CrmFollowUp::class);
    }

    public function systemSettings(): HasMany
    {
        return $this->hasMany(SystemSetting::class);
    }
}
