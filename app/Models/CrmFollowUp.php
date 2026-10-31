<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CrmFollowUp
{
    public int $id = 0;
    public int $crm_alert_id = 0;
    public ?int $supplier_id = null;
    public int $warung_id = 0;
    public Carbon $follow_up_date;
    public string $contact_method = 'whatsapp';
    public string $result_notes = '';
    public ?string $next_action = null;
    public string $status = 'selesai';
    public Carbon $created_at;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $val) {
            if ($key === 'follow_up_date') {
                $this->follow_up_date = $val instanceof Carbon ? $val : Carbon::parse($val);
            } elseif ($key === 'created_at') {
                $this->created_at = $val instanceof Carbon ? $val : Carbon::parse($val);
            } else {
                $this->{$key} = $val;
            }
        }

        if (!isset($this->follow_up_date)) {
            $this->follow_up_date = Carbon::now();
        }
        if (!isset($this->created_at)) {
            $this->created_at = Carbon::now();
        }
    }

    public function alert(): ?CrmAlert
    {
        return CrmAlert::findOrFail($this->crm_alert_id);
    }

    public function warung(): ?Warung
    {
        return Warung::find($this->warung_id);
    }

    public static function create(array $attributes): self
    {
        $records = Cache::get('crm_follow_ups_store', []);
        $nextId = count($records) > 0 ? (max(array_column($records, 'id')) + 1) : 1;

        $attributes['id'] = $nextId;
        $instance = new self($attributes);

        $records[] = [
            'id' => $instance->id,
            'crm_alert_id' => $instance->crm_alert_id,
            'supplier_id' => $instance->supplier_id,
            'warung_id' => $instance->warung_id,
            'follow_up_date' => $instance->follow_up_date->toDateString(),
            'contact_method' => $instance->contact_method,
            'result_notes' => $instance->result_notes,
            'next_action' => $instance->next_action,
            'status' => $instance->status,
            'created_at' => $instance->created_at->toDateTimeString(),
        ];

        Cache::forever('crm_follow_ups_store', $records);
        return $instance;
    }

    public static function all(): Collection
    {
        $records = Cache::get('crm_follow_ups_store', []);
        return collect($records)->map(fn($r) => new self($r));
    }

    public static function where(string $col, mixed $op = null, mixed $val = null)
    {
        $actualVal = ($val === null) ? $op : $val;
        return new class($col, $actualVal) {
            protected string $col;
            protected mixed $val;
            protected array $orders = [];

            public function __construct($col, $val)
            {
                $this->col = $col;
                $this->val = $val;
            }

            public function orderBy(string $col, string $dir = 'asc'): self
            {
                $this->orders[] = [$col, strtolower($dir)];
                return $this;
            }

            public function get(): Collection
            {
                $items = CrmFollowUp::all()->filter(fn($item) => ($item->{$this->col} ?? null) == $this->val);
                foreach ($this->orders as [$col, $dir]) {
                    $items = ($dir === 'desc') ? $items->sortByDesc($col) : $items->sortBy($col);
                }
                return $items->values();
            }
        };
    }
}
