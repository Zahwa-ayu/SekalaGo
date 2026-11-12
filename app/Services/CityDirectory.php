<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CityDirectory
{
    /** Nama kota aktif yang punya hotel ('hotels') atau armada ('cars') yang sudah disetujui. */
    public static function names(string $for): Collection
    {
        return self::query($for)->pluck('name');
    }

    /** Saran untuk kolom kota: nama beserta keterangan provinsi dan nama lain. */
    public static function suggestions(string $for): Collection
    {
        return self::query($for)->get()->map(function (City $city) {
            $notes = array_filter([$city->province, implode(', ', $city->aliasList())]);

            return [
                'name'  => $city->name,
                'label' => $city->name . ($notes ? ' - ' . implode(' | ', $notes) : ''),
            ];
        });
    }

    /** Kota favorit untuk Beranda, lengkap dengan jumlah dan harga mulai dari. Kota tanpa produk tidak ditampilkan. */
    public static function featured(int $limit = 8): Collection
    {
        return City::query()
            ->active()
            ->where('is_featured', true)
            ->withCount([
                'hotels as hotels_count'     => fn ($q) => $q->where('status', 'approved')->whereHas('roomTypes'),
                'vehicles as vehicles_count' => fn ($q) => $q->where('status', 'approved'),
            ])
            ->withMin(['roomTypes as min_room_price' => fn ($q) => $q->where('hotels.status', 'approved')], 'base_price')
            ->withMin(['vehicles as min_vehicle_price' => fn ($q) => $q->where('status', 'approved')], 'price_per_day')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (City $city) => $city->hotels_count + $city->vehicles_count > 0)
            ->take($limit)
            ->values();
    }

    private static function query(string $for): Builder
    {
        return City::query()
            ->active()
            ->when($for === 'hotels', fn ($q) => $q->whereHas('hotels', fn ($h) => $h->where('status', 'approved')->whereHas('roomTypes')))
            ->when($for === 'cars', fn ($q) => $q->whereHas('vehicles', fn ($v) => $v->where('status', 'approved')))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}