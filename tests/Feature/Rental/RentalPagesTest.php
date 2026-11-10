<?php

namespace Tests\Feature\Rental;

use App\Models\Booking;
use App\Models\Partner;
use App\Services\RentalService;
use Tests\TestCase;

class RentalPagesTest extends TestCase
{
    /** @return array{0: Partner, 1: Booking} sewa terkonfirmasi mulai hari ini selama 1 hari */
    private function rental(): array
    {
        $partner = $this->makePartner();
        $vehicle = $this->makeVehicle($partner);

        return [$partner, $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1))];
    }

    public function test_the_page_lists_rentals_by_stage(): void
    {
        [$partner, $booking] = $this->rental();

        $this->actingAs($partner->user)
            ->get(route('partner.rentals.index'))
            ->assertOk()
            ->assertSee($booking->booking_code);

        app(RentalService::class)->markPickedUp($booking);
        $this->travelTo($this->day(1)->setTime(13, 0));

        $this->actingAs($partner->user)
            ->get(route('partner.rentals.index'))
            ->assertOk()
            ->assertSee($booking->booking_code);
    }

    public function test_only_partners_can_open_the_page(): void
    {
        $this->get(route('partner.rentals.index'))->assertRedirect(route('login'));
        $this->actingAs($this->makeCustomer())->get(route('partner.rentals.index'))->assertForbidden();
    }

    public function test_the_partner_records_the_pick_up(): void
    {
        [$partner, $booking] = $this->rental();

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.pickup', $booking))
            ->assertSessionHas('success');

        $this->assertNotNull($booking->fresh()->picked_up_at);
    }

    public function test_the_partner_records_the_return_with_the_system_fee(): void
    {
        [$partner, $booking] = $this->rental();
        app(RentalService::class)->markPickedUp($booking);
        $this->travelTo($booking->fresh()->due_at->copy()->addMinutes(150));

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.return', $booking))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertNotNull($booking->returned_at);
        $this->assertSame(135000, $booking->late_fee);
    }

    public function test_the_partner_can_override_the_fee_when_returning(): void
    {
        [$partner, $booking] = $this->rental();
        app(RentalService::class)->markPickedUp($booking);
        $this->travelTo($booking->fresh()->due_at->copy()->addMinutes(150));

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.return', $booking), ['late_fee' => 0]);

        $booking->refresh();
        $this->assertSame(135000, $booking->late_fee_computed);
        $this->assertSame(0, $booking->late_fee);
    }

    public function test_a_negative_fee_is_refused(): void
    {
        [$partner, $booking] = $this->rental();
        app(RentalService::class)->markPickedUp($booking);

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.return', $booking), ['late_fee' => -5000])
            ->assertSessionHasErrors('late_fee');

        $this->assertNull($booking->fresh()->returned_at);
    }

    public function test_partners_cannot_touch_the_rentals_of_others(): void
    {
        [, $booking] = $this->rental();
        $stranger    = $this->makePartner();

        $this->actingAs($stranger->user)->post(route('partner.rentals.pickup', $booking))->assertNotFound();
        $this->actingAs($stranger->user)->post(route('partner.rentals.return', $booking))->assertNotFound();

        $this->assertNull($booking->fresh()->picked_up_at);
    }

    public function test_hotel_bookings_are_not_part_of_the_rental_flow(): void
    {
        $partner = $this->makePartner();
        $room    = $this->makeRoomType($this->makeHotel($partner), quota: 2);
        $hotel   = $this->pay($this->bookRoom($this->makeCustomer(), $room, from: 0, nights: 1));

        $this->actingAs($partner->user)->post(route('partner.rentals.pickup', $hotel))->assertNotFound();
    }
}