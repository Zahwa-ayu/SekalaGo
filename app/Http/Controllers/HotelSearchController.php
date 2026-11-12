<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Services\Search\HotelSearchService;
use Illuminate\Http\Request;

class HotelSearchController extends Controller
{
    public function __construct(private HotelSearchService $service)
    {
    }

    /** Halaman Hotel: hasil pencarian jika ada kata kunci, kalau tidak daftar hotel pilihan. */
    public function search(Request $request)
    {
        if (! $request->filled('city')) {
            return $this->browse();
        }

        $search = $this->service->search($request);

        return view('hotels.index', [
            'search' => $search,
            'params' => $search['params'],
            'cities' => $this->cities(),
        ]);
    }

    public function show(Request $request, Hotel $hotel)
    {
        abort_unless($hotel->status === 'approved', 404);

        $hotel->load('translations', 'facilities', 'photos', 'nearbyPlaces');

        $query  = $hotel->roomTypes()->with('translations', 'photos', 'facilities')->orderBy('base_price');
        $params = null;
        $nights = null;

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $params = $request->validate([
                'city'      => ['nullable', 'string', 'max:100'],
                'check_in'  => ['required', 'date', 'after_or_equal:today'],
                'check_out' => ['required', 'date', 'after:check_in'],
                'guests'    => ['nullable', 'integer', 'between:1,20'],
            ]);
            $params['guests'] = $params['guests'] ?? 2;

            [$checkIn, $checkOut, $nights] = $this->service->stay($params);

            $query->withOpenNights($checkIn, $checkOut);
        }

        $roomTypes = $query->get()->each(function ($roomType) use ($params, $nights) {
            $roomType->is_open = $params
                ? ($roomType->open_nights === $nights && $roomType->capacity >= $params['guests'])
                : null;
        });

        return view('hotels.show', compact('hotel', 'roomTypes', 'params', 'nights'));
    }

    /** Hotel pilihan beserta harga mulai dari (tanpa pencarian). */
    private function browse()
    {
        $hotels = Hotel::query()
            ->where('status', 'approved')
            ->whereHas('roomTypes')
            ->with(['translations', 'photos', 'facilities'])
            ->withMin('roomTypes as min_price', 'base_price')
            ->orderByDesc('star_rating')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('hotels.index', [
            'hotels' => $hotels,
            'cities' => $this->cities(),
            'params' => [],
        ]);
    }

    /** Daftar kota hotel yang sudah disetujui (untuk saran di kolom kota). */
/** Daftar kota hotel yang sudah disetujui (untuk saran dan kota populer). */
    private function cities()
    {
        return \App\Services\CityDirectory::names('hotels');
    }
}