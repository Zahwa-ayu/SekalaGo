<?php

namespace Tests\Feature\Rental;

use App\Models\Booking;
use App\Models\CompanyUser;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBlackout;
use App\Services\ExtensionService;
use App\Services\RentalService;
use App\Services\VehicleAvailability;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    private function service(): ExtensionService
    {
        return app(ExtensionService::class);
    }

    /** @return array{0: Vehicle, 1: Booking} sewa 2 hari mulai hari ini, sudah diambil (kembali lusa 12.00) */
    private function ongoing(array $vehicle = [], int $days = 2, array $mou = []): array
    {
        $vehicle = $this->makeVehicle($this->makePartner($mou), array_merge(['price_per_day' => 450000], $vehicle));
        $booking = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: $days));
        app(RentalService::class)->markPickedUp($booking);

        return [$vehicle, $booking->fresh()];
    }

    private function assertRefused(callable $action, string $key): void
    {
        try {
            $action();
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($key, $e->errors());
        }
    }

    // ------------------------------------------------------------ hitungan

    public function test_the_quote_shows_the_price_the_new_dates_and_availability(): void
    {
        [, $booking] = $this->ongoing();

        $quote = $this->service()->quote($booking, 3);

        $this->assertSame(1350000, $quote['amount']);
        $this->assertSame($booking->check_out->copy()->addDays(3)->toDateString(), $quote['new_check_out']->toDateString());
        $this->assertSame($booking->due_at->copy()->addDays(3)->toDateTimeString(), $quote['new_due_at']->toDateTimeString());
        $this->assertTrue($quote['available']);
    }

    public function test_a_gateway_extension_updates_the_booking_and_records_the_charge(): void
    {
        $fake = $this->fakeGateway();
        [, $booking] = $this->ongoing();   // Rp900.000, komisi 15%

        $extension = $this->service()->extend($booking, 2, $booking->user);

        $booking->refresh();
        $this->assertSame($this->day(4)->toDateString(), $booking->check_out->toDateString());
        $this->assertSame($this->day(4)->setTime(12, 0)->toDateTimeString(), $booking->due_at->toDateTimeString());
        $this->assertSame(4, $booking->nights);
        $this->assertSame(1800000, $booking->total_price);
        $this->assertSame(270000, $booking->platform_fee);
        $this->assertSame(1530000, $booking->vendor_revenue);
        $this->assertSame('confirmed', $booking->status);

        $this->assertSame(2, $extension->days);
        $this->assertSame(900000, $extension->amount);
        $this->assertSame(135000, $extension->platform_fee);
        $this->assertSame(765000, $extension->vendor_revenue);
        $this->assertSame('gateway', $extension->payment_method);
        $this->assertSame('FAKE-CHARGE-1', $extension->payment_reference);

        $this->assertSame(1, $fake->charges);
        $this->assertSame(900000, $fake->lastChargeAmount);   // hanya selisih, bukan seluruh pesanan
    }

    public function test_the_commission_follows_the_rate_of_the_original_booking(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing(mou: ['commission_rate' => 0.20]);

        $extension = $this->service()->extend($booking, 1, $booking->user);

        $this->assertSame(90000, $extension->platform_fee);       // 20% dari Rp450.000
        $this->assertSame(360000, $extension->vendor_revenue);
    }

    public function test_the_extended_dates_hold_the_unit_for_everyone_else(): void
    {
        $this->fakeGateway();
        [$vehicle, $booking] = $this->ongoing(['units' => 1]);

        $this->service()->extend($booking, 2, $booking->user);

        $this->assertSame(0, app(VehicleAvailability::class)->freeUnits($vehicle, $this->day(2), $this->day(4)));
    }

    public function test_returning_at_the_old_deadline_is_not_late_after_an_extension(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing();
        $oldDue = $booking->due_at->copy();

        $this->service()->extend($booking, 1, $booking->user);

        $this->travelTo($oldDue->copy()->addHours(2));
        $this->assertSame('ongoing', $booking->fresh()->rentalStage());

        $returned = app(RentalService::class)->markReturned($booking);
        $this->assertSame(0, $returned->late_minutes);
        $this->assertSame(0, $returned->late_fee);
    }

    // --------------------------------------------------------- pembayaran

    public function test_a_failed_charge_changes_nothing(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing();

        $this->assertRefused(fn () => $this->service()->extend($booking, 2, $booking->user, null, 'fail'), 'payment');

        $booking->refresh();
        $this->assertSame(900000, $booking->total_price);
        $this->assertSame($this->day(2)->toDateString(), $booking->check_out->toDateString());
        $this->assertSame(0, $booking->extensions()->count());
    }

    // --------------------------------------------------------------- aturan

    public function test_only_a_car_that_is_out_with_the_renter_and_not_yet_late_can_be_extended(): void
    {
        $this->fakeGateway();
        $partner = $this->makePartner();
        $vehicle = $this->makeVehicle($partner, ['units' => 5]);
        $room    = $this->makeRoomType($this->makeHotel($partner), quota: 2);

        $upcoming = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 2));
        $unpaid   = $this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 2);
        $hotel    = $this->pay($this->bookRoom($this->makeCustomer(), $room, from: 0, nights: 2));

        $returned = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 2));
        app(RentalService::class)->markPickedUp($returned);
        app(RentalService::class)->markReturned($returned);

        $late = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 2));
        app(RentalService::class)->markPickedUp($late);

        $this->travelTo($this->day(2)->setTime(13, 0));   // $late sudah lewat batas kembali

        foreach ([$upcoming, $unpaid, $hotel, $returned, $late] as $booking) {
            $this->assertRefused(
                fn () => $this->service()->extend($booking->fresh(), 1, $booking->user),
                'extension'
            );
        }
    }

    public function test_the_number_of_days_per_request_is_limited(): void
    {
        [, $booking] = $this->ongoing();

        $this->assertRefused(fn () => $this->service()->extend($booking, 0, $booking->user), 'days');
        $this->assertRefused(fn () => $this->service()->extend($booking, 8, $booking->user), 'days');
    }

    public function test_the_total_rental_cannot_pass_thirty_days(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing(days: 28);

        $this->assertRefused(fn () => $this->service()->extend($booking, 5, $booking->user), 'extension');
        $this->assertSame(28, $booking->fresh()->nights);
    }

    // --------------------------------------------------------- ketersediaan

    public function test_the_extension_is_refused_when_someone_else_has_the_car_next(): void
    {
        $this->fakeGateway();
        [$vehicle, $booking] = $this->ongoing(['units' => 1]);
        $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 2, days: 2));   // pesanan lain mulai lusa

        $this->assertRefused(fn () => $this->service()->extend($booking, 1, $booking->user), 'extension');

        $this->assertSame(0, $booking->fresh()->extensions()->count());
        $this->assertSame(900000, $booking->fresh()->total_price);
    }

    public function test_a_spare_unit_allows_the_extension(): void
    {
        $this->fakeGateway();
        [$vehicle, $booking] = $this->ongoing(['units' => 2]);
        $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 2, days: 2));

        $extension = $this->service()->extend($booking, 1, $booking->user);

        $this->assertSame(450000, $extension->amount);
    }

    public function test_an_extension_ending_right_before_the_next_booking_is_allowed(): void
    {
        $this->fakeGateway();
        [$vehicle, $booking] = $this->ongoing(['units' => 1]);
        $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 3, days: 2));   // mulai hari ke-3

        $extension = $this->service()->extend($booking, 1, $booking->user);   // menambah hari ke-2 saja

        $this->assertSame($this->day(3)->toDateString(), $extension->new_check_out->toDateString());
    }

    public function test_blackout_dates_block_the_extension(): void
    {
        $this->fakeGateway();
        [$vehicle, $booking] = $this->ongoing(['units' => 1]);

        VehicleBlackout::create([
            'vehicle_id' => $vehicle->id,
            'start_date' => $this->day(2),
            'end_date'   => $this->day(2),
        ]);

        $this->assertRefused(fn () => $this->service()->extend($booking, 1, $booking->user), 'extension');
    }

    // public function test_the_booking_itself_never_counts_as_a_conflict(): void
    // {
    //     $this->fakeGateway();
    //     [, $booking] = $this->ongoing(['units' => 1], days: 1);   // kembali besok 12.00

    //     $this->travelTo($this->day(1)->setTime(9, 0));            // hari kembali, sebelum jam 12.00

    //     $extension = $this->service()->extend($booking->fresh(), 1, $booking->user);

    //     $this->assertSame($this->day(2)->toDateString(), $extension->new_check_out->toDateString());
    // }

        public function test_the_booking_itself_never_counts_as_a_conflict(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing(['units' => 1], days: 1);   // kembali besok 12.00

        $expectedReturn = $this->day(2)->toDateString();          // dihitung sebelum waktu dimajukan
        $this->travelTo($this->day(1)->setTime(9, 0));            // hari kembali, sebelum jam 12.00

        $extension = $this->service()->extend($booking->fresh(), 1, $booking->user);

        $this->assertSame($expectedReturn, $extension->new_check_out->toDateString());
    }

    // ------------------------------------------------------------ corporate

    /** @return array{0: \App\Models\Company, 1: User, 2: CompanyUser, 3: Booking} */
    private function corporateOngoing(int $deposit = 5000000, ?int $limit = null, ?int $budget = null, string $role = 'employee'): array
    {
        $company  = $this->makeCompany(deposit: $deposit, attributes: ['travel_policy_limit' => 2000000]);
        $position = $limit ? $this->makePosition($company, $limit, $budget) : null;

        [$user, $member] = $this->makeEmployee($company, $role, ['position_id' => $position?->id]);

        $vehicle = $this->makeVehicle($this->makePartner(), ['units' => 2, 'price_per_day' => 450000]);
        $booking = $this->bookCorporateCar($user, $member, $vehicle, from: 0, days: 2);   // Rp900.000
        app(RentalService::class)->markPickedUp($booking);

        return [$company, $user, $member, $booking->fresh()];
    }

    public function test_a_corporate_extension_is_charged_to_the_deposit(): void
    {
        [$company, $user, $member, $booking] = $this->corporateOngoing();

        $extension = $this->service()->extend($booking, 1, $user, $member);

        $this->assertSame('deposit', $extension->payment_method);
        $this->assertStringStartsWith('DEP-', $extension->payment_reference);
        $this->assertSame(5000000 - 900000 - 450000, $company->fresh()->deposit_balance);

        $charge = $company->transactions()->where('type', 'charge')->latest('id')->firstOrFail();
        $this->assertSame(-450000, $charge->amount);
        $this->assertSame($booking->booking_code, $charge->reference);
        $this->assertSame(1350000, $booking->fresh()->total_price);
    }

    public function test_an_employee_cannot_extend_beyond_the_monthly_budget(): void
    {
        [$company, $user, $member, $booking] = $this->corporateOngoing(limit: 1000000, budget: 1000000);

        $this->assertRefused(fn () => $this->service()->extend($booking, 1, $user, $member), 'extension');

        $this->assertSame(5000000 - 900000, $company->fresh()->deposit_balance);
        $this->assertSame(900000, $booking->fresh()->total_price);
    }

    public function test_finance_is_not_held_back_by_limit_and_budget(): void
    {
        [$company, $user, $member, $booking] = $this->corporateOngoing(limit: 1000000, budget: 1000000, role: 'admin_finance');

        $extension = $this->service()->extend($booking, 1, $user, $member);

        $this->assertSame('deposit', $extension->payment_method);
        $this->assertSame(5000000 - 900000 - 450000, $company->fresh()->deposit_balance);
    }

    public function test_an_insufficient_deposit_refuses_the_extension_and_changes_nothing(): void
    {
        [$company, $user, $member, $booking] = $this->corporateOngoing(deposit: 900000);

        $this->assertRefused(fn () => $this->service()->extend($booking, 1, $user, $member), 'deposit');

        $this->assertSame(0, $company->fresh()->deposit_balance);
        $this->assertSame(900000, $booking->fresh()->total_price);
        $this->assertSame(0, $booking->fresh()->extensions()->count());
    }

    public function test_someone_from_another_company_cannot_extend_the_booking(): void
    {
        [, , , $booking] = $this->corporateOngoing();

        $other = $this->makeCompany(deposit: 5000000);
        [$stranger, $strangerMember] = $this->makeEmployee($other);

        $this->assertRefused(fn () => $this->service()->extend($booking, 1, $stranger, $strangerMember), 'extension');
    }

    // ----------------------------------------------------------------- web

    public function test_the_owner_opens_the_extension_page(): void
    {
        [, $booking] = $this->ongoing();

        $this->actingAs($booking->user)->get(route('bookings.extend', $booking))->assertOk();
        $this->actingAs($booking->user)->get(route('bookings.extend', ['booking' => $booking, 'days' => 3]))->assertOk();
    }

    public function test_the_booking_page_offers_the_extension_only_while_it_is_possible(): void
    {
        [, $booking] = $this->ongoing();
        $link = route('bookings.extend', $booking);

        $this->actingAs($booking->user)->get(route('bookings.show', $booking))->assertSee($link, false);

        app(RentalService::class)->markReturned($booking);

        $this->actingAs($booking->user)->get(route('bookings.show', $booking))->assertDontSee($link, false);
    }

    public function test_someone_elses_booking_cannot_be_extended(): void
    {
        [, $booking] = $this->ongoing();

        $this->actingAs($this->makeCustomer())->get(route('bookings.extend', $booking))->assertForbidden();
        $this->actingAs($this->makeCustomer())
            ->post(route('bookings.extend.store', $booking), ['days' => 1, 'outcome' => 'success'])
            ->assertForbidden();
    }

    public function test_the_page_sends_you_back_when_the_rental_cannot_be_extended(): void
    {
        $vehicle = $this->makeVehicle($this->makePartner());
        $booking = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 0, days: 2));   // belum diambil

        $this->actingAs($booking->user)
            ->get(route('bookings.extend', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHasErrors('extension');
    }

    public function test_extending_through_the_page_pays_and_updates_the_booking(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing();

        $this->actingAs($booking->user)
            ->post(route('bookings.extend.store', $booking), ['days' => 2, 'outcome' => 'success'])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success');

        $this->assertSame(1800000, $booking->fresh()->total_price);
        $this->assertSame(1, $booking->fresh()->extensions()->count());
    }

    public function test_a_simulated_payment_failure_through_the_page_changes_nothing(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing();

        $this->actingAs($booking->user)
            ->post(route('bookings.extend.store', $booking), ['days' => 2, 'outcome' => 'fail'])
            ->assertSessionHasErrors('payment');

        $this->assertSame(900000, $booking->fresh()->total_price);
    }

    public function test_invalid_day_counts_are_refused_by_the_form(): void
    {
        [, $booking] = $this->ongoing();

        foreach ([0, 8, 'abc'] as $days) {
            $this->actingAs($booking->user)
                ->post(route('bookings.extend.store', $booking), ['days' => $days, 'outcome' => 'success'])
                ->assertSessionHasErrors('days');
        }
    }

    public function test_hotel_bookings_have_no_extension_page(): void
    {
        $customer = $this->makeCustomer();
        $room     = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 2);
        $booking  = $this->pay($this->bookRoom($customer, $room, from: 0, nights: 2));

        $this->actingAs($customer)->get(route('bookings.extend', $booking))->assertNotFound();
    }

    public function test_guests_must_log_in(): void
    {
        [, $booking] = $this->ongoing();

        $this->get(route('bookings.extend', $booking))->assertRedirect(route('login'));
    }

    public function test_the_ticket_of_an_extended_and_late_returned_rental_still_downloads(): void
    {
        $this->fakeGateway();
        [, $booking] = $this->ongoing();

        $this->service()->extend($booking, 1, $booking->user);
        $this->travelTo($booking->fresh()->due_at->copy()->addHours(3));
        app(RentalService::class)->markReturned($booking);

        $this->actingAs($booking->user)
            ->get(route('bookings.ticket', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}