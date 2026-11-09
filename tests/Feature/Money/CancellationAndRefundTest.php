<?php

namespace Tests\Feature\Money;

use App\Models\Booking;
use App\Models\RoomStock;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\CurrencyService;
use App\Services\VehicleAvailability;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CancellationAndRefundTest extends TestCase
{
    private function room(array $mou = []): RoomType
    {
        return $this->makeRoomType(
            $this->makeHotel($this->makePartner($mou)),
            ['base_price' => 500000],
            quota: 1
        );
    }

    /** Pesanan terkonfirmasi: check-in hari ke-10, dua malam (Rp1.000.000), batas gratis = hari ke-7. */
    private function confirmed(?RoomType $room = null): Booking
    {
        return $this->pay($this->bookRoom($this->makeCustomer(), $room ?? $this->room(), from: 10, nights: 2));
    }

    private function bookedOnDay(Booking $booking, int $offset): int
    {
        return (int) RoomStock::where('room_type_id', $booking->room_type_id)
            ->whereDate('date', $this->day($offset))
            ->value('booked');
    }

    public function test_an_unpaid_booking_is_cancelled_without_any_refund(): void
    {
        $booking = $this->bookRoom($this->makeCustomer(), $this->room(), from: 10, nights: 2);

        app(BookingService::class)->cancel($booking, 'customer', 'Berubah rencana');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('customer', $booking->cancelled_by);
        $this->assertSame('Berubah rencana', $booking->cancellation_reason);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame(0, $booking->refund_amount);
        $this->assertNull($booking->refunded_at);
        $this->assertSame(0, $this->bookedOnDay($booking, 10));
    }

    public function test_a_confirmed_booking_is_refunded_in_full_and_stops_counting_as_revenue(): void
    {
        $fake    = $this->fakeGateway();
        $booking = $this->confirmed();

        app(BookingService::class)->cancel($booking, 'customer');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('refunded', $booking->payment_status);
        $this->assertSame(1000000, $booking->refund_amount);
        $this->assertSame('FAKE-REFUND-1', $booking->refund_reference);
        $this->assertNotNull($booking->refunded_at);
        $this->assertSame(1, $fake->refunds);
        $this->assertSame(0, $this->bookedOnDay($booking, 10));
        $this->assertSame(0, (int) Booking::where('status', 'confirmed')->sum('platform_fee'));
    }

    public function test_the_refund_is_always_in_idr_whatever_the_display_currency(): void
    {
        app(CurrencyService::class)->use('USD');
        $booking = $this->confirmed();

        app(BookingService::class)->cancel($booking, 'customer');

        $this->assertSame(1000000, $booking->fresh()->refund_amount);
    }

    public function test_cancelling_twice_refunds_only_once(): void
    {
        $fake    = $this->fakeGateway();
        $booking = $this->confirmed();
        $service = app(BookingService::class);

        $service->cancel($booking, 'customer');
        $service->cancel($booking->fresh(), 'customer');

        $this->assertSame(1, $fake->refunds);
        $this->assertSame(1000000, $booking->fresh()->refund_amount);
    }

    public function test_a_failed_refund_cancels_nothing(): void
    {
        $fake                 = $this->fakeGateway();
        $fake->refundSucceeds = false;
        $booking              = $this->confirmed();

        try {
            app(BookingService::class)->cancel($booking, 'customer');
            $this->fail('Pembatalan seharusnya gagal karena refund gagal.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('refund', $e->errors());
        }

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame(0, $booking->refund_amount);
        $this->assertSame(1, $this->bookedOnDay($booking, 10));   // kamar tetap terpakai
    }

    public function test_expired_bookings_cannot_be_cancelled_into_something_else(): void
    {
        $booking = $this->bookRoom($this->makeCustomer(), $this->room(), from: 10, nights: 2);

        $this->travel(31)->minutes();
        app(BookingService::class)->expireOverdue();
        app(BookingService::class)->cancel($booking->fresh(), 'customer');

        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(0, $booking->fresh()->refund_amount);
    }

    public function test_free_cancellation_lasts_until_the_end_of_the_deadline_day(): void
    {
        $booking = $this->confirmed();   // batas gratis: hari ke-7 (check-in hari ke-10 dikurangi 3)

        // Tanggal dihitung sebelum waktu dimajukan, supaya tidak bergeser
        $endOfDeadline = $this->day(7)->setTime(23, 59);
        $nextMorning   = $this->day(8)->setTime(0, 1);

        $this->travelTo($endOfDeadline);
        $this->assertTrue($booking->fresh()->canBeCancelledByCustomer());

        $this->travelTo($nextMorning);
        $this->assertFalse($booking->fresh()->canBeCancelledByCustomer());
    }

    public function test_the_cancel_window_follows_the_mou_of_each_partner(): void
    {
        $booking = $this->confirmed($this->room(['free_cancel_days' => 7]));   // batas gratis: hari ke-3

        $lastFreeDay = $this->day(3)->setTime(12, 0);
        $tooLate     = $this->day(4)->setTime(12, 0);

        $this->travelTo($lastFreeDay);
        $this->assertTrue($booking->fresh()->canBeCancelledByCustomer());

        $this->travelTo($tooLate);
        $this->assertFalse($booking->fresh()->canBeCancelledByCustomer());
    }

    public function test_customer_cancels_through_the_page_and_gets_the_money_back(): void
    {
        $this->fakeGateway();
        $customer = $this->makeCustomer();
        $booking  = $this->pay($this->bookRoom($customer, $this->room(), from: 10, nights: 2));

        $this->actingAs($customer)
            ->post(route('bookings.cancel', $booking), ['cancellation_reason' => 'Sakit'])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame(1000000, $booking->refund_amount);
    }

    public function test_customer_cannot_cancel_after_the_free_window(): void
    {
        $fake     = $this->fakeGateway();
        $customer = $this->makeCustomer();
        $booking  = $this->pay($this->bookRoom($customer, $this->room(), from: 10, nights: 2));

        $this->travelTo($this->day(8)->setTime(9, 0));

        $this->actingAs($customer)
            ->post(route('bookings.cancel', $booking))
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(0, $fake->refunds);
    }

    public function test_other_customers_cannot_cancel_someone_elses_booking(): void
    {
        $booking = $this->confirmed();

        $this->actingAs($this->makeCustomer())
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_admin_can_cancel_after_the_free_window_with_a_full_refund(): void
    {
        $this->fakeGateway();
        $admin   = $this->makeUser('admin');
        $booking = $this->confirmed();

        $this->travelTo($this->day(9)->setTime(9, 0));   // lewat batas gratis, check-in belum mulai

        $this->actingAs($admin)
            ->post(route('admin.bookings.cancel', $booking), ['cancellation_reason' => 'Hotel tutup mendadak'])
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('admin', $booking->cancelled_by);
        $this->assertSame('Hotel tutup mendadak', $booking->cancellation_reason);
        $this->assertSame(1000000, $booking->refund_amount);
    }

    public function test_admin_must_give_a_reason(): void
    {
        $admin   = $this->makeUser('admin');
        $booking = $this->confirmed();

        $this->actingAs($admin)
            ->post(route('admin.bookings.cancel', $booking), ['cancellation_reason' => ''])
            ->assertSessionHasErrors('cancellation_reason');

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_admin_can_cancel_up_to_the_check_in_day_but_not_after(): void
    {
        $booking = $this->confirmed();   // check-in hari ke-10

        $checkInDay = $this->day(10)->setTime(12, 0);
        $nextDay    = $this->day(11)->setTime(0, 1);

        $this->travelTo($checkInDay);
        $this->assertTrue($booking->fresh()->canBeCancelledByAdmin());

        $this->travelTo($nextDay);
        $this->assertFalse($booking->fresh()->canBeCancelledByAdmin());
    }

    public function test_admin_cancellation_is_refused_once_the_stay_has_started(): void
    {
        $admin   = $this->makeUser('admin');
        $booking = $this->confirmed();

        $this->travelTo($this->day(11)->setTime(9, 0));

        $this->actingAs($admin)
            ->post(route('admin.bookings.cancel', $booking), ['cancellation_reason' => 'Terlambat'])
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_customers_cannot_use_the_admin_cancellation(): void
    {
        $customer = $this->makeCustomer();
        $booking  = $this->pay($this->bookRoom($customer, $this->room(), from: 10, nights: 2));

        $this->actingAs($customer)
            ->post(route('admin.bookings.cancel', $booking), ['cancellation_reason' => 'Coba-coba'])
            ->assertForbidden();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_cancelling_a_car_rental_refunds_it_and_frees_the_unit(): void
    {
        $fake      = $this->fakeGateway();
        $vehicle   = $this->makeVehicle($this->makePartner(), ['units' => 1, 'price_per_day' => 450000]);
        $booking   = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3));
        $freeUnits = fn () => app(VehicleAvailability::class)->freeUnits($vehicle, $this->day(10), $this->day(13));

        $this->assertSame(0, $freeUnits());

        app(BookingService::class)->cancel($booking, 'customer');

        $booking->refresh();
        $this->assertSame(1350000, $booking->refund_amount);   // 3 hari x Rp450.000, tanpa biaya supir
        $this->assertSame(1, $fake->refunds);
        $this->assertSame(1, $freeUnits());
    }
}