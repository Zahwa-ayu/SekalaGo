<?php

namespace App\Services;

use App\Models\City;

class CityResolver
{
    /**
     * Kota master yang cocok dengan kata yang diketik pengunjung, lewat nama atau nama lain.
     * Contoh: "Jogja" cocok dengan Yogyakarta.
     *
     * @return list<int>
     */
    public function idsFor(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';

        return City::query()
            ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('aliases', 'like', $like))
            ->pluck('id')
            ->all();
    }
}