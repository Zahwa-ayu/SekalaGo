<?php

namespace Tests\Feature\Inventory;

use App\Models\Vehicle;
use App\Models\VehicleBlackout;
use App\Services\BookingService;
use App\Services\VehicleAvailability;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VehicleAvailabilityTest extends TestCase
{
    private function vehicle(array $attributes = []): Vehicle
    {
        return $this->makeVehicle($this->makePartner(), $attributes);
    }

    private function free(Vehicle $vehicle, int $from, int $days): int
    {
        return app(VehicleAvailability::class)->freeUnits($vehicle, $this->day($from), $this->day($from + $days));
    }

    private function openForBlackouts(Vehicle $vehicle, int $from, int $to): bool
    {
        return Vehicle::whereKey($vehicle->id)->availableFor($this->day($from), $this->day($to))->exists();
    }

    public function test_all_units_are_free_when_nothing_is_booked(): void
    {
        $vehicle = $this->vehicle(['units' => 3]);

        $this->assertSame(3, $this->free($vehicle, 10, 3));
    }

    public function test_a_booking_uses_one_unit(): void
    {
        $vehicle = $this->vehicle(['units' => 2]);

        $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3));

        $this->assertSame(1, $this->free($vehicle, 10, 3));
    }

    public function test_a_single_unit_is_unavailable_when_the_period_overlaps_at_all(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);

        $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3));   // hari 10, 11, 12

        $this->assertSame(0, $this->free($vehicle, 10, 3));   // sama persis
        $this->assertSame(0, $this->free($vehicle, 11, 1));   // di dalam
        $this->assertSame(0, $this->free($vehicle, 9, 2));    // menyentuh hari 10
        $this->assertSame(0, $this->free($vehicle, 12, 3));   // menyentuh hari 12
    }

    public function test_a_car_returned_today_can_be_picked_up_today_by_someone_else(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);

        $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3));   // kembali tanggal 13

        $this->assertSame(1, $this->free($vehicle, 13, 2));   // ambil tepat di hari kembali
        $this->assertSame(1, $this->free($vehicle, 8, 2));    // kembali tepat di hari ambil
    }

    public function test_free_units_are_counted_day_by_day(): void
    {
        $vehicle  = $this->vehicle(['units' => 2]);
        $customer = $this->makeCustomer();

        $this->bookCar($customer, $vehicle, from: 10, days: 2);   // hari 10, 11
        $this->bookCar($customer, $vehicle, from: 12, days: 2);   // hari 12, 13

        // Dua pesanan menyentuh rentang, tetapi tiap hari paling banyak satu unit terpakai
        $this->assertSame(1, $this->free($vehicle, 10, 4));
    }

    public function test_overlapping_bookings_can_fill_every_unit(): void
    {
        $vehicle  = $this->vehicle(['units' => 2]);
        $customer = $this->makeCustomer();

        $this->bookCar($customer, $vehicle, from: 10, days: 4);   // hari 10 sampai 13
        $this->bookCar($customer, $vehicle, from: 11, days: 2);   // hari 11, 12

        $this->assertSame(0, $this->free($vehicle, 10, 4));
        $this->assertSame(1, $this->free($vehicle, 10, 1));   // hari 10 saja: hanya satu pesanan
    }

    public function test_the_last_unit_cannot_be_booked_twice(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);

        $this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3);

        $this->expectException(ValidationException::class);
        $this->bookCar($this->makeCustomer(), $vehicle, from: 11, days: 2);
    }

    public function test_an_unpaid_booking_holds_a_unit_until_it_expires(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);

        $this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3);
        $this->assertSame(0, $this->free($vehicle, 10, 3));

        // Batas bayar lewat: unit kembali tersedia walau pekerjaan kedaluwarsa belum berjalan
        $this->travel(31)->minutes();
        $this->assertSame(1, $this->free($vehicle, 10, 3));
    }

    public function test_cancelled_bookings_free_their_unit(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);
        $booking = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3));

        app(BookingService::class)->cancel($booking, 'customer');

        $this->assertSame(1, $this->free($vehicle, 10, 3));
    }

    public function test_expired_bookings_free_their_unit(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);
        $this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3);

        $this->travel(31)->minutes();
        app(BookingService::class)->expireOverdue();

        $this->assertSame(1, $this->free($vehicle, 10, 3));
    }

    public function test_blackout_dates_block_overlapping_periods_only(): void
    {
        $vehicle = $this->vehicle();

        VehicleBlackout::create([
            'vehicle_id' => $vehicle->id,
            'start_date' => $this->day(10),
            'end_date'   => $this->day(12),   // tanggal 12 ikut diblokir
            'note'       => 'Servis',
        ]);

        $this->assertFalse($this->openForBlackouts($vehicle, 10, 13));   // sama persis
        $this->assertFalse($this->openForBlackouts($vehicle, 8, 11));    // hari 10 terpakai
        $this->assertFalse($this->openForBlackouts($vehicle, 12, 14));   // hari 12 terpakai
        $this->assertTrue($this->openForBlackouts($vehicle, 8, 10));     // kembali tepat di awal blokir
        $this->assertTrue($this->openForBlackouts($vehicle, 13, 15));    // ambil sehari setelah blokir
    }

    public function test_a_blackout_starting_on_the_return_date_does_not_block(): void
    {
        $vehicle = $this->vehicle();

        VehicleBlackout::create([
            'vehicle_id' => $vehicle->id,
            'start_date' => $this->day(15),
            'end_date'   => $this->day(16),
        ]);

        $this->assertTrue($this->openForBlackouts($vehicle, 13, 15));    // hari terpakai 13 dan 14
        $this->assertFalse($this->openForBlackouts($vehicle, 13, 16));   // hari 15 ikut terpakai
    }

    public function test_booking_is_rejected_on_blackout_dates(): void
    {
        $vehicle = $this->vehicle();

        VehicleBlackout::create([
            'vehicle_id' => $vehicle->id,
            'start_date' => $this->day(10),
            'end_date'   => $this->day(12),
        ]);

        $this->expectException(ValidationException::class);
        $this->bookCar($this->makeCustomer(), $vehicle, from: 9, days: 3);
    }

    public function test_only_approved_vehicles_are_available(): void
    {
        $pending = $this->vehicle(['status' => 'pending']);

        $this->assertFalse($this->openForBlackouts($pending, 10, 13));

        $this->expectException(ValidationException::class);
        $this->bookCar($this->makeCustomer(), $pending);
    }

    public function test_minimum_rental_days_are_enforced(): void
    {
        $vehicle  = $this->vehicle(['min_days' => 3, 'units' => 2]);
        $customer = $this->makeCustomer();

        $this->assertSame(3, $this->bookCar($customer, $vehicle, from: 5, days: 3)->nights);

        $this->expectException(ValidationException::class);
        $this->bookCar($customer, $vehicle, from: 10, days: 2);
    }

    public function test_passengers_cannot_exceed_the_number_of_seats(): void
    {
        $vehicle = $this->vehicle(['seats' => 4]);

        $this->expectException(ValidationException::class);
        $this->bookCar($this->makeCustomer(), $vehicle, passengers: 5);
    }

    public function test_peak_usage_is_the_most_units_used_at_the_same_time(): void
    {
        $vehicle  = $this->vehicle(['units' => 3]);
        $customer = $this->makeCustomer();
        $service  = app(VehicleAvailability::class);

        $this->assertSame(0, $service->peakUsage($vehicle));

        $this->bookCar($customer, $vehicle, from: 10, days: 3);   // hari 10 sampai 12
        $this->bookCar($customer, $vehicle, from: 11, days: 3);   // hari 11 sampai 13
        $this->bookCar($customer, $vehicle, from: 20, days: 2);   // terpisah

        $this->assertSame(2, $service->peakUsage($vehicle));
    }

    public function test_vehicle_with_running_bookings_is_reported_as_busy_until_they_end(): void
    {
        $vehicle = $this->vehicle(['units' => 1]);
        $service = app(VehicleAvailability::class);

        $this->assertFalse($service->hasActiveBookings($vehicle));

        $booking = $this->pay($this->bookCar($this->makeCustomer(), $vehicle, from: 10, days: 3));
        $this->assertTrue($service->hasActiveBookings($vehicle));

        app(BookingService::class)->cancel($booking, 'customer');
        $this->assertFalse($service->hasActiveBookings($vehicle));
    }
}