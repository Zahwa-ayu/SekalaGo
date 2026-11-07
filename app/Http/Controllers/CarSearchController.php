<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\VehicleAvailability;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CarSearchController extends Controller
{
    private const MAX_DAYS = 30;
    private const PER_PAGE = 12;

    public function __construct(private VehicleAvailability $availability)
    {
    }

    public function home()
    {
        $vehicles = Vehicle::query()
            ->where('status', 'approved')
            ->with('translations', 'photos')
            ->orderBy('price_per_day')
            ->paginate(12)
            ->withQueryString();

        return view('cars.home', [
            'cities'   => $this->cities(),
            'params'   => [],
            'vehicles' => $vehicles,
        ]);
    }
    public function search(Request $request)
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

        $city = addcslashes($data['city'], '%_\\');

        $query = Vehicle::query()
            ->availableFor($pickup, $return)
            ->where('city', 'like', "%{$city}%")
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

        $page     = Paginator::resolveCurrentPage();
        $vehicles = new LengthAwarePaginator(
            $available->forPage($page, self::PER_PAGE)->values(),
            $available->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cars.search', [
            'vehicles' => $vehicles,
            'days'     => $days,
            'params'   => Arr::only($data, ['city', 'pickup_date', 'return_date', 'driver']),
            'filters'  => Arr::only($data, ['type', 'seats', 'transmission', 'max_price', 'sort']),
            'cities'   => $this->cities(),
        ]);
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->status === 'approved', 404);

        $vehicle->load('translations', 'photos');

        $params     = null;
        $days       = null;
        $available  = null;
        $freeUnits  = null;
        $passengers = 1;
        $driver     = null;

        if ($request->filled('pickup_date') && $request->filled('return_date')) {
            $params = $request->validate([
                'city'        => ['nullable', 'string', 'max:100'],
                'pickup_date' => ['required', 'date', 'after_or_equal:today'],
                'return_date' => ['required', 'date', 'after:pickup_date'],
                'driver'      => ['nullable', Rule::in(['with_driver', 'self_drive'])],
                'passengers'  => ['nullable', 'integer', 'between:1,' . $vehicle->seats],
            ]);

            [$pickup, $return, $days] = $this->period($params);

            $passengers = (int) ($params['passengers'] ?? 1);
            $driver     = $this->effectiveDriver($vehicle, $params['driver'] ?? null);
            $freeUnits  = $this->availability->freeUnits($vehicle, $pickup, $return);

            $available = $days >= $vehicle->min_days
                && $freeUnits >= 1
                && Vehicle::whereKey($vehicle->id)->availableFor($pickup, $return)->exists();
        }

        return view('cars.show', compact('vehicle', 'params', 'days', 'available', 'freeUnits', 'passengers', 'driver'));
    }

    /** Opsi supir yang berlaku: pilihan pengguna jika armada menawarkannya, kalau tidak yang tersedia. */
    private function effectiveDriver(Vehicle $vehicle, ?string $wanted): string
    {
        if ($wanted === 'with_driver' && $vehicle->offersDriver()) {
            return 'with_driver';
        }

        if ($wanted === 'self_drive' && $vehicle->offersSelfDrive()) {
            return 'self_drive';
        }

        return $vehicle->offersDriver() ? 'with_driver' : 'self_drive';
    }

    /** @return array{0: Carbon, 1: Carbon, 2: int} */
    private function period(array $data): array
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

    /** Daftar kota yang punya armada disetujui (untuk saran di kolom kota). */
    private function cities()
    {
        return Vehicle::where('status', 'approved')->distinct()->orderBy('city')->pluck('city');
    }
}