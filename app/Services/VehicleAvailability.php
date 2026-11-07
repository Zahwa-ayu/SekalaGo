<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VehicleAvailability
{
    /**
     * Pesanan yang sedang menahan unit, yang menyentuh rentang [$from, $to), dikelompokkan per armada.
     * Pesanan menempati hari check_in sampai sehari sebelum check_out.
     * Pesanan yang menunggu bayar atau persetujuan hanya dihitung selama belum kedaluwarsa.
     */
    public function overlapping(iterable $vehicleIds, Carbon $from, Carbon $to): Collection
    {
        return $this->holding(Booking::query()->whereIn('vehicle_id', collect($vehicleIds)->all()))
            ->where('check_in', '<', $to->toDateString())
            ->where('check_out', '>', $from->toDateString())
            ->get(['id', 'vehicle_id', 'check_in', 'check_out'])
            ->groupBy('vehicle_id');
    }

    /** Jumlah unit yang masih kosong pada SETIAP hari di rentang (nilai terkecil dari semua hari). */
    public function freeUnits(Vehicle $vehicle, Carbon $pickup, Carbon $return, ?Collection $bookings = null): int
    {
        $bookings ??= $this->overlapping([$vehicle->id], $pickup, $return)->get($vehicle->id, collect());

        $free = $vehicle->units;

        for ($day = $pickup->copy()->startOfDay(); $day->lt($return); $day->addDay()) {
            $used = $bookings->filter(fn ($b) => $b->check_in->lte($day) && $b->check_out->gt($day))->count();
            $free = min($free, $vehicle->units - $used);
        }

        return max(0, $free);
    }

    /** Masih ada pesanan aktif yang belum selesai untuk armada ini? */
    public function hasActiveBookings(Vehicle $vehicle): bool
    {
        return $this->holding(Booking::query()->where('vehicle_id', $vehicle->id))
            ->where('check_out', '>', today()->toDateString())
            ->exists();
    }

    private function holding(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'confirmed')
              ->orWhere(fn ($pending) => $pending
                  ->whereIn('status', ['pending_payment', 'pending_approval'])
                  ->where('expires_at', '>', now()));
        });
    }

    /** Jumlah unit terbanyak yang terpakai bersamaan pada hari mana pun dari hari ini ke depan. */
public function peakUsage(Vehicle $vehicle): int
{
    $bookings = $this->holding(Booking::query()->where('vehicle_id', $vehicle->id))
        ->where('check_out', '>', today()->toDateString())
        ->get(['id', 'vehicle_id', 'check_in', 'check_out']);

    if ($bookings->isEmpty()) {
        return 0;
    }

    $start = $bookings->sortBy('check_in')->first()->check_in->copy();
    if ($start->lt(today())) {
        $start = today();
    }

    $end  = $bookings->sortByDesc('check_out')->first()->check_out;
    $peak = 0;

    for ($day = $start->copy(); $day->lt($end); $day->addDay()) {
        $used = $bookings->filter(fn ($b) => $b->check_in->lte($day) && $b->check_out->gt($day))->count();
        $peak = max($peak, $used);
    }

    return $peak;
}
}