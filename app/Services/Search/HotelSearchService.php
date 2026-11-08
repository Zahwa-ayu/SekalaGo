<?php

namespace App\Services\Search;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class HotelSearchService
{
    private const MAX_NIGHTS = 30;

    /**
     * Cari hotel yang punya tipe kamar tersedia untuk setiap malam di rentang tanggal.
     *
     * @return array{results: \Illuminate\Support\Collection, nights: int, params: array}
     */
    public function search(Request $request): array
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
            ->with(['translations', 'hotel.translations', 'hotel.facilities', 'hotel.photos'])
            ->get();

        $results = $roomTypes
            ->groupBy('hotel_id')
            ->map(fn ($types) => [
                'hotel'     => $types->first()->hotel,
                'min_price' => $types->min('base_price'),
            ])
            ->sortBy('min_price')
            ->values();

        return [
            'results' => $results,
            'nights'  => $nights,
            'params'  => Arr::only($data, ['city', 'check_in', 'check_out', 'guests']),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon, 2: int} */
    public function stay(array $data): array
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
}