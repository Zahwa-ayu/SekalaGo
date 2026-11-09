<?php

namespace Tests\Feature\Inventory;

use App\Models\Booking;
use App\Models\RoomStock;
use App\Models\RoomType;
use App\Services\BookingService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HotelStockTest extends TestCase
{
    private function room(int $quota = 2, array $attributes = []): RoomType
    {
        $hotel = $this->makeHotel($this->makePartner());

        return $this->makeRoomType($hotel, $attributes, $quota);
    }

    private function booked(RoomType $room, int $offset): int
    {
        return (int) RoomStock::where('room_type_id', $room->id)
            ->whereDate('date', $this->day($offset))
            ->value('booked');
    }

    public function test_booking_takes_one_room_for_each_night_of_the_stay_only(): void
    {
        $room = $this->room(quota: 2);

        $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 3);

        $this->assertSame(0, $this->booked($room, 1));
        $this->assertSame(1, $this->booked($room, 2));
        $this->assertSame(1, $this->booked($room, 3));
        $this->assertSame(1, $this->booked($room, 4));
        $this->assertSame(0, $this->booked($room, 5));   // malam check-out tidak terpakai
    }

    public function test_sold_out_room_cannot_be_booked(): void
    {
        $room = $this->room(quota: 1);

        $this->bookRoom($this->makeCustomer(), $room);

        $this->expectException(ValidationException::class);
        $this->bookRoom($this->makeCustomer(), $room);
    }

    public function test_the_last_room_is_taken_by_the_first_buyer_only(): void
    {
        $room = $this->room(quota: 1);

        $this->bookRoom($this->makeCustomer(), $room);

        try {
            $this->bookRoom($this->makeCustomer(), $room);
            $this->fail('Pesanan kedua seharusnya ditolak.');
        } catch (ValidationException) {
            // diharapkan
        }

        $this->assertSame(1, $this->booked($room, 2));
        $this->assertSame(1, Booking::count());
    }

    public function test_one_sold_out_night_blocks_the_whole_stay_and_changes_nothing(): void
    {
        $room = $this->room(quota: 2);
        RoomStock::where('room_type_id', $room->id)->whereDate('date', $this->day(3))->update(['booked' => 2]);

        try {
            $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 3);
            $this->fail('Pesanan seharusnya ditolak.');
        } catch (ValidationException) {
            // diharapkan
        }

        $this->assertSame(0, $this->booked($room, 2));
        $this->assertSame(2, $this->booked($room, 3));
        $this->assertSame(0, $this->booked($room, 4));
        $this->assertSame(0, Booking::count());
    }

    public function test_a_night_without_stock_row_counts_as_unavailable(): void
    {
        $room = $this->room(quota: 2);
        RoomStock::where('room_type_id', $room->id)->whereDate('date', $this->day(3))->delete();

        $this->expectException(ValidationException::class);
        $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 3);
    }

    public function test_guests_above_room_capacity_are_rejected(): void
    {
        $room = $this->room(quota: 2, attributes: ['capacity' => 2]);

        $this->expectException(ValidationException::class);
        $this->bookRoom($this->makeCustomer(), $room, guests: 3);
    }

    public function test_unpaid_booking_expires_after_the_hold_and_releases_its_rooms(): void
    {
        $room    = $this->room(quota: 1);
        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 2);

        $this->assertSame(1, $this->booked($room, 2));

        $this->travel(31)->minutes();
        $expired = app(BookingService::class)->expireOverdue();

        $this->assertSame(1, $expired);
        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(0, $this->booked($room, 2));
        $this->assertSame(0, $this->booked($room, 3));
    }

    public function test_booking_within_the_hold_keeps_its_rooms(): void
    {
        $room    = $this->room(quota: 1);
        $booking = $this->bookRoom($this->makeCustomer(), $room);

        $this->travel(10)->minutes();
        $expired = app(BookingService::class)->expireOverdue();

        $this->assertSame(0, $expired);
        $this->assertSame('pending_payment', $booking->fresh()->status);
        $this->assertSame(1, $this->booked($room, 2));
    }

    public function test_expire_command_releases_rooms(): void
    {
        $room    = $this->room(quota: 1);
        $booking = $this->bookRoom($this->makeCustomer(), $room);

        $this->travel(31)->minutes();
        $this->artisan('bookings:expire')->assertExitCode(0);

        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(0, $this->booked($room, 2));
    }

    public function test_cancelling_a_confirmed_booking_releases_its_rooms(): void
    {
        $room    = $this->room(quota: 1);
        $booking = $this->pay($this->bookRoom($this->makeCustomer(), $room));

        app(BookingService::class)->cancel($booking, 'customer');

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame(0, $this->booked($room, 2));
        $this->assertSame(0, $this->booked($room, 3));
    }

    public function test_cancelling_twice_never_releases_stock_twice(): void
    {
        $room    = $this->room(quota: 2);
        $service = app(BookingService::class);

        $first  = $this->bookRoom($this->makeCustomer(), $room);
        $second = $this->bookRoom($this->makeCustomer(), $room);

        $service->cancel($first, 'customer');
        $service->cancel($first->fresh(), 'customer');   // pembatalan kedua tidak berpengaruh

        $this->assertSame(1, $this->booked($room, 2));   // pesanan kedua masih memegang satu kamar
        $this->assertSame('pending_payment', $second->fresh()->status);
    }

    public function test_search_scope_counts_only_nights_that_still_have_rooms(): void
    {
        $room     = $this->room(quota: 1);
        $openNights = fn () => RoomType::withOpenNights($this->day(2), $this->day(5))
            ->find($room->id)
            ->open_nights;

        $this->assertEquals(3, $openNights());

        $this->bookRoom($this->makeCustomer(), $room, from: 3, nights: 1);

        $this->assertEquals(2, $openNights());
    }
}