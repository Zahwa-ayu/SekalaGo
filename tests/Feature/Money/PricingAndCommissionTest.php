<?php

namespace Tests\Feature\Money;

use App\Models\Currency;
use App\Services\CurrencyService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PricingAndCommissionTest extends TestCase
{
    public static function commissionCases(): array
    {
        // [komisi MOU, harga per malam, komisi platform yang diharapkan, hak mitra yang diharapkan]
        return [
            'komisi minimum 10%'        => [0.10, 500000, 50000, 450000],
            'komisi maksimum 25%'       => [0.25, 500000, 125000, 375000],
            'pembulatan ke atas (12,5%)' => [0.125, 333333, 41667, 291666],
            'pembulatan 10% dari 999999' => [0.10, 999999, 100000, 899999],
        ];
    }

    #[DataProvider('commissionCases')]
    public function test_commission_follows_the_rate_in_each_partners_mou(float $rate, int $price, int $fee, int $net): void
    {
        $partner = $this->makePartner(['commission_rate' => $rate]);
        $room    = $this->makeRoomType($this->makeHotel($partner), ['base_price' => $price], quota: 2);

        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 1);

        $this->assertSame($price, $booking->total_price);
        $this->assertSame($fee, $booking->platform_fee);
        $this->assertSame($net, $booking->vendor_revenue);
    }

    public function test_hotel_totals_are_nightly_price_times_nights(): void
    {
        $room = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000]);

        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 3);

        $this->assertSame(500000, $booking->price_per_night);
        $this->assertSame(3, $booking->nights);
        $this->assertSame(1500000, $booking->total_price);
        $this->assertSame(225000, $booking->platform_fee);      // 15%
        $this->assertSame(1275000, $booking->vendor_revenue);
    }

    public function test_commission_and_partner_share_always_add_up_to_the_total(): void
    {
        foreach ([0.10, 0.125, 0.175, 0.25] as $rate) {
            foreach ([99999, 123457, 777777, 1250000] as $price) {
                $partner = $this->makePartner(['commission_rate' => $rate]);
                $room    = $this->makeRoomType($this->makeHotel($partner), ['base_price' => $price], quota: 1);

                $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 2);

                $this->assertSame(
                    $booking->total_price,
                    $booking->platform_fee + $booking->vendor_revenue,
                    "Selisih pada komisi {$rate} dan harga {$price}"
                );
            }
        }
    }

    public function test_the_price_is_locked_at_booking_time(): void
    {
        $room    = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000]);
        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 2);

        $room->update(['base_price' => 900000]);

        $booking->refresh();
        $this->assertSame(500000, $booking->price_per_night);
        $this->assertSame(1000000, $booking->total_price);
    }

    public function test_a_partner_without_signed_mou_uses_the_default_commission_and_cancel_window(): void
    {
        $partner = $this->makePartner(signed: false);
        $room    = $this->makeRoomType($this->makeHotel($partner), ['base_price' => 500000]);

        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 1);

        $this->assertSame((int) round(500000 * (float) config('sekalago.commission_rate')), $booking->platform_fee);
        $this->assertSame(3, $booking->free_cancel_days);
    }

    public function test_free_cancel_days_are_copied_from_the_mou_and_never_change_afterwards(): void
    {
        $partner = $this->makePartner(['free_cancel_days' => 7]);
        $room    = $this->makeRoomType($this->makeHotel($partner));

        $booking = $this->bookRoom($this->makeCustomer(), $room);
        $this->assertSame(7, $booking->free_cancel_days);

        $partner->agreements()->update(['free_cancel_days' => 2]);

        $this->assertSame(7, $booking->fresh()->free_cancel_days);
    }

    public function test_the_display_currency_and_rate_are_locked_but_payment_stays_in_idr(): void
    {
        $room = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000]);

        app(CurrencyService::class)->use('USD');
        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 3);

        $this->assertSame('USD', $booking->currency_code);
        $this->assertEquals(16500, $booking->exchange_rate);
        $this->assertEqualsWithDelta(90.91, $booking->total_price_display, 0.001);   // 1.500.000 / 16.500
        $this->assertSame(1500000, $booking->total_price);                           // pembayaran tetap IDR

        Currency::where('code', 'USD')->update(['rate_to_base' => 17000]);

        $this->assertEquals(16500, $booking->fresh()->exchange_rate);
        $this->assertEqualsWithDelta(90.91, $booking->fresh()->total_price_display, 0.001);
    }

    public function test_won_prices_are_whole_numbers(): void
    {
        $room = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000]);

        app(CurrencyService::class)->use('KRW');
        $booking = $this->bookRoom($this->makeCustomer(), $room, from: 2, nights: 3);

        $this->assertSame('KRW', $booking->currency_code);
        $this->assertEqualsWithDelta(125000.0, $booking->total_price_display, 0.001);   // 1.500.000 / 12
    }

    public function test_car_commission_is_charged_on_the_rental_price_only(): void
    {
        $vehicle = $this->makeVehicle($this->makePartner(), ['price_per_day' => 450000, 'driver_fee_per_day' => 250000]);

        $booking = $this->bookCar($this->makeCustomer(), $vehicle, from: 2, days: 3, withDriver: true);

        $this->assertSame('car', $booking->product_type);
        $this->assertSame(3, $booking->nights);
        $this->assertSame(450000, $booking->price_per_night);
        $this->assertSame(1350000, $booking->total_price);          // tanpa biaya supir
        $this->assertSame(202500, $booking->platform_fee);          // 15% dari harga sewa
        $this->assertSame(1147500, $booking->vendor_revenue);
        $this->assertTrue($booking->with_driver);
        $this->assertSame(250000, $booking->driver_fee_per_day);    // hanya salinan estimasi
    }

    public function test_self_drive_bookings_carry_no_driver_fee(): void
    {
        $vehicle = $this->makeVehicle($this->makePartner(), ['driver_fee_per_day' => 250000]);

        $booking = $this->bookCar($this->makeCustomer(), $vehicle, withDriver: false);

        $this->assertFalse($booking->with_driver);
        $this->assertNull($booking->driver_fee_per_day);
        $this->assertSame(__('ui.self_drive_label'), $booking->room_name);
    }

    public function test_car_booking_copies_the_pickup_location_and_cancel_window(): void
    {
        $partner = $this->makePartner(['free_cancel_days' => 5]);
        $vehicle = $this->makeVehicle($partner, ['address' => 'Jl. Tes No. 2', 'area' => 'Sleman', 'city' => 'Yogyakarta']);

        $booking = $this->bookCar($this->makeCustomer(), $vehicle);

        $this->assertSame('Jl. Tes No. 2, Sleman, Yogyakarta', $booking->pickup_location);
        $this->assertSame(5, $booking->free_cancel_days);
    }
}