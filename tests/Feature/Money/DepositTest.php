<?php

namespace Tests\Feature\Money;

use App\Models\Booking;
use App\Models\RoomStock;
use App\Services\BookingService;
use App\Services\DepositService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepositTest extends TestCase
{
    // ------------------------------------------------------ buku besar

    public function test_a_topup_adds_to_the_balance_and_writes_a_ledger_row(): void
    {
        $company = $this->makeCompany();
        $admin   = $this->makeUser('admin');

        $tx = app(DepositService::class)->credit($company, 1000000, 'topup', 'Transfer bank', 'TRX-1', $admin);

        $this->assertSame(1000000, $company->fresh()->deposit_balance);
        $this->assertSame('topup', $tx->type);
        $this->assertSame(1000000, $tx->amount);
        $this->assertSame(1000000, $tx->balance_after);
        $this->assertSame('TRX-1', $tx->reference);
        $this->assertSame($admin->id, $tx->created_by);
    }

    public function test_a_debit_lowers_the_balance_and_is_recorded_as_a_negative_amount(): void
    {
        $company = $this->makeCompany(deposit: 1000000);

        $tx = app(DepositService::class)->debit($company, 300000, 'Pemesanan');

        $this->assertSame(700000, $company->fresh()->deposit_balance);
        $this->assertSame('charge', $tx->type);
        $this->assertSame(-300000, $tx->amount);
        $this->assertSame(700000, $tx->balance_after);
    }

    public function test_the_ledger_always_adds_up_to_the_balance(): void
    {
        $company = $this->makeCompany();
        $service = app(DepositService::class);

        $service->credit($company, 1000000, 'topup');
        $service->debit($company, 300000, 'Pemesanan');
        $service->credit($company, 50000, 'refund');

        $rows = $company->transactions()->orderBy('id')->get();

        $this->assertSame([1000000, 700000, 750000], $rows->pluck('balance_after')->all());
        $this->assertSame([1000000, -300000, 50000], $rows->pluck('amount')->all());
        $this->assertSame(750000, $company->fresh()->deposit_balance);
        $this->assertSame(750000, (int) $rows->sum('amount'));
    }

    public function test_a_debit_above_the_balance_is_refused_and_changes_nothing(): void
    {
        $company = $this->makeCompany(deposit: 100000);

        try {
            app(DepositService::class)->debit($company, 100001);
            $this->fail('Pemotongan seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('deposit', $e->errors());
        }

        $this->assertSame(100000, $company->fresh()->deposit_balance);
        $this->assertSame(1, $company->transactions()->count());   // hanya baris saldo awal
    }

    public function test_the_whole_balance_can_be_spent_but_not_a_rupiah_more(): void
    {
        $company = $this->makeCompany(deposit: 100000);
        $service = app(DepositService::class);

        $service->debit($company, 100000);
        $this->assertSame(0, $company->fresh()->deposit_balance);

        $this->expectException(ValidationException::class);
        $service->debit($company, 1);
    }

    // ------------------------------------------- pemesanan dari saldo

    public function test_a_booking_within_the_limit_is_paid_from_the_deposit_at_once(): void
    {
        $company         = $this->makeCompany(deposit: 5000000);
        [$user, $member] = $this->makeEmployee($company);
        $room            = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000], quota: 2);

        $booking = $this->bookCorporate($user, $member, $room, from: 2, nights: 3);   // Rp1.500.000, limit Rp2.000.000

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('deposit', $booking->payment_method);
        $this->assertSame($company->id, $booking->company_id);
        $this->assertSame(2000000, $booking->policy_limit);
        $this->assertSame(3500000, $company->fresh()->deposit_balance);

        $tx = $company->transactions()->where('type', 'charge')->firstOrFail();
        $this->assertSame(-1500000, $tx->amount);
        $this->assertSame($booking->booking_code, $tx->reference);
        $this->assertSame('DEP-' . $tx->id, $booking->payment_reference);
    }

    public function test_insufficient_balance_rejects_the_booking_and_holds_no_rooms(): void
    {
        $company         = $this->makeCompany(deposit: 1000000);
        [$user, $member] = $this->makeEmployee($company);
        $room            = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000], quota: 1);

        try {
            $this->bookCorporate($user, $member, $room, from: 2, nights: 3);   // Rp1.500.000 > saldo
            $this->fail('Pemesanan seharusnya ditolak karena saldo kurang.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('deposit', $e->errors());
        }

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, (int) RoomStock::where('room_type_id', $room->id)->sum('booked'));
        $this->assertSame(1000000, $company->fresh()->deposit_balance);
        $this->assertSame(1, $company->transactions()->count());
    }

    public function test_cancelling_a_deposit_booking_returns_the_money_to_the_deposit(): void
    {
        $fake            = $this->fakeGateway();
        $company         = $this->makeCompany(deposit: 5000000);
        [$user, $member] = $this->makeEmployee($company);
        $room            = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000], quota: 1);
        $booking         = $this->bookCorporate($user, $member, $room, from: 10, nights: 2);   // Rp1.000.000

        $this->assertSame(4000000, $company->fresh()->deposit_balance);

        app(BookingService::class)->cancel($booking, 'customer');

        $booking->refresh();
        $refund = $company->transactions()->where('type', 'refund')->firstOrFail();

        $this->assertSame(5000000, $company->fresh()->deposit_balance);
        $this->assertSame(1000000, $refund->amount);
        $this->assertSame($booking->booking_code, $refund->reference);
        $this->assertSame('refunded', $booking->payment_status);
        $this->assertSame(1000000, $booking->refund_amount);
        $this->assertSame('DEP-RF-' . $refund->id, $booking->refund_reference);
        $this->assertSame(0, $fake->refunds);   // gateway tidak dilibatkan
    }

    public function test_deposit_bookings_cannot_go_through_the_card_payment_page(): void
    {
        $company         = $this->makeCompany(deposit: 5000000);
        [$user, $member] = $this->makeEmployee($company);
        $room            = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking         = $this->bookCorporate($user, $member, $room);

        $this->actingAs($user)
            ->post(route('bookings.pay.process', $booking), ['outcome' => 'success'])
            ->assertNotFound();
    }
}