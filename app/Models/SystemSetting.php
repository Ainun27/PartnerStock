<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class SystemSetting
{
    /**
     * Dapatkan nilai pengaturan sistem dari Cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::get('sys_setting_' . $key, $default);
    }

    /**
     * Simpan nilai pengaturan sistem ke Cache
     */
    public static function set(string $key, mixed $value, ?string $description = null, ?int $supplierId = null): mixed
    {
        Cache::forever('sys_setting_' . $key, $value);
        return $value;
    }
}
