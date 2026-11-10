<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VehicleAvailability
{
    private const COLUMNS = ['id', 'vehicle_id', 'check_in', 'check_out', 'picked_up_at', 'returned_at'];

    /**
     * Pesanan yang sedang menahan unit dan menyentuh rentang [$from, $to), dikelompokkan per armada.
     * Pesanan menempati hari check_in sampai sehari sebelum check_out. Mobil yang sudah diambil
     * tapi belum kembali setelah tanggal kembali tetap memakai unit sampai hari ini.
     * Pesanan menunggu bayar atau persetujuan hanya dihitung selama belum kedaluwarsa.
     */
    public function overlapping(iterable $vehicleIds, Carbon $from, Carbon $to): Collection
    {
        return $this->holding(Booking::query()->whereIn('vehicle_id', collect($vehicleIds)->all()))
            ->where('check_in', '<', $to->toDateString())
            ->where(fn ($q) => $q
                ->where('check_out', '>', $from->toDateString())
                ->orWhere(fn ($late) => $this->stillOut($late)))
            ->get(self::COLUMNS)
            ->filter(fn (Booking $b) => $b->occupiedUntil()->gt($from))
            ->groupBy('vehicle_id');
    }

    /** Jumlah unit yang masih kosong pada SETIAP hari di rentang (nilai terkecil dari semua hari). */
    public function freeUnits(Vehicle $vehicle, Carbon $pickup, Carbon $return, ?Collection $bookings = null): int
    {
        $bookings ??= $this->overlapping([$vehicle->id], $pickup, $return)->get($vehicle->id, collect());

        $free = $vehicle->units;

        for ($day = $pickup->copy()->startOfDay(); $day->lt($return); $day->addDay()) {
            $used = $bookings->filter(fn ($b) => $b->check_in->lte($day) && $b->occupiedUntil()->gt($day))->count();
            $free = min($free, $vehicle->units - $used);
        }

        return max(0, $free);
    }

    /** Masih ada pesanan yang berjalan atau belum selesai untuk armada ini? */
    public function hasActiveBookings(Vehicle $vehicle): bool
    {
        return $this->holding(Booking::query()->where('vehicle_id', $vehicle->id))
            ->where(fn ($q) => $q
                ->where('check_out', '>', today()->toDateString())
                ->orWhere(fn ($late) => $late->whereNotNull('picked_up_at')->whereNull('returned_at')))
            ->exists();
    }

    /** Jumlah unit terbanyak yang terpakai bersamaan pada hari mana pun dari hari ini ke depan. */
    public function peakUsage(Vehicle $vehicle): int
    {
        $bookings = $this->holding(Booking::query()->where('vehicle_id', $vehicle->id))
            ->where(fn ($q) => $q
                ->where('check_out', '>', today()->toDateString())
                ->orWhere(fn ($late) => $late->whereNotNull('picked_up_at')->whereNull('returned_at')))
            ->get(self::COLUMNS);

        if ($bookings->isEmpty()) {
            return 0;
        }

        $start = $bookings->sortBy('check_in')->first()->check_in->copy();
        if ($start->lt(today())) {
            $start = today();
        }

        $end  = $bookings->map(fn (Booking $b) => $b->occupiedUntil())->sortDesc()->first();
        $peak = 0;

        for ($day = $start->copy(); $day->lt($end); $day->addDay()) {
            $used = $bookings->filter(fn ($b) => $b->check_in->lte($day) && $b->occupiedUntil()->gt($day))->count();
            $peak = max($peak, $used);
        }

        return $peak;
    }

    private function stillOut(Builder $query): Builder
    {
        return $query->whereNotNull('picked_up_at')
            ->whereNull('returned_at')
            ->where('check_out', '<=', today()->toDateString());
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
}