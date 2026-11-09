<?php

namespace Tests\Feature\Corporate;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyPosition;
use App\Models\RoomStock;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\DepositService;
use Tests\TestCase;

class PolicyAndApprovalTest extends TestCase
{
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->makeCompany(deposit: 20000000, attributes: ['travel_policy_limit' => 2000000]);
    }

    private function room(int $price = 500000): RoomType
    {
        return $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => $price], quota: 10);
    }

    private function booked(RoomType $room, int $offset = 2): int
    {
        return (int) RoomStock::where('room_type_id', $room->id)
            ->whereDate('date', $this->day($offset))
            ->value('booked');
    }

    private function balance(): int
    {
        return $this->company->fresh()->deposit_balance;
    }

    /** @return array{0: \App\Models\User, 1: \App\Models\CompanyUser} */
    private function employee(?CompanyPosition $position = null, string $role = 'employee'): array
    {
        return $this->makeEmployee($this->company, $role, ['position_id' => $position?->id]);
    }

    // ------------------------------------------------------------ limit

    public function test_a_booking_within_the_position_limit_is_confirmed_and_paid_from_the_deposit(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 2000000));

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 3);   // Rp1.500.000

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame(18500000, $this->balance());
    }

    public function test_a_booking_exactly_at_the_limit_needs_no_approval(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 1000000));

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 2);   // Rp1.000.000

        $this->assertSame('confirmed', $booking->status);
    }

    public function test_one_rupiah_over_the_limit_waits_for_approval_and_holds_the_room(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 500000));
        $room            = $this->room(500001);

        $booking = $this->bookCorporate($user, $member, $room, from: 2, nights: 1);

        $this->assertSame('pending_approval', $booking->status);
        $this->assertSame('limit', $booking->approval_reason);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame(20000000, $this->balance());   // saldo belum dipotong
        $this->assertSame(1, $this->booked($room));      // kamar ditahan
        $this->assertTrue(
            $booking->expires_at->equalTo(now()->addHours((int) config('sekalago.approval_hold_hours')))
        );
    }

    public function test_an_employee_without_a_position_follows_the_company_default_limit(): void
    {
        [$user, $member] = $this->employee();

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 5);   // Rp2.500.000 > Rp2.000.000

        $this->assertSame('pending_approval', $booking->status);
        $this->assertSame('limit', $booking->approval_reason);
    }

    public function test_the_position_limit_overrides_the_company_default(): void
    {
        $this->company->forceFill(['travel_policy_limit' => 1000000])->save();
        [$user, $member] = $this->employee($this->makePosition($this->company, 3000000));

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 3);   // Rp1.500.000

        $this->assertSame('confirmed', $booking->status);
    }

    // --------------------------------------------------------- anggaran

    public function test_the_monthly_budget_is_checked_against_everything_spent_this_month(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 5000000, 2000000));
        $room            = $this->room();

        $first  = $this->bookCorporate($user, $member, $room, from: 2, nights: 2);   // Rp1.000.000
        $second = $this->bookCorporate($user, $member, $room, from: 5, nights: 2);   // total Rp2.000.000, tepat di anggaran
        $third  = $this->bookCorporate($user, $member, $room, from: 8, nights: 1);   // Rp2.500.000 > anggaran

        $this->assertSame('confirmed', $first->status);
        $this->assertSame('confirmed', $second->status);
        $this->assertSame('pending_approval', $third->status);
        $this->assertSame('budget', $third->approval_reason);
    }

    public function test_exceeding_both_limit_and_budget_is_recorded_as_such(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 500000, 700000));

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 2);   // Rp1.000.000

        $this->assertSame('pending_approval', $booking->status);
        $this->assertSame('limit_budget', $booking->approval_reason);
    }

    public function test_the_company_default_budget_applies_when_the_position_has_none(): void
    {
        $this->company->forceFill(['default_monthly_budget' => 1000000])->save();
        [$user, $member] = $this->employee();

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 3);   // Rp1.500.000, limit Rp2.000.000 aman

        $this->assertSame('pending_approval', $booking->status);
        $this->assertSame('budget', $booking->approval_reason);
    }

    public function test_pending_bookings_count_as_spending_but_rejected_ones_do_not(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 500000, 5000000));
        [$finance]       = $this->employee(null, 'admin_finance');

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 2);   // menunggu persetujuan

        $this->assertSame(1000000, $member->fresh()->monthlySpent());

        app(BookingService::class)->reject($booking, $finance, 'Terlalu mahal');

        $this->assertSame(0, $member->fresh()->monthlySpent());
    }

    public function test_spending_from_last_month_does_not_count(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 5000000, 5000000));

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 2);
        $this->assertSame(1000000, $member->fresh()->monthlySpent());

        Booking::whereKey($booking->id)->update(['created_at' => now()->subMonth()]);

        $this->assertSame(0, $member->fresh()->monthlySpent());
    }

    public function test_finance_is_exempt_from_limit_and_budget(): void
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 500000, 500000), 'admin_finance');

        $booking = $this->bookCorporate($user, $member, $this->room(), from: 2, nights: 3);   // Rp1.500.000

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(18500000, $this->balance());
    }

    // ------------------------------------------------------ persetujuan

    /** Pesanan Rp1.000.000 dari karyawan berlimit Rp500.000: menunggu persetujuan. */
    private function pendingBooking(?RoomType $room = null): array
    {
        [$user, $member] = $this->employee($this->makePosition($this->company, 500000));
        [$finance]       = $this->employee(null, 'admin_finance');
        $room          ??= $this->room();

        $booking = $this->bookCorporate($user, $member, $room, from: 2, nights: 2);

        return [$booking, $finance, $room];
    }

    public function test_finance_approves_and_the_deposit_is_charged(): void
    {
        [$booking, $finance] = $this->pendingBooking();

        $this->actingAs($finance)
            ->post(route('corporate.bookings.approve', $booking))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame($finance->id, $booking->decided_by);
        $this->assertNull($booking->expires_at);
        $this->assertStringStartsWith('DEP-', $booking->payment_reference);
        $this->assertSame(19000000, $this->balance());
    }

    public function test_approving_twice_charges_only_once(): void
    {
        [$booking, $finance] = $this->pendingBooking();

        $this->actingAs($finance)->post(route('corporate.bookings.approve', $booking));
        $this->actingAs($finance)
            ->post(route('corporate.bookings.approve', $booking))
            ->assertSessionHasErrors('approval');

        $this->assertSame(19000000, $this->balance());
        $this->assertSame(1, $this->company->transactions()->where('type', 'charge')->count());
    }

    public function test_approval_fails_when_the_deposit_is_too_low_and_the_booking_stays_pending(): void
    {
        [$booking, $finance, $room] = $this->pendingBooking();

        app(DepositService::class)->debit($this->company, 19500000, 'Kurangi saldo');   // sisa Rp500.000

        $this->actingAs($finance)
            ->post(route('corporate.bookings.approve', $booking))
            ->assertSessionHasErrors('deposit');

        $this->assertSame('pending_approval', $booking->fresh()->status);
        $this->assertSame(1, $this->booked($room));
        $this->assertSame(500000, $this->balance());
    }

    public function test_rejection_releases_the_room_and_keeps_the_note(): void
    {
        [$booking, $finance, $room] = $this->pendingBooking();

        $this->actingAs($finance)
            ->post(route('corporate.bookings.reject', $booking), ['decision_note' => 'Di luar kebijakan'])
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('rejected', $booking->status);
        $this->assertSame('Di luar kebijakan', $booking->decision_note);
        $this->assertSame($finance->id, $booking->decided_by);
        $this->assertSame(0, $this->booked($room));
        $this->assertSame(20000000, $this->balance());
    }

    public function test_rejection_requires_a_note(): void
    {
        [$booking, $finance] = $this->pendingBooking();

        $this->actingAs($finance)
            ->post(route('corporate.bookings.reject', $booking), ['decision_note' => ''])
            ->assertSessionHasErrors('decision_note');

        $this->assertSame('pending_approval', $booking->fresh()->status);
    }

    public function test_an_unanswered_request_expires_and_releases_the_room(): void
    {
        [$booking, , $room] = $this->pendingBooking();

        $this->travel(25)->hours();

        $this->assertSame(1, app(BookingService::class)->expireOverdue());
        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(0, $this->booked($room));
        $this->assertSame(20000000, $this->balance());
    }

    public function test_approving_after_the_hold_has_lapsed_is_refused(): void
    {
        [$booking, $finance, $room] = $this->pendingBooking();

        $this->travel(25)->hours();

        $this->actingAs($finance)
            ->post(route('corporate.bookings.approve', $booking))
            ->assertSessionHas('error');

        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(0, $this->booked($room));
        $this->assertSame(20000000, $this->balance());
    }

    // ------------------------------------------------------------- akses

    public function test_ordinary_employees_cannot_approve(): void
    {
        [$booking] = $this->pendingBooking();
        [$employee] = $this->employee();

        $this->actingAs($employee)
            ->post(route('corporate.bookings.approve', $booking))
            ->assertForbidden();

        $this->assertSame('pending_approval', $booking->fresh()->status);
    }

    public function test_finance_of_another_company_cannot_touch_the_request(): void
    {
        [$booking] = $this->pendingBooking();
        $other     = $this->makeCompany(deposit: 5000000);
        [$outsider] = $this->makeEmployee($other, 'admin_finance');

        $this->actingAs($outsider)
            ->post(route('corporate.bookings.approve', $booking))
            ->assertNotFound();

        $this->assertSame('pending_approval', $booking->fresh()->status);
    }

    public function test_customers_cannot_use_the_approval_pages(): void
    {
        [$booking] = $this->pendingBooking();

        $this->actingAs($this->makeCustomer())
            ->post(route('corporate.bookings.approve', $booking))
            ->assertForbidden();

        $this->assertSame('pending_approval', $booking->fresh()->status);
    }
}