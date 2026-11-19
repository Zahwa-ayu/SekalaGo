<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\Search\CarSearchService;
use App\Services\VehicleAvailability;
use App\Support\BackLink;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarSearchController extends Controller
{
    public function __construct(
        private CarSearchService $service,
        private VehicleAvailability $availability,
    ) {
    }

    /** Halaman Rental Mobil: hasil pencarian jika ada kata kunci, kalau tidak armada pilihan. */
    public function home(Request $request)
    {
        $base = ['cities' => $this->cities(), 'params' => []];

        if ($request->filled('city')) {
            $search = $this->service->search($request);

            return view('cars.home', array_merge($base, ['search' => $search, 'params' => $search['params']]));
        }

        $vehicles = Vehicle::query()
            ->where('status', 'approved')
            ->with('translations', 'photos')
            ->orderBy('price_per_day')
            ->paginate(12)
            ->withQueryString();

        return view('cars.home', array_merge($base, ['vehicles' => $vehicles]));
    }

    /** Tautan lama /cars/search dialihkan ke halaman Rental Mobil. */
    public function legacySearch(Request $request)
    {
        return redirect()->route('cars.home', $request->query());
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->status === 'approved', 404);

        $vehicle->load('translations', 'photos');

        // Tanpa tanggal di alamat: pakai tanggal contoh agar ketersediaan langsung terlihat dan bisa dipesan
        $hasDates = $request->filled('pickup_date') && $request->filled('return_date');
        $input    = $hasDates
            ? $request->all()
            : [
                'city'        => $request->input('city'),
                'pickup_date' => today()->addDay()->toDateString(),
                'return_date' => today()->addDays(1 + max(2, (int) $vehicle->min_days))->toDateString(),
                'driver'      => $request->input('driver'),
                'passengers'  => $request->input('passengers'),
            ];

        $params = Validator::make($input, [
            'city'        => ['nullable', 'string', 'max:100'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
            'driver'      => ['nullable', Rule::in(['with_driver', 'self_drive'])],
            'passengers'  => ['nullable', 'integer', 'between:1,' . $vehicle->seats],
        ])->validate();

        [$pickup, $return, $days] = $this->service->period($params);

        $passengers = (int) ($params['passengers'] ?? 1);
        $driver     = $this->effectiveDriver($vehicle, $params['driver'] ?? null);
        $freeUnits  = $this->availability->freeUnits($vehicle, $pickup, $return);

        $available = $days >= $vehicle->min_days
            && $freeUnits >= 1
            && Vehicle::whereKey($vehicle->id)->availableFor($pickup, $return)->exists();

        $datesAreDefault = ! $hasDates;

        $backUrl = BackLink::toListing(
    $request,
    'cars.home',
    $hasDates ? \Illuminate\Support\Arr::only($params, ['city', 'pickup_date', 'return_date', 'driver']) : []
    );

    return view('cars.show', compact('vehicle', 'params', 'days', 'available', 'freeUnits', 'passengers', 'driver', 'datesAreDefault', 'backUrl'));
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

    /** Daftar kota yang punya armada disetujui (untuk saran di kolom kota). */
    /** Daftar kota armada yang sudah disetujui (untuk saran dan kota populer). */
    private function cities()
    {
        return \App\Services\CityDirectory::names('cars');
    }

}