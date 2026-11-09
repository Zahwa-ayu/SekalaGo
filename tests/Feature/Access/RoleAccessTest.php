<?php

namespace Tests\Feature\Access;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    // ------------------------------------------------------------ tamu

    public static function guestRoutes(): array
    {
        return [
            'pesanan pelanggan' => ['bookings.index'],
            'pesanan admin'     => ['admin.bookings.index'],
            'pesanan mitra'     => ['partner.bookings.index'],
            'portal corporate'  => ['corporate.dashboard'],
            'ganti password'    => ['account.password.edit'],
        ];
    }

    #[DataProvider('guestRoutes')]
    public function test_guests_are_sent_to_the_login_page(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
    }

    // ----------------------------------------------------------- peran

    public static function forbiddenCombinations(): array
    {
        return [
            'pelanggan ke admin'          => ['customer', 'admin.bookings.index'],
            'pelanggan ke mitra'          => ['customer', 'partner.bookings.index'],
            'pelanggan ke corporate'      => ['customer', 'corporate.dashboard'],
            'mitra ke admin'              => ['partner', 'admin.bookings.index'],
            'mitra ke corporate'          => ['partner', 'corporate.dashboard'],
            'mitra ke pesanan pelanggan'  => ['partner', 'bookings.index'],
            'admin ke mitra'              => ['admin', 'partner.bookings.index'],
            'admin ke corporate'          => ['admin', 'corporate.dashboard'],
            'admin ke pesanan pelanggan'  => ['admin', 'bookings.index'],
            'corporate ke admin'          => ['corporate', 'admin.bookings.index'],
            'corporate ke mitra'          => ['corporate', 'partner.bookings.index'],
        ];
    }

    #[DataProvider('forbiddenCombinations')]
    public function test_each_role_is_kept_out_of_the_other_areas(string $type, string $route): void
    {
        $this->actingAs($this->userOfType($type))->get(route($route))->assertForbidden();
    }

    public static function ownAreas(): array
    {
        return [
            'pelanggan' => ['customer', 'bookings.index'],
            'admin'     => ['admin', 'admin.bookings.index'],
            'mitra'     => ['partner', 'partner.bookings.index'],
            'corporate' => ['corporate', 'corporate.dashboard'],
        ];
    }

    #[DataProvider('ownAreas')]
    public function test_each_role_reaches_its_own_area(string $type, string $route): void
    {
        $this->actingAs($this->userOfType($type))->get(route($route))->assertOk();
    }

    public function test_only_finance_reaches_the_finance_pages(): void
    {
        $company    = $this->makeCompany();
        [$employee] = $this->makeEmployee($company);
        [$finance]  = $this->makeEmployee($company, 'admin_finance');

        foreach (['corporate.bookings.index', 'corporate.employees.index', 'corporate.reports.index'] as $route) {
            $this->actingAs($employee)->get(route($route))->assertForbidden();
            $this->actingAs($finance)->get(route($route))->assertOk();
        }
    }

    // ---------------------------------------------------------- pesanan

    public function test_customers_open_only_their_own_booking(): void
    {
        $room    = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 3);
        $owner   = $this->makeCustomer();
        $booking = $this->bookRoom($owner, $room);

        $this->actingAs($owner)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs($this->makeCustomer())->get(route('bookings.show', $booking))->assertForbidden();
    }

    public function test_colleagues_in_the_same_company_cannot_open_each_others_bookings(): void
    {
        $company          = $this->makeCompany(deposit: 5000000);
        [$owner, $member] = $this->makeEmployee($company);
        [$colleague]      = $this->makeEmployee($company);
        $room             = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 3);

        $booking = $this->bookCorporate($owner, $member, $room);

        $this->actingAs($owner)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs($colleague)->get(route('bookings.show', $booking))->assertForbidden();
    }

    public function test_booking_pages_render_for_hotels_and_cars(): void
    {
        $customer = $this->makeCustomer();
        $partner  = $this->makePartner();
        $room     = $this->makeRoomType($this->makeHotel($partner), quota: 2);
        $vehicle  = $this->makeVehicle($partner, ['units' => 2]);

        $hotelBooking = $this->bookRoom($customer, $room, from: 5, nights: 2);
        $carBooking   = $this->bookCar($customer, $vehicle, from: 5, days: 3, withDriver: true);

        $this->actingAs($customer);

        // Belum dibayar: halaman bayar dan halaman pesanan
        foreach ([$hotelBooking, $carBooking] as $booking) {
            $this->get(route('bookings.pay', $booking))->assertOk();
            $this->get(route('bookings.show', $booking))->assertOk()->assertSee($booking->booking_code);
        }

        // Sudah dibayar
        foreach ([$hotelBooking, $carBooking] as $booking) {
            $this->pay($booking);
            $this->get(route('bookings.show', $booking))->assertOk()->assertSee($booking->booking_code);
        }
    }

    public function test_partners_see_only_their_own_bookings_while_admin_sees_all(): void
    {
        $partnerA = $this->makePartner();
        $partnerB = $this->makePartner();
        $roomA    = $this->makeRoomType($this->makeHotel($partnerA), quota: 2);
        $roomB    = $this->makeRoomType($this->makeHotel($partnerB), quota: 2);

        $bookingA = $this->pay($this->bookRoom($this->makeCustomer(), $roomA));
        $bookingB = $this->pay($this->bookRoom($this->makeCustomer(), $roomB));

        $this->actingAs($partnerA->user)
            ->get(route('partner.bookings.index'))
            ->assertOk()
            ->assertSee($bookingA->booking_code)
            ->assertDontSee($bookingB->booking_code);

        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee($bookingA->booking_code)
            ->assertSee($bookingB->booking_code);
    }

    public function test_partners_cannot_open_the_hotels_and_vehicles_of_other_partners(): void
    {
        $partnerA = $this->makePartner();
        $partnerB = $this->makePartner();
        $hotelB   = $this->makeHotel($partnerB);
        $vehicleB = $this->makeVehicle($partnerB);

        $this->actingAs($partnerA->user);

        foreach ([
            route('partner.hotels.edit', $hotelB),
            route('partner.vehicles.edit', $vehicleB),
            route('partner.vehicles.blackouts.index', $vehicleB),
        ] as $url) {
            $this->assertContains($this->get($url)->getStatusCode(), [403, 404], "Terbuka: {$url}");
        }
    }
}