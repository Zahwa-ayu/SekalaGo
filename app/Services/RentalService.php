<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalService
{
    public function __construct(
        private BookingService $bookings,
        private VehicleAvailability $availability,
    ) {
    }

    /** Mitra mencatat mobil sudah di tangan penyewa. */
    public function markPickedUp(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $fresh = $this->lockedRental($booking);

            if ($fresh->picked_up_at) {
                $this->fail('rental_already_picked_up');
            }

            if ($fresh->check_in->gt(today())) {
                $this->fail('rental_too_early');
            }

            $fresh->update(['picked_up_at' => now()]);

            return $fresh;
        });
    }

    /**
     * Mitra mencatat mobil sudah kembali. Denda dihitung sistem; mitra boleh menggantinya
     * ($lateFeeOverride), termasuk 0 untuk membebaskan denda.
     */
    public function markReturned(Booking $booking, ?int $lateFeeOverride = null): Booking
    {
        return DB::transaction(function () use ($booking, $lateFeeOverride) {
            $fresh = $this->lockedRental($booking);

            if (! $fresh->picked_up_at) {
                $this->fail('rental_not_picked_up');
            }

            if ($fresh->returned_at) {
                $this->fail('rental_already_returned');
            }

            $returnedAt = now();
            [$minutes, $computed] = $this->lateFee($fresh, $returnedAt);

            $fresh->update([
                'returned_at'       => $returnedAt,
                'late_minutes'      => $minutes,
                'late_fee_computed' => $computed,
                'late_fee'          => max(0, $lateFeeOverride ?? $computed),
            ]);

            return $fresh;
        });
    }

    /**
     * Denda keterlambatan: lewat toleransi, dihitung per jam (dibulatkan ke atas) sebesar persen tarif harian,
     * paling banyak satu hari penuh untuk setiap 24 jam.
     *
     * @return array{0: int, 1: int} [menit terlambat, denda dalam IDR]
     */
    public function lateFee(Booking $booking, Carbon $returnedAt): array
    {
        if (! $booking->due_at) {
            return [0, 0];
        }

        $late    = max(0, (int) floor($booking->due_at->diffInMinutes($returnedAt, false)));
        $percent = (int) $booking->late_fee_percent;

        if ($late <= (int) $booking->late_grace_minutes || $percent <= 0) {
            return [$late, 0];
        }

        $hours = (int) ceil($late / 60);
        $days  = intdiv($hours, 24);
        $rest  = $hours % 24;
        $daily = (int) $booking->price_per_night;

        $fee = $days * $daily + min($rest * $daily * $percent / 100, $daily);

        return [$late, (int) round($fee)];
    }

    /** Mobil milik mitra yang sudah lewat batas kembali dan belum ditandai kembali. */
    public function overdueFor(Partner $partner): Collection
    {
        return Booking::where('partner_id', $partner->id)
            ->where('product_type', 'car')
            ->where('status', 'confirmed')
            ->whereNotNull('picked_up_at')
            ->whereNull('returned_at')
            ->where('due_at', '<', now())
            ->with('vehicle.translations')
            ->orderBy('due_at')
            ->get();
    }

    /**
     * Pesanan yang belum diambil dan terancam tidak kebagian unit hari ini atau besok karena
     * ada mobil yang terlambat. Yang paling baru dipesan dianggap terancam lebih dulu.
     */
    public function atRisk(Vehicle $vehicle): Collection
    {
        $bookings = $this->availability
            ->overlapping([$vehicle->id], today(), today()->addDays(2))
            ->get($vehicle->id, collect());

        $riskIds = collect();

        foreach ([today(), today()->addDay()] as $day) {
            $using  = $bookings->filter(fn ($b) => $b->check_in->lte($day) && $b->occupiedUntil()->gt($day));
            $excess = $using->count() - $vehicle->units;

            if ($excess > 0) {
                $waiting = $using->filter(fn ($b) => $b->picked_up_at === null)->sortByDesc('id');
                $riskIds = $riskIds->merge($waiting->take($excess)->pluck('id'));
            }
        }

        if ($riskIds->isEmpty()) {
            return collect();
        }

        return Booking::whereIn('id', $riskIds->unique()->all())->orderBy('check_in')->get();
    }

    public function isAtRisk(Booking $booking): bool
    {
        if ($booking->rentalStage() !== 'upcoming' || ! $booking->vehicle_id) {
            return false;
        }

        $vehicle = $booking->vehicle ?? Vehicle::find($booking->vehicle_id);

        return $vehicle !== null && $this->atRisk($vehicle)->contains('id', $booking->id);
    }

    /**
     * Armada milik mitra yang terlambat dan membuat pesanan lain terancam.
     *
     * @return Collection<int, array{vehicle: Vehicle, overdue: Collection, atRisk: Collection}>
     */
    public function conflicts(Partner $partner): Collection
    {
        return $this->overdueFor($partner)
            ->groupBy('vehicle_id')
            ->map(function (Collection $group) {
                $vehicle = $group->first()->vehicle;

                return ['vehicle' => $vehicle, 'overdue' => $group, 'atRisk' => $this->atRisk($vehicle)];
            })
            ->filter(fn (array $conflict) => $conflict['atRisk']->isNotEmpty())
            ->values();
    }

    /** Mitra membatalkan pesanan yang terancam; refund penuh lewat jalur pembatalan biasa. */
    public function cancelAtRisk(Booking $booking, string $reason): void
    {
        $fresh = Booking::with('vehicle')->findOrFail($booking->id);

        if (! $this->isAtRisk($fresh)) {
            $this->fail('rental_cancel_not_allowed');
        }

        $this->bookings->cancel($fresh, 'partner', $reason);
    }

    private function lockedRental(Booking $booking): Booking
    {
        $fresh = Booking::lockForUpdate()->findOrFail($booking->id);

        if (! $fresh->isCar()) {
            $this->fail('rental_not_car');
        }

        if ($fresh->status !== 'confirmed') {
            $this->fail('rental_not_confirmed');
        }

        return $fresh;
    }

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['booking' => __('ui.' . $key)]);
    }
}