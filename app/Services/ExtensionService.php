<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\CompanyUser;
use App\Models\Currency;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExtensionService
{
    public const MAX_EXTENSION_DAYS = 7;
    public const MAX_TOTAL_DAYS     = 30;

    public function __construct(
        private PaymentGateway $gateway,
        private DepositService $deposits,
        private VehicleAvailability $availability,
    ) {
    }

    /** Hanya mobil yang sedang di tangan penyewa dan belum lewat batas kembali. */
    public function canExtend(Booking $booking): bool
    {
        return $booking->isCar()
            && $booking->rentalStage() === 'ongoing'
            && $booking->nights < self::MAX_TOTAL_DAYS;
    }

    /** @return array{days: int, amount: int, new_check_out: Carbon, new_due_at: Carbon, available: bool} */
    public function quote(Booking $booking, int $days): array
    {
        $from = $booking->check_out->copy();
        $to   = $from->copy()->addDays($days);

        return [
            'days'          => $days,
            'amount'        => (int) $booking->price_per_night * $days,
            'new_check_out' => $to,
            'new_due_at'    => $booking->due_at->copy()->addDays($days),
            'available'     => $this->isFree($booking, $from, $to),
        ];
    }

    /**
     * Perpanjang sewa $days hari. Dibayar lewat jalur pesanan asalnya: saldo kantor untuk corporate
     * (limit dan anggaran tetap berlaku), gateway untuk pelanggan biasa. Gagal bayar = tidak ada yang berubah.
     */
    public function extend(Booking $booking, int $days, User $by, ?CompanyUser $member = null, string $simulate = 'success'): BookingExtension
    {
        if ($days < 1 || $days > self::MAX_EXTENSION_DAYS) {
            throw ValidationException::withMessages([
                'days' => __('ui.extension_days_range', ['max' => self::MAX_EXTENSION_DAYS]),
            ]);
        }

        return DB::transaction(function () use ($booking, $days, $by, $member, $simulate) {
            $locked = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $this->canExtend($locked)) {
                $this->fail('extension_not_allowed');
            }

            if ($locked->nights + $days > self::MAX_TOTAL_DAYS) {
                $this->fail('extension_too_long');
            }

            // Kunci baris armada: perpanjangan dan pemesanan baru diproses bergantian
            $vehicle = Vehicle::lockForUpdate()->findOrFail($locked->vehicle_id);
            $from    = $locked->check_out->copy();
            $to      = $from->copy()->addDays($days);

            if (! $this->isFree($locked, $from, $to, $vehicle)) {
                $this->fail('extension_unavailable');
            }

            $amount = (int) $locked->price_per_night * $days;
            $rate   = $locked->total_price > 0 ? $locked->platform_fee / $locked->total_price : 0;   // komisi pesanan asal
            $fee    = (int) round($amount * $rate);

            [$method, $reference] = $locked->payment_method === 'deposit'
                ? $this->chargeDeposit($locked, $amount, $by, $member)
                : $this->chargeGateway($locked, $amount, $simulate);

            $extension = BookingExtension::create([
                'booking_id'        => $locked->id,
                'days'              => $days,
                'old_check_out'     => $from->toDateString(),
                'new_check_out'     => $to->toDateString(),
                'amount'            => $amount,
                'platform_fee'      => $fee,
                'vendor_revenue'    => $amount - $fee,
                'payment_method'    => $method,
                'payment_reference' => $reference,
                'created_by'        => $by->id,
            ]);

            $decimals = (int) (Currency::where('code', $locked->currency_code)->value('decimal_places') ?? 2);
            $newTotal = $locked->total_price + $amount;

            $locked->update([
                'check_out'           => $to->toDateString(),
                'due_at'              => $locked->due_at->copy()->addDays($days),
                'nights'              => $locked->nights + $days,
                'total_price'         => $newTotal,
                'platform_fee'        => $locked->platform_fee + $fee,
                'vendor_revenue'      => $locked->vendor_revenue + ($amount - $fee),
                'total_price_display' => round($newTotal / $locked->exchange_rate, $decimals),
            ]);

            return $extension;
        });
    }

    /** Hari tambahan harus bebas: aktif, tidak diblokir, dan masih ada unit (pesanan ini sendiri tidak dihitung). */
    private function isFree(Booking $booking, Carbon $from, Carbon $to, ?Vehicle $vehicle = null): bool
    {
        $vehicle ??= Vehicle::findOrFail($booking->vehicle_id);

        if (! Vehicle::whereKey($vehicle->id)->availableFor($from, $to)->exists()) {
            return false;
        }

        $others = $this->availability
            ->overlapping([$vehicle->id], $from, $to)
            ->get($vehicle->id, collect())
            ->reject(fn ($other) => $other->id === $booking->id);

        return $this->availability->freeUnits($vehicle, $from, $to, $others) >= 1;
    }

    /** @return array{0: string, 1: string} */
    private function chargeDeposit(Booking $booking, int $amount, User $by, ?CompanyUser $member): array
    {
        $member ??= CompanyUser::where('user_id', $by->id)->first();

        if (! $member || $member->company_id !== $booking->company_id) {
            $this->fail('extension_not_allowed');
        }

        // Kunci baris karyawan supaya dua permintaan bersamaan tidak sama-sama lolos cek anggaran
        CompanyUser::lockForUpdate()->find($member->id);

        $member->loadMissing('company', 'position');

        if ($member->evaluatePolicy($amount)['needs_approval']) {
            $this->fail('extension_needs_finance');
        }

        $tx = $this->deposits->debit(
            $member->company,
            $amount,
            'Perpanjangan ' . $booking->booking_code . ' - ' . $booking->hotel_name,
            $booking->booking_code,
            $by
        );

        return ['deposit', 'DEP-' . $tx->id];
    }

    /** @return array{0: string, 1: string} */
    private function chargeGateway(Booking $booking, int $amount, string $simulate): array
    {
        // Gateway menagih total_price dari pesanan yang diberikan, jadi yang dikirim adalah salinan
        // (tidak disimpan) dengan jumlah perpanjangan saja.
        $charge               = $booking->replicate();
        $charge->total_price  = $amount;
        $charge->booking_code = $booking->booking_code . '-EXT';

        $result = $this->gateway->charge($charge, $simulate);

        if (! $result['success']) {
            throw ValidationException::withMessages(['payment' => __('ui.payment_failed')]);
        }

        return ['gateway', $result['reference']];
    }

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['extension' => __('ui.' . $key)]);
    }
}