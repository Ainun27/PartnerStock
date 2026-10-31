<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'setting_key',
        'setting_value',
        'description',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $decoded = json_decode($setting->setting_value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $setting->setting_value;
    }

    public static function set(string $key, mixed $value, ?string $description = null, ?int $supplierId = null): self
    {
        $valString = is_array($value) ? json_encode($value) : (string) $value;

        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'supplier_id' => $supplierId,
                'setting_value' => $valString,
                'description' => $description,
            ]
        );
    }
}
