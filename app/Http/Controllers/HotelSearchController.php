<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class HotelSearchController extends Controller
{
    private const MAX_NIGHTS = 30;

    public function home()
    {
        return view('home', ['cities' => $this->cities(), 'params' => []]);
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'city'      => ['required', 'string', 'max:100'],
            'check_in'  => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['required', 'integer', 'between:1,20'],
        ]);

        [$checkIn, $checkOut, $nights] = $this->stay($data);

        $city = addcslashes($data['city'], '%_\\');

        $roomTypes = RoomType::query()
            ->withOpenNights($checkIn, $checkOut)
            ->where('capacity', '>=', $data['guests'])
            ->having('open_nights', '=', $nights)
            ->whereHas('hotel', fn ($q) => $q
                ->where('status', 'approved')
                ->where('city', 'like', "%{$city}%"))
            ->with(['translations', 'hotel.translations', 'hotel.facilities'])
            ->get();

        $results = $roomTypes
            ->groupBy('hotel_id')
            ->map(fn ($types) => [
                'hotel'     => $types->first()->hotel,
                'min_price' => $types->min('base_price'),
            ])
            ->sortBy('min_price')
            ->values();

        return view('hotels.search', [
            'results' => $results,
            'nights'  => $nights,
            'params'  => $data,
            'cities'  => $this->cities(),
        ]);
    }

    public function show(Request $request, Hotel $hotel)
    {
        abort_unless($hotel->status === 'approved', 404);

        $hotel->load('translations', 'facilities');

        $query  = $hotel->roomTypes()->with('translations')->orderBy('base_price');
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

            [$checkIn, $checkOut, $nights] = $this->stay($params);

            $query->withOpenNights($checkIn, $checkOut);
        }

        $roomTypes = $query->get()->each(function ($roomType) use ($params, $nights) {
            $roomType->is_open = $params
                ? ($roomType->open_nights === $nights && $roomType->capacity >= $params['guests'])
                : null;
        });

        return view('hotels.show', compact('hotel', 'roomTypes', 'params', 'nights'));
    }

    /** @return array{0: Carbon, 1: Carbon, 2: int} */
    private function stay(array $data): array
    {
        $checkIn  = Carbon::parse($data['check_in'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out'])->startOfDay();
        $nights   = (int) $checkIn->diffInDays($checkOut);

        if ($nights > self::MAX_NIGHTS) {
            throw ValidationException::withMessages([
                'check_out' => __('ui.max_nights', ['count' => self::MAX_NIGHTS]),
            ]);
        }

        return [$checkIn, $checkOut, $nights];
    }

    /** Daftar kota hotel yang sudah disetujui (untuk saran di kolom kota). */
    private function cities()
    {
        return Hotel::where('status', 'approved')
            ->whereHas('roomTypes')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }
}