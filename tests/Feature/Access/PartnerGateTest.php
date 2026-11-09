<?php

namespace Tests\Feature\Access;

use App\Models\Partner;
use App\Models\User;
use Tests\TestCase;

class PartnerGateTest extends TestCase
{
    private function partnerWith(array $state, bool $signed = true): Partner
    {
        $partner = $this->makePartner([], $signed);
        $partner->forceFill($state)->save();

        return $partner;
    }

    // ------------------------------------------------------- verifikasi

    public function test_a_pending_partner_is_logged_out_of_the_portal(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending']);

        $this->actingAs($partner->user)
            ->get(route('partner.bookings.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_a_rejected_partner_is_told_why(): void
    {
        $partner = $this->partnerWith([
            'verification_status' => 'rejected',
            'verification_note'   => 'Dokumen tidak lengkap',
        ]);

        $this->actingAs($partner->user)
            ->get(route('partner.bookings.index'))
            ->assertRedirect(route('login'));

        $this->assertStringContainsString('Dokumen tidak lengkap', (string) session('error'));
        $this->assertGuest();
    }

    public function test_public_partner_registration_creates_a_pending_partner_without_logging_in(): void
    {
        $this->post(route('register.partner.store'), [
            'company_name'          => 'Hotel Baru Jaya',
            'business_license_no'   => 'NIB-998877',
            'bank_account_no'       => '1234567890',
            'email'                 => 'baru@mitra.test',
            'phone_number'          => '08123456789',
            'password'              => 'RahasiaKu123',
            'password_confirmation' => 'RahasiaKu123',
        ]);

        $this->assertGuest();

        $user = User::where('email', 'baru@mitra.test')->firstOrFail();
        $this->assertSame('partner', $user->user_type);
        $this->assertSame('pending', $user->partner->verification_status);
    }

    // -------------------------------------------------------------- MOU

    public function test_a_verified_partner_without_a_signed_mou_is_sent_to_the_agreement_page(): void
    {
        $partner = $this->partnerWith([], signed: false);

        $this->actingAs($partner->user)
            ->get(route('partner.hotels.index'))
            ->assertRedirect(route('partner.agreement.show'));
    }

    public function test_the_agreement_page_stays_open_before_signing(): void
    {
        $partner = $this->partnerWith([], signed: false);

        $this->actingAs($partner->user)->get(route('partner.agreement.show'))->assertOk();
    }

    public function test_the_portal_opens_once_the_mou_is_signed(): void
    {
        $partner = $this->partnerWith([], signed: true);

        $this->actingAs($partner->user)->get(route('partner.hotels.index'))->assertOk();
        $this->actingAs($partner->user)->get(route('partner.vehicles.index'))->assertOk();
    }

    // ------------------------------------------------------ keputusan admin

    // public function test_admin_verifies_a_pending_partner(): void
    // {
    //     $partner = $this->partnerWith(['verification_status' => 'pending']);

    //     $this->actingAs($this->makeUser('admin'))
    //         ->post(route('admin.partners.verify', $partner));

    //     $this->assertSame('verified', $partner->fresh()->verification_status);
    // }

    public function test_admin_rejects_a_partner_with_a_note(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending']);

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.partners.reject', $partner), ['verification_note' => 'NIB tidak terbaca']);

        $partner->refresh();
        $this->assertSame('rejected', $partner->verification_status);
        $this->assertSame('NIB tidak terbaca', $partner->verification_note);
    }

    public function test_a_rejection_without_a_note_is_refused(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending']);

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.partners.reject', $partner), ['verification_note' => ''])
            ->assertSessionHasErrors('verification_note');

        $this->assertSame('pending', $partner->fresh()->verification_status);
    }

    // public function test_only_admins_can_verify_partners(): void
    // {
    //     $partner = $this->partnerWith(['verification_status' => 'pending']);

    //     $this->actingAs($this->makeCustomer())
    //         ->post(route('admin.partners.verify', $partner))
    //         ->assertForbidden();

    //     $this->actingAs($partner->user)
    //         ->post(route('admin.partners.verify', $partner))
    //         ->assertForbidden();

    //     $this->assertSame('pending', $partner->fresh()->verification_status);
    // }

        private function mouPayload(array $overrides = []): array
    {
        return array_merge([
            'commission_percent' => 15,
            'payout_schedule'    => 'monthly',
            'free_cancel_days'   => 3,
        ], $overrides);
    }

    public function test_admin_verifies_a_partner_by_sending_the_mou(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending'], signed: false);

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.partners.agreement', $partner), $this->mouPayload())
            ->assertSessionHasNoErrors();

        $partner->refresh();
        $agreement = $partner->agreements()->firstOrFail();

        $this->assertSame('verified', $partner->verification_status);
        $this->assertSame('sent', $agreement->status);
        $this->assertSame(1, $agreement->version);
        $this->assertEquals(0.15, $agreement->commission_rate);
        $this->assertSame('monthly', $agreement->payout_schedule);
        $this->assertSame(3, $agreement->free_cancel_days);
    }

    public function test_the_commission_must_stay_between_10_and_25_percent(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending'], signed: false);
        $admin   = $this->makeUser('admin');

        foreach ([9, 26] as $percent) {
            $this->actingAs($admin)
                ->post(route('admin.partners.agreement', $partner), $this->mouPayload(['commission_percent' => $percent]))
                ->assertSessionHasErrors('commission_percent');
        }

        $this->assertSame(0, $partner->agreements()->count());
        $this->assertSame('pending', $partner->fresh()->verification_status);
    }

    public function test_a_new_mou_replaces_the_one_that_was_not_signed_yet(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending'], signed: false);
        $admin   = $this->makeUser('admin');

        $this->actingAs($admin)->post(route('admin.partners.agreement', $partner), $this->mouPayload());
        $this->actingAs($admin)->post(route('admin.partners.agreement', $partner), $this->mouPayload(['commission_percent' => 20]));

        $versions = $partner->agreements()->orderBy('version')->get();

        $this->assertSame(['void', 'sent'], $versions->pluck('status')->all());
        $this->assertSame([1, 2], $versions->pluck('version')->all());
        $this->assertEquals(0.20, $versions->last()->commission_rate);
    }

    public function test_a_signed_mou_cannot_be_replaced(): void
    {
        $partner = $this->partnerWith([], signed: true);   // MOU sudah ditandatangani (komisi 15%)

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.partners.agreement', $partner), $this->mouPayload(['commission_percent' => 25]))
            ->assertSessionHasErrors('agreement');

        $this->assertSame(1, $partner->agreements()->count());
        $this->assertEquals(0.15, $partner->commissionRate());
    }

    public function test_only_admins_can_send_the_mou(): void
    {
        $partner = $this->partnerWith(['verification_status' => 'pending'], signed: false);

        $this->actingAs($this->makeCustomer())
            ->post(route('admin.partners.agreement', $partner), $this->mouPayload())
            ->assertForbidden();

        $this->actingAs($partner->user)
            ->post(route('admin.partners.agreement', $partner), $this->mouPayload())
            ->assertForbidden();

        $this->assertSame('pending', $partner->fresh()->verification_status);
        $this->assertSame(0, $partner->agreements()->count());
    }
}