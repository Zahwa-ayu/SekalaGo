<?php

namespace Tests\Feature\Money;

use App\Models\Booking;
use App\Models\RoomStock;
use App\Services\BookingService;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private function booking(): Booking
    {
        $room = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);

        return $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 2);
    }

    private function bookedOnDay(Booking $booking, int $offset): int
    {
        return (int) RoomStock::where('room_type_id', $booking->room_type_id)
            ->whereDate('date', $this->day($offset))
            ->value('booked');
    }

    public function test_a_new_booking_waits_for_payment_with_a_hold_deadline(): void
    {
        $booking = $this->booking();

        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertTrue(
            $booking->expires_at->equalTo(now()->addMinutes((int) config('sekalago.booking_hold_minutes')))
        );
    }

    public function test_successful_payment_confirms_the_booking(): void
    {
        $booking = $this->pay($this->booking(), 'SBX-AAA');

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('SBX-AAA', $booking->payment_reference);
        $this->assertNotNull($booking->paid_at);
    }

    public function test_paying_twice_keeps_the_first_payment(): void
    {
        $booking = $this->pay($this->booking(), 'SBX-FIRST');
        $paidAt  = $booking->paid_at;

        $this->travel(5)->minutes();
        $booking = $this->pay($booking, 'SBX-SECOND');

        $this->assertSame('SBX-FIRST', $booking->payment_reference);
        $this->assertTrue($booking->paid_at->equalTo($paidAt));
    }

    public function test_a_failed_charge_keeps_the_booking_and_its_rooms(): void
    {
        $booking = $this->booking();

        app(BookingService::class)->markFailed($booking);

        $booking->refresh();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('failed', $booking->payment_status);
        $this->assertSame(1, $this->bookedOnDay($booking, 2));
    }

    public function test_payment_can_be_retried_after_a_failure(): void
    {
        $booking = $this->booking();

        app(BookingService::class)->markFailed($booking);
        $booking = $this->pay($booking->fresh());

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
    }

    public function test_customer_pays_through_the_sandbox_gateway(): void
    {
        $customer = $this->makeCustomer();
        $room     = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking  = $this->bookRoom($customer, $room);

        $this->actingAs($customer)
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_a_simulated_failure_leaves_the_booking_unpaid(): void
    {
        $customer = $this->makeCustomer();
        $room     = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking  = $this->bookRoom($customer, $room);

        $this->actingAs($customer)
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'fail'])
            ->assertSessionHas('error');

        $booking->refresh();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('failed', $booking->payment_status);
    }

    public function test_payment_after_the_deadline_is_refused_and_releases_the_rooms(): void
    {
        $customer = $this->makeCustomer();
        $room     = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking  = $this->bookRoom($customer, $room);

        $this->travel(31)->minutes();

        $this->actingAs($customer)
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('error');

        $booking->refresh();
        $this->assertSame('expired', $booking->status);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame(0, $this->bookedOnDay($booking, 2));
    }

    public function test_a_confirmed_booking_cannot_be_charged_again(): void
    {
        $customer = $this->makeCustomer();
        $room     = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking  = $this->pay($this->bookRoom($customer, $room), 'SBX-ONCE');

        $this->actingAs($customer)
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertSame('SBX-ONCE', $booking->fresh()->payment_reference);
    }

    public function test_other_customers_cannot_pay_someone_elses_booking(): void
    {
        $room    = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking = $this->bookRoom($this->makeCustomer(), $room);

        $this->actingAs($this->makeCustomer())
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertForbidden();

        $this->assertSame('pending_payment', $booking->fresh()->status);
    }

    public function test_guests_must_log_in_to_pay(): void
    {
        $room    = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking = $this->bookRoom($this->makeCustomer(), $room);

        $this->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertRedirect(route('login'));
    }

    public function test_car_bookings_use_the_same_payment_flow(): void
    {
        $customer = $this->makeCustomer();
        $vehicle  = $this->makeVehicle($this->makePartner());
        $booking  = $this->bookCar($customer, $vehicle);

        $this->actingAs($customer)
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertRedirect(route('bookings.show', $booking));

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('car', $booking->product_type);
    }
}