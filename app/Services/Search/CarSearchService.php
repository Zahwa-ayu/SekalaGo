<?php

namespace App\Services\Search;

use App\Models\Vehicle;
use App\Services\VehicleAvailability;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CarSearchService
{
    private const MAX_DAYS = 30;
    private const PER_PAGE = 12;

    public function __construct(private VehicleAvailability $availability)
    {
    }

    /**
     * @return array{vehicles: LengthAwarePaginator, days: int, params: array, filters: array}
     */
    public function search(Request $request): array
    {
        $data = $request->validate([
            'city'         => ['required', 'string', 'max:100'],
            'pickup_date'  => ['required', 'date', 'after_or_equal:today'],
            'return_date'  => ['required', 'date', 'after:pickup_date'],
            'driver'       => ['nullable', Rule::in(['with_driver', 'self_drive'])],
            'type'         => ['nullable', Rule::in(Vehicle::TYPES)],
            'seats'        => ['nullable', 'integer', 'between:1,20'],
            'transmission' => ['nullable', Rule::in(Vehicle::TRANSMISSIONS)],
            'max_price'    => ['nullable', 'integer', 'min:0'],
            'sort'         => ['nullable', Rule::in(['price_asc', 'price_desc', 'seats_desc'])],
        ]);

        [$pickup, $return, $days] = $this->period($data);

        $query = Vehicle::query()
            ->availableFor($pickup, $return)
            ->inCity($data['city'])
            ->where('min_days', '<=', $days)
            ->when($data['driver'] ?? null, fn ($q, $mode) => $q->whereIn('driver_mode', ['both', $mode]))
            ->when($data['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($data['seats'] ?? null, fn ($q, $seats) => $q->where('seats', '>=', $seats))
            ->when($data['transmission'] ?? null, fn ($q, $value) => $q->where('transmission', $value))
            ->when($data['max_price'] ?? null, fn ($q, $max) => $q->where('price_per_day', '<=', $max));

        match ($data['sort'] ?? 'price_asc') {
            'price_desc' => $query->orderByDesc('price_per_day'),
            'seats_desc' => $query->orderByDesc('seats')->orderBy('price_per_day'),
            default      => $query->orderBy('price_per_day'),
        };

        // Ketersediaan unit dihitung tepat per hari, lalu hasilnya dibagi per halaman
        $candidates = $query->with('translations', 'photos')->get();
        $bookings   = $this->availability->overlapping($candidates->pluck('id'), $pickup, $return);

        $available = $candidates
            ->filter(fn ($vehicle) => $this->availability->freeUnits(
                $vehicle, $pickup, $return, $bookings->get($vehicle->id, collect())
            ) >= 1)
            ->values();

        $page      = Paginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $available->forPage($page, self::PER_PAGE)->values(),
            $available->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Tautan halaman berikutnya membawa pengguna kembali ke bagian hasil
        $paginator->fragment('results');

        return [
            'vehicles' => $paginator,
            'days'     => $days,
            'params'   => Arr::only($data, ['city', 'pickup_date', 'return_date', 'driver']),
            'filters'  => Arr::only($data, ['type', 'seats', 'transmission', 'max_price', 'sort']),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon, 2: int} */
    public function period(array $data): array
    {
        $pickup = Carbon::parse($data['pickup_date'])->startOfDay();
        $return = Carbon::parse($data['return_date'])->startOfDay();
        $days   = (int) $pickup->diffInDays($return);

        if ($days > self::MAX_DAYS) {
            throw ValidationException::withMessages([
                'return_date' => __('ui.max_days', ['count' => self::MAX_DAYS]),
            ]);
        }

        return [$pickup, $return, $days];
    }
}