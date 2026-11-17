<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\CityResolver;
use App\Services\CurrencyService;
use App\Services\VehicleAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CalendarPriceController extends Controller
{
    private const MAX_SPAN_DAYS = 70;

    public function index(
        Request $request,
        VehicleAvailability $availability,
        CityResolver $cities,
        CurrencyService $currencies,
    ): JsonResponse {
        $data = $request->validate([
            'type' => ['required', Rule::in(['hotels', 'cars'])],
            'city' => ['nullable', 'string', 'max:100'],
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->startOfDay();

        if ($from->lt(today())) {
            $from = today();
        }

        if ($to->gt($from->copy()->addDays(self::MAX_SPAN_DAYS))) {
            $to = $from->copy()->addDays(self::MAX_SPAN_DAYS);
        }

        $term = trim((string) ($data['city'] ?? ''));

        $idr = $to->lt($from)
            ? []
            : ($data['type'] === 'hotels'
                ? $this->hotelPrices($from, $to, $term, $cities)
                : $this->carPrices($from, $to, $term, $availability));

        $currency = $currencies->current();
        $isIdr    = $currency->code === config('sekalago.default_currency');

        // Rupiah ditampilkan dalam ribuan; mata uang lain apa adanya (satu desimal untuk angka kecil)
        $convert = function (int $amount) use ($currency, $isIdr) {
            if ($isIdr) {
                return (int) round($amount / 1000);
            }

            $value = $amount / $currency->rate_to_base;

            return $value < 10 ? round($value, 1) : (int) round($value);
        };

        return response()->json([
            'prices'   => collect($idr)->map($convert)->all(),
            'currency' => $currency->code,
            'legend'   => $isIdr
                ? __("ui.calendar_legend_{$data['type']}_idr")
                : __("ui.calendar_legend_{$data['type']}_other", ['currency' => $currency->code]),
        ]);
    }

    /** Harga kamar termurah per malam, hanya dari hotel disetujui yang masih punya kamar pada malam itu. */
    private function hotelPrices(Carbon $from, Carbon $to, string $term, CityResolver $cities): array
    {
        $ids  = $term !== '' ? $cities->idsFor($term) : [];
        $like = '%' . addcslashes($term, '%_\\') . '%';

        return DB::table('room_stocks')
            ->join('room_types', 'room_types.id', '=', 'room_stocks.room_type_id')
            ->join('hotels', 'hotels.id', '=', 'room_types.hotel_id')
            ->where('hotels.status', 'approved')
            ->when($term !== '', fn ($q) => $q->where(function ($w) use ($like, $ids) {
                $w->where('hotels.city', 'like', $like);

                if ($ids !== []) {
                    $w->orWhereIn('hotels.city_id', $ids);
                }
            }))
            ->whereBetween('room_stocks.date', [$from->toDateString(), $to->toDateString()])
            ->whereColumn('room_stocks.available', '>', 'room_stocks.booked')
            ->groupBy('room_stocks.date')
            ->selectRaw('room_stocks.date AS day, MIN(room_types.base_price) AS price')
            ->pluck('price', 'day')
            ->map(fn ($price) => (int) $price)
            ->all();
    }

    /** Harga sewa harian termurah dari armada yang masih punya unit kosong dan tidak diblokir pada hari itu. */
    private function carPrices(Carbon $from, Carbon $to, string $term, VehicleAvailability $availability): array
    {
        $vehicles = Vehicle::query()
            ->where('status', 'approved')
            ->where('units', '>', 0)
            ->when($term !== '', fn ($q) => $q->inCity($term))
            ->with(['blackouts' => fn ($q) => $q
                ->where('start_date', '<=', $to->toDateString())
                ->where('end_date', '>=', $from->toDateString())])
            ->get(['id', 'units', 'price_per_day']);

        if ($vehicles->isEmpty()) {
            return [];
        }

        $bookings = $availability->overlapping($vehicles->pluck('id'), $from, $to->copy()->addDay());
        $prices   = [];

        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            foreach ($vehicles as $vehicle) {
                $blocked = $vehicle->blackouts->contains(fn ($b) => $b->start_date->lte($day) && $b->end_date->gte($day));

                if ($blocked) {
                    continue;
                }

                $used = $bookings->get($vehicle->id, collect())
                    ->filter(fn ($b) => $b->check_in->lte($day) && $b->occupiedUntil()->gt($day))
                    ->count();

                if ($used < $vehicle->units) {
                    $key          = $day->toDateString();
                    $prices[$key] = min($prices[$key] ?? PHP_INT_MAX, (int) $vehicle->price_per_day);
                }
            }
        }

        return $prices;
    }
}