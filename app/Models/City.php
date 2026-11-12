<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'province', 'aliases', 'image_path', 'is_featured', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return list<string> nama lain kota ini */
    public function aliasList(): array
    {
        return collect(explode(',', (string) $this->aliases))
            ->map(fn ($alias) => trim($alias))
            ->filter()
            ->values()
            ->all();
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /** Pilihan dropdown untuk mitra: kota aktif, ditambah kota yang sedang dipakai meski sudah dinonaktifkan. */
    public static function optionsFor(?int $currentId = null)
    {
        return static::query()
            ->where(fn ($q) => $q
                ->where('is_active', true)
                ->when($currentId, fn ($w) => $w->orWhere('id', $currentId)))
            ->orderBy('name')
            ->get();
    }
}