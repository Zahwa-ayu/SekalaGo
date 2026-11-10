<?php

namespace Tests\Feature\Rental;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\Vehicle;
use App\Services\RentalService;
use Tests\TestCase;

class ConflictTest extends TestCase
{
    /**
     * Mobil A (diambil hari ini, kembali besok 12.00) dan pesanan B pelanggan lain yang mulai besok.
     *
     * @return array{0: Partner, 1: Vehicle, 2: Booking, 3: Booking}
     */
    private function scenario(int $units = 1): array
    {
        $partner = $this->makePartner();
        $vehicle = $this->makeVehicle($partner, ['units' => $units]);

        $a = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1));
        app(RentalService::class)->markPickedUp($a);

        $b = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 1, days: 2));

        return [$partner, $vehicle, $a->fresh(), $b];
    }

    private function overdueMoment(): void
    {
        $this->travelTo($this->day(1)->setTime(13, 0));   // A sudah lewat batas kembali, B mulai hari ini
    }

    private function rentals(): RentalService
    {
        return app(RentalService::class);
    }

    public function test_nothing_is_at_risk_while_the_car_is_not_overdue(): void
    {
        [$partner, $vehicle] = $this->scenario();

        $this->assertTrue($this->rentals()->atRisk($vehicle)->isEmpty());
        $this->assertTrue($this->rentals()->conflicts($partner)->isEmpty());
    }

    public function test_the_next_booking_is_at_risk_once_the_only_unit_is_overdue(): void
    {
        [$partner, $vehicle, $a, $b] = $this->scenario();
        $this->overdueMoment();

        $this->assertSame([$b->id], $this->rentals()->atRisk($vehicle)->pluck('id')->all());
        $this->assertTrue($b->fresh()->isAtRisk());
        $this->assertFalse($a->fresh()->isAtRisk());

        $conflicts = $this->rentals()->conflicts($partner);
        $this->assertCount(1, $conflicts);
        $this->assertSame($vehicle->id, $conflicts->first()['vehicle']->id);
        $this->assertSame([$a->id], $conflicts->first()['overdue']->pluck('id')->all());
    }

    public function test_a_spare_unit_means_no_conflict(): void
    {
        [$partner, $vehicle, , $b] = $this->scenario(units: 2);
        $this->overdueMoment();

        $this->assertTrue($this->rentals()->atRisk($vehicle)->isEmpty());
        $this->assertFalse($b->fresh()->isAtRisk());
        $this->assertTrue($this->rentals()->conflicts($partner)->isEmpty());
    }

    public function test_the_conflict_disappears_once_the_car_is_returned(): void
    {
        [$partner, $vehicle, $a] = $this->scenario();
        $this->overdueMoment();

        $this->rentals()->markReturned($a);

        $this->assertTrue($this->rentals()->atRisk($vehicle)->isEmpty());
        $this->assertTrue($this->rentals()->conflicts($partner)->isEmpty());
    }

    public function test_the_most_recent_booking_is_the_one_at_risk_when_a_spare_unit_exists(): void
    {
        $partner = $this->makePartner();
        $vehicle = $this->makeVehicle($partner, ['units' => 2]);

        $a = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 1));
        app(RentalService::class)->markPickedUp($a);

        $first  = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 1, days: 1));
        $second = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 1, days: 1));

        $this->overdueMoment();

        $this->assertSame([$second->id], $this->rentals()->atRisk($vehicle)->pluck('id')->all());
    }

    // -------------------------------------------- pembatalan oleh mitra

    public function test_the_partner_cancels_an_at_risk_booking_and_the_customer_is_refunded_in_full(): void
    {
        $fake = $this->fakeGateway();
        [$partner, , $a, $b] = $this->scenario();
        $this->overdueMoment();

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.cancel', $b), ['cancellation_reason' => 'Unit sebelumnya belum kembali'])
            ->assertSessionHas('success');

        $b->refresh();
        $this->assertSame('cancelled', $b->status);
        $this->assertSame('partner', $b->cancelled_by);
        $this->assertSame('Unit sebelumnya belum kembali', $b->cancellation_reason);
        $this->assertSame($b->total_price, $b->refund_amount);
        $this->assertSame('refunded', $b->payment_status);
        $this->assertSame(1, $fake->refunds);

        $this->assertSame('confirmed', $a->fresh()->status);   // penyewa yang terlambat tidak terpengaruh
    }

    public function test_the_partner_cannot_cancel_a_booking_that_is_not_at_risk(): void
    {
        $fake = $this->fakeGateway();
        [$partner, , , $b] = $this->scenario();   // A belum terlambat

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.cancel', $b), ['cancellation_reason' => 'Ingin membatalkan'])
            ->assertSessionHasErrors('booking');

        $this->assertSame('confirmed', $b->fresh()->status);
        $this->assertSame(0, $fake->refunds);
    }

    public function test_the_overdue_renter_themself_cannot_be_cancelled_by_the_partner(): void
    {
        [$partner, , $a] = $this->scenario();
        $this->overdueMoment();

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.cancel', $a), ['cancellation_reason' => 'Coba'])
            ->assertSessionHasErrors('booking');

        $this->assertSame('confirmed', $a->fresh()->status);
    }

    public function test_the_cancellation_needs_a_reason(): void
    {
        [$partner, , , $b] = $this->scenario();
        $this->overdueMoment();

        $this->actingAs($partner->user)
            ->post(route('partner.rentals.cancel', $b), ['cancellation_reason' => ''])
            ->assertSessionHasErrors('cancellation_reason');

        $this->assertSame('confirmed', $b->fresh()->status);
    }

    public function test_another_partner_cannot_cancel_the_booking(): void
    {
        [, , , $b] = $this->scenario();
        $this->overdueMoment();

        $this->actingAs($this->makePartner()->user)
            ->post(route('partner.rentals.cancel', $b), ['cancellation_reason' => 'Coba'])
            ->assertNotFound();

        $this->assertSame('confirmed', $b->fresh()->status);
    }
}