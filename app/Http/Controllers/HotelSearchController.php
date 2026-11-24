<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Services\Search\HotelSearchService;
use App\Support\BackLink;
use Illuminate\Support\Facades\Validator;
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

        // Tanpa tanggal di alamat: pakai tanggal contoh (besok, 1 malam) supaya ketersediaan langsung terlihat
        // dan kamar yang tersedia bisa langsung dipesan. Pengunjung tinggal mengubah tanggalnya.
        $hasDates = $request->filled('check_in') && $request->filled('check_out');
        $input    = $hasDates
            ? $request->all()
            : [
                'city'      => $request->input('city'),
                'check_in'  => today()->addDay()->toDateString(),
                'check_out' => today()->addDays(2)->toDateString(),
                'guests'    => $request->input('guests'),
            ];

        $params = Validator::make($input, [
            'city'      => ['nullable', 'string', 'max:100'],
            'check_in'  => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['nullable', 'integer', 'between:1,20'],
        ])->validate();

        $params['guests'] = $params['guests'] ?? 2;

        [$checkIn, $checkOut, $nights] = $this->service->stay($params);

        $roomTypes = $hotel->roomTypes()
            ->with('translations', 'photos', 'facilities')
            ->withOpenNights($checkIn, $checkOut)
            ->orderBy('base_price')
            ->get()
            ->each(function ($roomType) use ($params, $nights) {
                $roomType->is_open = $roomType->open_nights === $nights && $roomType->capacity >= $params['guests'];
            });

        $datesAreDefault = ! $hasDates;

        $backUrl = BackLink::toListing(
    $request,
    'hotels.search',
    $hasDates ? \Illuminate\Support\Arr::only($params, ['city', 'check_in', 'check_out', 'guests']) : []
    );

    $reviews = \App\Models\Review::visible()->where('hotel_id', $hotel->id)->latest()
    ->paginate(5, ['*'], 'reviews')->withQueryString()->fragment('reviews');

    return view('hotels.show', compact('hotel', 'roomTypes', 'reviews', 'params', 'nights', 'datesAreDefault', 'backUrl'));
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