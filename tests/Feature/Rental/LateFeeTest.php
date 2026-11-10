<?php

namespace Tests\Feature\Rental;

use App\Models\Booking;
use App\Services\RentalService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LateFeeTest extends TestCase
{
    /** Sewa 2 hari mulai hari ini, jatuh tempo lusa 12.00 (Rp450.000 per hari, denda 10% per jam, toleransi 60 menit). */
    private function booking(array $vehicle = []): Booking
    {
        $vehicle = $this->makeVehicle($this->makePartner(), array_merge(['price_per_day' => 450000], $vehicle));

        return $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 2));
    }

    private function lateBy(Booking $booking, int $minutes): array
    {
        return app(RentalService::class)->lateFee($booking, $booking->due_at->copy()->addMinutes($minutes));
    }

    public static function feeCases(): array
    {
        // [menit setelah jatuh tempo, menit terlambat yang dicatat, denda]
        return [
            'tepat waktu'                  => [0, 0, 0],
            'lebih awal'                   => [-120, 0, 0],
            'dalam toleransi (45 menit)'   => [45, 45, 0],
            'tepat di batas toleransi'     => [60, 60, 0],
            '61 menit dihitung 2 jam'      => [61, 61, 90000],
            '2,5 jam dihitung 3 jam'       => [150, 150, 135000],
            '10 jam mencapai satu hari'    => [600, 600, 450000],
            '11 jam tidak melebihi sehari' => [660, 660, 450000],
            '24 jam tepat'                 => [1440, 1440, 450000],
            '26 jam'                       => [1560, 1560, 540000],
            '49 jam'                       => [2940, 2940, 945000],
        ];
    }

    #[DataProvider('feeCases')]
    public function test_the_late_fee_follows_the_hourly_rule_capped_at_one_day(int $minutes, int $expectedMinutes, int $expectedFee): void
    {
        [$late, $fee] = $this->lateBy($this->booking(), $minutes);

        $this->assertSame($expectedMinutes, $late);
        $this->assertSame($expectedFee, $fee);
    }

    public function test_a_vehicle_without_late_fee_never_charges(): void
    {
        [$late, $fee] = $this->lateBy($this->booking(['late_fee_percent' => 0]), 300);

        $this->assertSame(300, $late);
        $this->assertSame(0, $fee);
    }

    public function test_the_percentage_and_grace_period_come_from_the_vehicle(): void
    {
        $booking = $this->booking(['late_fee_percent' => 20, 'late_grace_minutes' => 0]);

        [, $fee] = $this->lateBy($booking, 30);   // 1 jam x 20% x Rp450.000

        $this->assertSame(90000, $fee);
    }

    public function test_the_return_time_of_the_vehicle_sets_the_deadline(): void
    {
        $booking = $this->booking(['return_time' => '10:30']);

        $this->assertSame($booking->check_out->format('Y-m-d') . ' 10:30', $booking->due_at->format('Y-m-d H:i'));
    }

    public function test_the_policy_is_copied_to_the_booking_and_later_changes_do_not_apply(): void
    {
        $booking = $this->booking();

        $this->assertSame(60, $booking->late_grace_minutes);
        $this->assertSame(10, $booking->late_fee_percent);

        $booking->vehicle->update(['late_fee_percent' => 50, 'late_grace_minutes' => 0]);

        [, $fee] = $this->lateBy($booking->fresh(), 150);

        $this->assertSame(135000, $fee);   // tetap memakai 10% dan toleransi 60 menit saat dipesan
    }
}