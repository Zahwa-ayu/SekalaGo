<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Cari kata di beberapa kolom. Kolom langsung ditulis apa adanya,
     * kolom relasi dengan "relasi.kolom".
     *
     * @param  list<string>  $columns
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';

        return $query->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, fn ($r) => $r->where($field, 'like', $like));
                } else {
                    $q->orWhere($column, 'like', $like);
                }
            }
        });
    }
}