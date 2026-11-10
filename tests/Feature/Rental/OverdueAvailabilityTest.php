<?php

namespace Tests\Feature\Rental;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\RentalService;
use App\Services\VehicleAvailability;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OverdueAvailabilityTest extends TestCase
{
    /** @return array{0: Vehicle, 1: Booking} sewa 1 hari mulai hari ini (kembali besok 12.00) */
    private function rental(int $units = 1): array
    {
        $vehicle = $this->makeVehicle($this->makePartner(), ['units' => $units]);
        $booking = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1));

        return [$vehicle, $booking];
    }

    private function freeToday(Vehicle $vehicle): int
    {
        return app(VehicleAvailability::class)->freeUnits($vehicle, today(), today()->addDay());
    }

    private function goOverdue(Booking $booking): void
    {
        app(RentalService::class)->markPickedUp($booking);
        $this->travelTo($this->day(1)->setTime(13, 0));   // setelah batas kembali
    }

    public function test_an_unreturned_car_keeps_its_unit_busy_after_the_return_date(): void
    {
        [$vehicle, $booking] = $this->rental();

        $this->goOverdue($booking);

        $this->assertSame(0, $this->freeToday($vehicle));
    }

    public function test_a_car_never_marked_as_picked_up_does_not_block_the_unit(): void
    {
        [$vehicle] = $this->rental();

        $this->travelTo($this->day(1)->setTime(13, 0));

        $this->assertSame(1, $this->freeToday($vehicle));
    }

    public function test_the_unit_is_free_again_once_the_car_is_returned(): void
    {
        [$vehicle, $booking] = $this->rental();
        $this->goOverdue($booking);

        app(RentalService::class)->markReturned($booking);

        $this->assertSame(1, $this->freeToday($vehicle));
    }

    public function test_only_today_is_blocked_tomorrow_stays_open(): void
    {
        [$vehicle, $booking] = $this->rental();
        $this->goOverdue($booking);

        $tomorrow = app(VehicleAvailability::class)->freeUnits($vehicle, today()->addDay(), today()->addDays(2));

        $this->assertSame(1, $tomorrow);
    }

    public function test_other_units_stay_available_while_one_is_overdue(): void
    {
        [$vehicle, $booking] = $this->rental(units: 2);

        $this->goOverdue($booking);

        $this->assertSame(1, $this->freeToday($vehicle));
    }

    public function test_nobody_can_book_the_blocked_unit_for_today(): void
    {
        [$vehicle, $booking] = $this->rental();
        $this->goOverdue($booking);

        $this->expectException(ValidationException::class);
        $this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1);
    }

    public function test_an_overdue_car_counts_as_an_active_booking_until_it_is_returned(): void
    {
        [$vehicle, $booking] = $this->rental();
        $service = app(VehicleAvailability::class);

        $this->goOverdue($booking);
        $this->assertTrue($service->hasActiveBookings($vehicle));
        $this->assertSame(1, $service->peakUsage($vehicle));

        app(RentalService::class)->markReturned($booking);
        $this->assertFalse($service->hasActiveBookings($vehicle));
        $this->assertSame(0, $service->peakUsage($vehicle));
    }
}