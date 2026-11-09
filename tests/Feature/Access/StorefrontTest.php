<?php

namespace Tests\Feature\Access;

use App\Models\VehicleBlackout;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    private function hotelParams(): array
    {
        return [
            'city'      => 'Yogyakarta',
            'check_in'  => $this->day(2)->toDateString(),
            'check_out' => $this->day(4)->toDateString(),
            'guests'    => 2,
        ];
    }

    private function carParams(): array
    {
        return [
            'city'        => 'Yogyakarta',
            'pickup_date' => $this->day(2)->toDateString(),
            'return_date' => $this->day(4)->toDateString(),
        ];
    }

    public function test_the_public_pages_load_for_guests(): void
    {
        $partner = $this->makePartner();
        $this->makeRoomType($this->makeHotel($partner), quota: 2);
        $this->makeVehicle($partner);

        $this->get('/')->assertOk();
        $this->get(route('hotels.search'))->assertOk();
        $this->get(route('cars.home'))->assertOk();
    }

    public function test_the_hotel_page_lists_approved_hotels_only(): void
    {
        $partner  = $this->makePartner();
        $approved = $this->makeHotel($partner);
        $pending  = $this->makeHotel($partner, ['status' => 'pending']);
        $this->makeRoomType($approved, quota: 2);
        $this->makeRoomType($pending, quota: 2);

        $this->get(route('hotels.search'))
            ->assertOk()
            ->assertSee($approved->name)
            ->assertDontSee($pending->name);
    }

    public function test_hotel_search_results_stay_on_the_hotel_page(): void
    {
        $hotel = $this->makeHotel($this->makePartner());
        $this->makeRoomType($hotel, quota: 2);

        $this->get(route('hotels.search', $this->hotelParams()))
            ->assertOk()
            ->assertSee($hotel->name);
    }

    public function test_hotel_search_from_the_home_page_stays_on_the_home_page(): void
    {
        $hotel = $this->makeHotel($this->makePartner());
        $this->makeRoomType($hotel, quota: 2);

        $this->get(route('home', ['tab' => 'hotels'] + $this->hotelParams()))
            ->assertOk()
            ->assertSee('id="results"', false)
            ->assertSee($hotel->name);
    }

    public function test_car_search_from_the_home_page_stays_on_the_home_page(): void
    {
        $vehicle = $this->makeVehicle($this->makePartner());

        $this->get(route('home', ['tab' => 'cars'] + $this->carParams()))
            ->assertOk()
            ->assertSee('id="results"', false)
            ->assertSee($vehicle->name);
    }

    public function test_car_search_on_the_car_page_stays_on_the_car_page(): void
    {
        $vehicle = $this->makeVehicle($this->makePartner());

        $this->get(route('cars.home', $this->carParams()))
            ->assertOk()
            ->assertSee($vehicle->name);
    }

    public function test_cars_that_are_blocked_or_fully_booked_are_not_listed(): void
    {
        $partner = $this->makePartner();
        $blocked = $this->makeVehicle($partner);
        $busy    = $this->makeVehicle($partner, ['units' => 1]);
        $free    = $this->makeVehicle($partner);

        VehicleBlackout::create([
            'vehicle_id' => $blocked->id,
            'start_date' => $this->day(1),
            'end_date'   => $this->day(5),
        ]);
        $this->pay($this->bookCar($this->makeCustomer(), $busy, from: 2, days: 2));

        $this->get(route('cars.home', $this->carParams()))
            ->assertOk()
            ->assertSee($free->name)
            ->assertDontSee($blocked->name)
            ->assertDontSee($busy->name);
    }

    public function test_the_old_car_search_address_redirects_to_the_car_page(): void
    {
        $params = $this->carParams();

        $this->get('/cars/search?' . http_build_query($params))
            ->assertRedirect(route('cars.home', $params));
    }

    public function test_search_with_impossible_dates_is_refused_where_it_started(): void
    {
        $this->get(route('cars.home', ['pickup_date' => $this->day(-1)->toDateString()] + $this->carParams()))
            ->assertSessionHasErrors('pickup_date');

        $this->get(route('hotels.search', ['check_out' => $this->day(1)->toDateString()] + $this->hotelParams()))
            ->assertSessionHasErrors('check_out');
    }

    public function test_detail_pages_render_with_and_without_dates(): void
    {
        $partner = $this->makePartner();
        $hotel   = $this->makeHotel($partner);
        $this->makeRoomType($hotel, quota: 2);
        $vehicle = $this->makeVehicle($partner);

        $this->get(route('hotels.show', $hotel))->assertOk()->assertSee($hotel->name);
        $this->get(route('hotels.show', ['hotel' => $hotel] + $this->hotelParams()))->assertOk();
        $this->get(route('cars.show', $vehicle))->assertOk()->assertSee($vehicle->name);
        $this->get(route('cars.show', ['vehicle' => $vehicle] + $this->carParams()))->assertOk();
    }

    public function test_unapproved_hotels_and_vehicles_have_no_public_page(): void
    {
        $partner = $this->makePartner();
        $hotel   = $this->makeHotel($partner, ['status' => 'pending']);
        $vehicle = $this->makeVehicle($partner, ['status' => 'pending']);

        $this->get(route('hotels.show', $hotel))->assertNotFound();
        $this->get(route('cars.show', $vehicle))->assertNotFound();
    }
}