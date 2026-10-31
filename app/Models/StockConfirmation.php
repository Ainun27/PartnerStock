<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class StockConfirmation
{
    public int $id = 0;
    public ?int $supplier_id = null;
    public int $warung_id = 0;
    public Carbon $confirmation_date;
    public int $reported_remaining_stock = 0;
    public ?int $physically_checked_stock = null;
    public string $data_status = 'menunggu_pemeriksaan';
    public ?string $notes = null;
    public Carbon $created_at;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $val) {
            if ($key === 'confirmation_date') {
                $this->confirmation_date = $val instanceof Carbon ? $val : Carbon::parse($val);
            } elseif ($key === 'created_at') {
                $this->created_at = $val instanceof Carbon ? $val : Carbon::parse($val);
            } else {
                $this->{$key} = $val;
            }
        }

        if (!isset($this->confirmation_date)) {
            $this->confirmation_date = Carbon::now();
        }
        if (!isset($this->created_at)) {
            $this->created_at = Carbon::now();
        }
    }

    public function __get($key)
    {
        if ($key === 'warung') {
            return Warung::find($this->warung_id);
        }
        return null;
    }

    public function warung(): ?Warung
    {
        return Warung::find($this->warung_id);
    }

    public static function create(array $attributes): self
    {
        // 1. Sinkronisasi sisa stok langsung ke master data Warung
        $warungId = $attributes['warung_id'] ?? null;
        if ($warungId && isset($attributes['reported_remaining_stock'])) {
            $warung = Warung::find($warungId);
            if ($warung) {
                $warung->stok_saat_ini = (int) $attributes['reported_remaining_stock'];
                $warung->save();
            }
        }

        $records = Cache::get('stock_confirmations_store', []);
        $nextId = count($records) > 0 ? (max(array_column($records, 'id')) + 1) : 1;

        $attributes['id'] = $nextId;
        $instance = new self($attributes);

        $records[] = [
            'id' => $instance->id,
            'supplier_id' => $instance->supplier_id,
            'warung_id' => $instance->warung_id,
            'confirmation_date' => $instance->confirmation_date->toDateString(),
            'reported_remaining_stock' => $instance->reported_remaining_stock,
            'physically_checked_stock' => $instance->physically_checked_stock,
            'data_status' => $instance->data_status,
            'notes' => $instance->notes,
            'created_at' => $instance->created_at->toDateTimeString(),
        ];

        Cache::forever('stock_confirmations_store', $records);
        return $instance;
    }

    public static function updateOrCreate(array $attributes, array $values = []): self
    {
        return static::create(array_merge($attributes, $values));
    }

    public static function all(): Collection
    {
        $records = Cache::get('stock_confirmations_store', []);
        return collect($records)->map(fn($r) => new self($r));
    }

    public static function latestForWarung(int $warungId): ?self
    {
        return static::all()->filter(fn($c) => $c->warung_id === $warungId)->sortByDesc('confirmation_date')->first();
    }

    public static function with(mixed $relations)
    {
        return static::query();
    }

    public static function query()
    {
        return new class {
            protected array $orders = [];

            public function orderBy(string $col, string $dir = 'asc'): self
            {
                $this->orders[] = [$col, strtolower($dir)];
                return $this;
            }

            public function get(): Collection
            {
                $items = StockConfirmation::all();
                foreach ($this->orders as [$col, $dir]) {
                    $items = ($dir === 'desc') ? $items->sortByDesc($col) : $items->sortBy($col);
                }
                return $items->values();
            }

            public function paginate(int $perPage = 15): LengthAwarePaginator
            {
                $items = $this->get();
                $page = request()->get('page', 1);
                $slice = $items->forPage($page, $perPage);

                return new LengthAwarePaginator($slice, $items->count(), $perPage, $page, [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]);
            }
        };
    }
}
