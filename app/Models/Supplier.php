<?php

namespace App\Models;

class Supplier
{
    public int $id = 1;
    public int $user_id = 1;
    public string $business_name = 'Keripik Pisang Original Pak Aceng';
    public string $phone = '081298765432';
    public string $address = 'Sentra UMKM Keripik Pisang, Bantul, D.I. Yogyakarta';

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function user()
    {
        return User::find($this->user_id);
    }

    public static function updateOrCreate(array $attributes = [], array $values = []): self
    {
        return new self(array_merge($attributes, $values));
    }

    public static function first(): self
    {
        return new self();
    }
}
