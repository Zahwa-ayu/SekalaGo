<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Vehicle;
use App\Services\CityDirectory;
use App\Services\Search\CarSearchService;
use App\Services\Search\HotelSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index(Request $request, HotelSearchService $hotels, CarSearchService $cars)
    {
        $tab = $request->query('tab');

        $data = [
            'cities'         => CityDirectory::names('hotels'),
            'carCities'      => CityDirectory::names('cars'),
            'favoriteCities' => CityDirectory::featured(),
            'params'         => [],
            'carParams'      => [],
            // Tab mobil aktif jika diminta lewat alamat, atau jika form mobil baru saja gagal validasi
            'activeTab'      => ($tab === 'cars' || $request->old('pickup_date')) ? 'cars' : 'hotels',

            // Etalase Beranda (hanya data berstatus approved)
            'cityStats'        => $this->cityStats(),
            'featuredHotels'   => $this->featuredHotels(),
            'featuredVehicles' => $this->featuredVehicles(),
        ];

        if ($tab === 'hotels' && $request->filled('city')) {
            $search              = $hotels->search($request);
            $data['hotelSearch'] = $search;
            $data['params']      = $search['params'];
        }

        if ($tab === 'cars' && $request->filled('city')) {
            $search             = $cars->search($request);
            $data['carSearch']  = $search;
            $data['carParams']  = $search['params'];
            $data['activeTab']  = 'cars';
        }

        return view('home', $data);
    }

    /** 4 kota dengan hotel terbanyak: jumlah hotel, jumlah armada, harga mulai, rata-rata bintang. */
    private function cityStats(): Collection
    {
        $vehicleCount = Vehicle::where('status', 'approved')
            ->selectRaw('city, count(*) as total')
            ->groupBy('city')
            ->pluck('total', 'city');

        return Hotel::query()
            ->where('status', 'approved')
            ->whereHas('roomTypes')
            ->withMin('roomTypes as min_price', 'base_price')
            ->get()
            ->groupBy('city')
            ->map(fn (Collection $group, $city) => (object) [
                'city'      => $city,
                'hotels'    => $group->count(),
                'vehicles'  => (int) ($vehicleCount[$city] ?? 0),
                'min_price' => $group->min('min_price'),
                'avg_star'  => round((float) $group->avg('star_rating'), 1),
            ])
            ->sortByDesc('hotels')
            ->take(4)
            ->values();
    }

    /** 6 hotel pilihan (bintang tertinggi, lalu terbaru) beserta harga mulai dari. */
    private function featuredHotels(): Collection
    {
        return Hotel::query()
            ->where('status', 'approved')
            ->whereHas('roomTypes')
            ->with(['translations', 'photos'])
            ->withMin('roomTypes as min_price', 'base_price')
            ->orderByDesc('star_rating')
            ->latest('id')
            ->take(6)
            ->get();
    }

    /** 3 armada dengan harga sewa harian terendah. */
    private function featuredVehicles(): Collection
    {
        return Vehicle::query()
            ->where('status', 'approved')
            ->with(['translations', 'photos'])
            ->orderBy('price_per_day')
            ->take(3)
            ->get();
    }
}