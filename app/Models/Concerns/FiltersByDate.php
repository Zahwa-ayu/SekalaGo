<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait FiltersByDate
{
    /** Saring menurut rentang tanggal (format Y-m-d). Tanggal tidak valid diabaikan; urutan terbalik ditukar. */
    public function scopeBetweenDates(Builder $query, string $column, ?string $from, ?string $to): Builder
    {
        $start = self::parseFilterDate($from);
        $end   = self::parseFilterDate($to);

        if ($start && $end && $end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return $query
            ->when($start, fn ($q) => $q->whereDate($column, '>=', $start->toDateString()))
            ->when($end, fn ($q) => $q->whereDate($column, '<=', $end->toDateString()));
    }

    private static function parseFilterDate(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
