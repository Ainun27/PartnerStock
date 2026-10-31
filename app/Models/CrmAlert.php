<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CrmAlert
{
    public int $id = 0;
    public ?int $supplier_id = null;
    public int $warung_id = 0;
    public string $category = 'churn_prevention';
    public string $trigger_type = '';
    public ?string $trigger_period = null;
    public string $description = '';
    public array $evidence_data = [];
    public string $status = 'perlu_ditinjau';
    public ?Carbon $resolved_at = null;
    public Carbon $created_at;
    public Carbon $updated_at;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $val) {
            if ($key === 'created_at') {
                $this->created_at = $val instanceof Carbon ? $val : Carbon::parse($val);
            } elseif ($key === 'updated_at') {
                $this->updated_at = $val instanceof Carbon ? $val : Carbon::parse($val);
            } elseif ($key === 'resolved_at' && $val) {
                $this->resolved_at = $val instanceof Carbon ? $val : Carbon::parse($val);
            } else {
                $this->{$key} = $val;
            }
        }

        if (!isset($this->created_at)) {
            $this->created_at = Carbon::now();
        }
        if (!isset($this->updated_at)) {
            $this->updated_at = Carbon::now();
        }
    }

    public function __get($key)
    {
        if ($key === 'warung') {
            return Warung::find($this->warung_id);
        }
        if ($key === 'followUps') {
            return CrmFollowUp::where('crm_alert_id', $this->id)->get();
        }
        if ($key === 'category_label') {
            return $this->getCategoryLabelAttribute();
        }
        if ($key === 'status_badge') {
            return $this->getStatusBadgeAttribute();
        }
        return null;
    }

    public function warung(): ?Warung
    {
        return Warung::find($this->warung_id);
    }

    public function followUps(): Collection
    {
        return CrmFollowUp::where('crm_alert_id', $this->id)->get();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'data_mining' => 'Data Mining',
            'churn_prevention' => 'Churn Prevention',
            'reorder_recommendation' => 'Reorder Recommendation',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'perlu_ditinjau' => ['label' => 'Perlu Ditinjau', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'perlu_dihubungi' => ['label' => 'Perlu Dihubungi', 'class' => 'bg-red-100 text-red-800 border-red-200'],
            'sudah_dihubungi' => ['label' => 'Sudah Dihubungi', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'dalam_tindak_lanjut' => ['label' => 'Dalam Tindak Lanjut', 'class' => 'bg-purple-100 text-purple-800 border-purple-200'],
            'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-slate-100 text-slate-800 border-slate-200'],
        };
    }

    public function save(): bool
    {
        $records = Cache::get('crm_alerts_store', []);
        $this->updated_at = Carbon::now();

        $index = -1;
        foreach ($records as $i => $rec) {
            if ($rec['id'] === $this->id) {
                $index = $i;
                break;
            }
        }

        $data = [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'warung_id' => $this->warung_id,
            'category' => $this->category,
            'trigger_type' => $this->trigger_type,
            'trigger_period' => $this->trigger_period,
            'description' => $this->description,
            'evidence_data' => $this->evidence_data,
            'status' => $this->status,
            'resolved_at' => $this->resolved_at ? $this->resolved_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];

        if ($index >= 0) {
            $records[$index] = $data;
        } else {
            $records[] = $data;
        }

        Cache::forever('crm_alerts_store', $records);
        return true;
    }

    public function fresh(): self
    {
        return static::findOrFail($this->id);
    }

    // Static Query & Persistence Helpers
    public static function create(array $attributes): self
    {
        $records = Cache::get('crm_alerts_store', []);
        $nextId = count($records) > 0 ? (max(array_column($records, 'id')) + 1) : 1;

        $attributes['id'] = $nextId;
        $attributes['created_at'] = Carbon::now();
        $attributes['updated_at'] = Carbon::now();

        $instance = new self($attributes);
        $records[] = [
            'id' => $instance->id,
            'supplier_id' => $instance->supplier_id,
            'warung_id' => $instance->warung_id,
            'category' => $instance->category,
            'trigger_type' => $instance->trigger_type,
            'trigger_period' => $instance->trigger_period,
            'description' => $instance->description,
            'evidence_data' => $instance->evidence_data,
            'status' => $instance->status,
            'resolved_at' => $instance->resolved_at ? $instance->resolved_at->toDateTimeString() : null,
            'created_at' => $instance->created_at->toDateTimeString(),
            'updated_at' => $instance->updated_at->toDateTimeString(),
        ];

        Cache::forever('crm_alerts_store', $records);
        return $instance;
    }

    public static function updateOrCreate(array $attributes, array $values = []): self
    {
        $query = static::query();
        foreach ($attributes as $col => $val) {
            $query->where($col, $val);
        }

        $existing = $query->first();
        if ($existing) {
            foreach ($values as $col => $val) {
                $existing->{$col} = $val;
            }
            $existing->save();
            return $existing;
        }

        return static::create(array_merge($attributes, $values));
    }

    public static function all(): Collection
    {
        $records = Cache::get('crm_alerts_store', []);
        return collect($records)->map(fn($r) => new self($r));
    }

    public static function findOrFail(int $id): self
    {
        $records = Cache::get('crm_alerts_store', []);
        foreach ($records as $r) {
            if ($r['id'] === $id) {
                return new self($r);
            }
        }
        abort(404, "CrmAlert with ID {$id} not found.");
    }

    public static function first(): ?self
    {
        return static::all()->first();
    }

    public static function count(): int
    {
        return static::all()->count();
    }

    public static function query()
    {
        return new class {
            protected array $filters = [];
            protected array $orders = [];

            public function where(string $col, mixed $op = null, mixed $val = null): self
            {
                if ($val === null) {
                    $this->filters[] = [$col, '=', $op];
                } else {
                    $this->filters[] = [$col, $op, $val];
                }
                return $this;
            }

            public function whereBetween(string $col, array $values): self
            {
                $this->filters[] = [$col, 'between', $values];
                return $this;
            }

            public function with(mixed $relations): self
            {
                return $this;
            }

            public function orderBy(string $col, string $direction = 'asc'): self
            {
                $this->orders[] = [$col, strtolower($direction)];
                return $this;
            }

            public function orderByRaw(string $sql): self
            {
                return $this;
            }

            public function get(): Collection
            {
                $items = CrmAlert::all();

                foreach ($this->filters as [$col, $op, $val]) {
                    $items = $items->filter(function ($item) use ($col, $op, $val) {
                        $actual = $item->{$col} ?? null;
                        if ($actual instanceof Carbon) {
                            $actual = $actual->toDateTimeString();
                        }
                        if ($op === '=') {
                            return $actual == $val;
                        }
                        if ($op === '!=') {
                            return $actual != $val;
                        }
                        if ($op === 'between') {
                            $start = $val[0] instanceof Carbon ? $val[0]->toDateTimeString() : (string) $val[0];
                            $end = $val[1] instanceof Carbon ? $val[1]->toDateTimeString() : (string) $val[1];
                            return $actual >= $start && $actual <= $end;
                        }
                        return true;
                    });
                }

                foreach ($this->orders as [$col, $dir]) {
                    $items = ($dir === 'desc') ? $items->sortByDesc($col) : $items->sortBy($col);
                }

                return $items->values();
            }

            public function first(): ?CrmAlert
            {
                return $this->get()->first();
            }

            public function count(): int
            {
                return $this->get()->count();
            }
        };
    }

    public static function where(string $col, mixed $op = null, mixed $val = null)
    {
        return static::query()->where($col, $op, $val);
    }

    public static function with(mixed $relations)
    {
        return static::query()->with($relations);
    }
}
