<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class StockRecommendation
{
    public array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public static function updateOrCreate(array $attributes, array $values = []): self
    {
        $warungId = $attributes['warung_id'] ?? null;
        $date = $attributes['calculation_date'] ?? now()->toDateString();
        $cacheKey = "stock_rec_{$warungId}_{$date}";

        $merged = array_merge($attributes, $values);
        Cache::put($cacheKey, $merged, now()->addDays(30));

        return new self($merged);
    }

    public static function where(string $column, mixed $operator = null, mixed $value = null)
    {
        return new class($column, $operator, $value) {
            protected array $conditions = [];

            public function __construct($col, $op, $val)
            {
                if ($val === null) {
                    $this->conditions[$col] = $op;
                } else {
                    $this->conditions[$col] = $val;
                }
            }

            public function whereDate(string $col, string $val): self
            {
                $this->conditions[$col] = $val;
                return $this;
            }

            public function where(string $col, mixed $val): self
            {
                $this->conditions[$col] = $val;
                return $this;
            }

            public function first(): ?StockRecommendation
            {
                $warungId = $this->conditions['warung_id'] ?? null;
                $date = $this->conditions['calculation_date'] ?? now()->toDateString();
                $cacheKey = "stock_rec_{$warungId}_{$date}";

                $data = Cache::get($cacheKey);
                return $data ? new StockRecommendation($data) : null;
            }
        };
    }
}
