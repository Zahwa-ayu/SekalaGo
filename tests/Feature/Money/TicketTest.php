<?php

namespace Tests\Feature\Money;

use App\Services\BookingService;
use Tests\TestCase;

class TicketTest extends TestCase
{
    private function hotelBooking($customer)
    {
        $room = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);

        return $this->bookRoom($customer, $room, from: 10, nights: 2);
    }

    public function test_a_confirmed_hotel_booking_has_a_pdf_ticket(): void
    {
        $customer = $this->makeCustomer();
        $booking  = $this->pay($this->hotelBooking($customer));

        $this->actingAs($customer)
            ->get(route('bookings.ticket', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_confirmed_car_booking_has_a_pdf_ticket(): void
    {
        $customer = $this->makeCustomer();
        $vehicle  = $this->makeVehicle($this->makePartner());
        $booking  = $this->pay($this->bookCar($customer, $vehicle, from: 10, days: 2, withDriver: true));

        $this->actingAs($customer)
            ->get(route('bookings.ticket', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_an_unpaid_booking_has_no_ticket(): void
    {
        $customer = $this->makeCustomer();
        $booking  = $this->hotelBooking($customer);

        $this->actingAs($customer)->get(route('bookings.ticket', $booking))->assertNotFound();
    }

    public function test_a_cancelled_booking_has_no_ticket(): void
    {
        $customer = $this->makeCustomer();
        $booking  = $this->pay($this->hotelBooking($customer));

        app(BookingService::class)->cancel($booking, 'customer');

        $this->actingAs($customer)->get(route('bookings.ticket', $booking))->assertNotFound();
    }

    public function test_tickets_belong_to_their_owner_only(): void
    {
        $booking = $this->pay($this->hotelBooking($this->makeCustomer()));

        $this->actingAs($this->makeCustomer())
            ->get(route('bookings.ticket', $booking))
            ->assertForbidden();
    }
}