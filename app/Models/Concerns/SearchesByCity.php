<?php

namespace App\Models\Concerns;

use App\Services\CityResolver;
use Illuminate\Database\Eloquent\Builder;

trait SearchesByCity
{
    /**
     * Cocokkan kota: nama yang diketik (seperti sebelumnya) ATAU kota master yang namanya
     * atau nama lainnya cocok (misalnya "Jogja" untuk Yogyakarta).
     */
    public function scopeInCity(Builder $query, string $term): Builder
    {
        $term = trim($term);
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $ids  = app(CityResolver::class)->idsFor($term);

        return $query->where(function (Builder $q) use ($like, $ids) {
            $q->where('city', 'like', $like);

            if ($ids !== []) {
                $q->orWhereIn('city_id', $ids);
            }
        });
    }
}