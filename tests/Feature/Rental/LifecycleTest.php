<?php

namespace Tests\Feature\Rental;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\RentalService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LifecycleTest extends TestCase
{
    private function rentals(): RentalService
    {
        return app(RentalService::class);
    }

    /** Sewa terkonfirmasi: mulai $from hari dari sekarang selama $days hari (batas kembali pukul 12.00). */
    private function rental(int $from = 0, int $days = 1, array $vehicle = []): Booking
    {
        $vehicle = $this->makeVehicle($this->makePartner(), $vehicle);

        return $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: $from, days: $days));
    }

    private function assertRejected(callable $action, string $expectedMessageKey): void
    {
        try {
            $action();
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('booking', $e->errors());
        }
    }

    public function test_a_rental_moves_through_its_stages(): void
    {
        $booking   = $this->rental(from: 0, days: 1);   // kembali besok pukul 12.00
        $beforeDue = $this->day(1)->setTime(11, 0);
        $afterDue  = $this->day(1)->setTime(13, 0);

        $this->assertSame('upcoming', $booking->fresh()->rentalStage());

        $this->rentals()->markPickedUp($booking);
        $this->assertSame('ongoing', $booking->fresh()->rentalStage());

        $this->travelTo($beforeDue);
        $this->assertSame('ongoing', $booking->fresh()->rentalStage());

        $this->travelTo($afterDue);
        $this->assertSame('overdue', $booking->fresh()->rentalStage());

        $this->rentals()->markReturned($booking);
        $this->assertSame('returned', $booking->fresh()->rentalStage());
    }

    public function test_only_confirmed_car_bookings_have_a_stage(): void
    {
        $partner = $this->makePartner();
        $vehicle = $this->makeVehicle($partner);
        $room    = $this->makeRoomType($this->makeHotel($partner), quota: 2);

        $unpaidCar = $this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1);
        $hotel     = $this->pay($this->bookRoom($this->makeCustomer(), $room));

        $this->assertNull($unpaidCar->rentalStage());
        $this->assertNull($hotel->rentalStage());
    }

    public function test_pick_up_is_recorded_with_the_time(): void
    {
        $booking = $this->rental();

        $picked = $this->rentals()->markPickedUp($booking);

        $this->assertTrue($picked->picked_up_at->equalTo(now()));
    }

    public function test_pick_up_cannot_be_recorded_twice(): void
    {
        $booking = $this->rental();
        $this->rentals()->markPickedUp($booking);

        $this->assertRejected(fn () => $this->rentals()->markPickedUp($booking), 'rental_already_picked_up');
    }

    public function test_pick_up_cannot_be_recorded_before_the_pick_up_date(): void
    {
        $booking = $this->rental(from: 3, days: 2);

        $this->assertRejected(fn () => $this->rentals()->markPickedUp($booking), 'rental_too_early');
        $this->assertNull($booking->fresh()->picked_up_at);
    }

    public function test_unpaid_cancelled_and_hotel_bookings_cannot_be_recorded(): void
    {
        $partner = $this->makePartner();
        $vehicle = $this->makeVehicle($partner, ['units' => 3]);
        $room    = $this->makeRoomType($this->makeHotel($partner), quota: 2);

        $unpaid    = $this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1);
        $cancelled = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1));
        app(BookingService::class)->cancel($cancelled, 'customer');
        $hotel = $this->pay($this->bookRoom($this->makeCustomer(), $room, from: 0, nights: 1));

        foreach ([$unpaid, $cancelled->fresh(), $hotel] as $booking) {
            $this->assertRejected(fn () => $this->rentals()->markPickedUp($booking), 'rental');
        }
    }

    public function test_return_requires_a_recorded_pick_up_and_works_only_once(): void
    {
        $booking = $this->rental();

        $this->assertRejected(fn () => $this->rentals()->markReturned($booking), 'rental_not_picked_up');

        $this->rentals()->markPickedUp($booking);
        $this->rentals()->markReturned($booking);

        $this->assertRejected(fn () => $this->rentals()->markReturned($booking), 'rental_already_returned');
    }

    public function test_an_on_time_return_has_no_late_fee(): void
    {
        $booking = $this->rental();
        $this->rentals()->markPickedUp($booking);

        $this->travelTo($booking->fresh()->due_at->copy()->subHour());
        $returned = $this->rentals()->markReturned($booking);

        $this->assertSame(0, $returned->late_minutes);
        $this->assertSame(0, $returned->late_fee_computed);
        $this->assertSame(0, $returned->late_fee);
    }

    public function test_a_late_return_records_minutes_and_the_computed_fee(): void
    {
        $booking = $this->rental();   // Rp450.000 per hari, kembali besok pukul 12.00
        $this->rentals()->markPickedUp($booking);

        $this->travelTo($booking->fresh()->due_at->copy()->addMinutes(150));
        $returned = $this->rentals()->markReturned($booking);

        $this->assertSame(150, $returned->late_minutes);
        $this->assertSame(135000, $returned->late_fee_computed);
        $this->assertSame(135000, $returned->late_fee);
        $this->assertTrue($returned->returned_at->equalTo(now()));
    }

    public function test_the_partner_can_change_the_fee_but_the_system_estimate_stays_on_record(): void
    {
        $booking = $this->rental();
        $this->rentals()->markPickedUp($booking);

        $this->travelTo($booking->fresh()->due_at->copy()->addMinutes(150));
        $returned = $this->rentals()->markReturned($booking, 100000);

        $this->assertSame(135000, $returned->late_fee_computed);
        $this->assertSame(100000, $returned->late_fee);
    }

    public function test_the_partner_can_waive_the_fee_entirely(): void
    {
        $booking = $this->rental();
        $this->rentals()->markPickedUp($booking);

        $this->travelTo($booking->fresh()->due_at->copy()->addMinutes(150));
        $returned = $this->rentals()->markReturned($booking, 0);

        $this->assertSame(135000, $returned->late_fee_computed);
        $this->assertSame(0, $returned->late_fee);
    }

    public function test_the_late_fee_never_touches_the_amount_paid_or_the_commission(): void
    {
        $booking = $this->rental();
        $paid    = $booking->total_price;
        $fee     = $booking->platform_fee;
        $this->rentals()->markPickedUp($booking);

        $this->travelTo($booking->fresh()->due_at->copy()->addHours(5));
        $returned = $this->rentals()->markReturned($booking);

        $this->assertGreaterThan(0, $returned->late_fee);
        $this->assertSame($paid, $returned->total_price);
        $this->assertSame($fee, $returned->platform_fee);
        $this->assertSame('paid', $returned->payment_status);
    }
}