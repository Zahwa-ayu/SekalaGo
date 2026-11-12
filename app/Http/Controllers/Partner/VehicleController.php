<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\TranslationService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use App\Services\VehicleAvailability;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    use PartnerHelpers;

    public function __construct(private TranslationService $translations)
    {
    }

    public function index(Request $request)
    {
        $vehicles = $this->partner($request)->vehicles()->with('translations', 'photos')->latest()->get();

        return view('partner.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('partner.vehicles.create', ['vehicle' => new Vehicle()]);
    }

    public function store(Request $request)
    {
        $partner = $this->partner($request);
        $data    = $this->validated($request);

        $vehicle = DB::transaction(function () use ($partner, $data) {
            $vehicle = $partner->vehicles()->create($this->vehicleAttributes($data));

            $this->translations->sync(
                $vehicle,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            return $vehicle;
        });

        ActivityLogger::record($vehicle, 'created', [], [
            'price_per_day' => $vehicle->price_per_day,
            'units'         => $vehicle->units,
            'status'        => 'pending',
        ]);

        return redirect()->route('partner.vehicles.photos.index', $vehicle)
            ->with('success', __('ui.vehicle_created_add_photos'));
    }

    public function edit(Request $request, Vehicle $vehicle)
    {
        $this->ownVehicle($request, $vehicle);
        $vehicle->load('translations');

        return view('partner.vehicles.edit', [
            'vehicle'    => $vehicle,
            'activities' => $vehicle->activities()->limit(15)->get(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->ownVehicle($request, $vehicle);
        $data = $this->validated($request);

        $peak = app(VehicleAvailability::class)->peakUsage($vehicle);

        if ($data['units'] < $peak) {
            throw ValidationException::withMessages([
                'units' => __('ui.units_below_bookings', ['count' => $peak]),
            ]);
        }

        DB::transaction(function () use ($vehicle, $data) {
            $changes = $this->vehicleAttributes($data);

            // Armada yang ditolak diajukan ulang setelah diperbaiki mitra
            if ($vehicle->status === 'rejected') {
                $changes['status']           = 'pending';
                $changes['rejection_reason'] = null;
            }

            $vehicle->update($changes);

            $this->translations->sync(
                $vehicle,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );
        });

        return redirect()->route('partner.vehicles.index')->with('success', __('ui.saved'));
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        $this->ownVehicle($request, $vehicle);

        // Armada dengan pesanan yang masih berjalan tidak bisa dihapus
        if (app(VehicleAvailability::class)->hasActiveBookings($vehicle)) {
            return back()->with('error', __('ui.vehicle_has_bookings'));
        }

        $vehicle->delete();   // foto ikut dihapus lewat event model

        return redirect()->route('partner.vehicles.index')->with('success', __('ui.deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'area'                    => ['nullable', 'string', 'max:100'],
            'address'                 => ['nullable', 'string', 'max:255'],
            'type'                    => ['required', Rule::in(Vehicle::TYPES)],
            'transmission'            => ['required', Rule::in(Vehicle::TRANSMISSIONS)],
            'fuel_type'               => ['required', Rule::in(Vehicle::FUEL_TYPES)],
            'seats'                   => ['required', 'integer', 'between:1,20'],
            'luggage_capacity'        => ['nullable', 'integer', 'between:0,20'],
            'model_year'              => ['nullable', 'integer', 'between:1990,' . (now()->year + 1)],
            'features'                => ['nullable', 'array'],
            'features.*'              => [Rule::in(array_keys(Vehicle::FEATURE_ICONS))],
            'units'                   => ['required', 'integer', 'between:1,100'],
            'price_per_day'           => ['required', 'integer', 'min:0'],
            'min_days'                => ['required', 'integer', 'between:1,30'],
            'return_time'             => ['required', 'date_format:H:i'],
            'late_grace_minutes'      => ['required', 'integer', 'between:0,240'],
            'late_fee_percent'        => ['required', 'integer', 'between:0,100'],
            'driver_mode'             => ['required', Rule::in(Vehicle::DRIVER_MODES)],
            'driver_fee_per_day'      => ['nullable', 'integer', 'min:0'],
            'extras_note'             => ['nullable', 'string', 'max:500'],
            'source_locale'           => ['required', Rule::in(array_keys(config('sekalago.locales')))],
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string', 'max:2000'],
            'overrides'               => ['nullable', 'array'],
            'overrides.*.name'        => ['nullable', 'string', 'max:255'],
            'overrides.*.description' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function vehicleAttributes(array $data): array
    {
        return [
            'city_id' => $data['city_id'],
            'city'    => \App\Models\City::findOrFail($data['city_id'])->name,
            'area'               => $data['area'] ?? null,
            'address'            => $data['address'] ?? null,
            'type'               => $data['type'],
            'transmission'       => $data['transmission'],
            'fuel_type'          => $data['fuel_type'],
            'seats'              => $data['seats'],
            'luggage_capacity'   => $data['luggage_capacity'] ?? null,
            'model_year'         => $data['model_year'] ?? null,
            'features'           => array_values($data['features'] ?? []),
            'units'              => $data['units'],
            'price_per_day'      => $data['price_per_day'],
            'min_days'           => $data['min_days'],
            'return_time'        => $data['return_time'],
            'late_grace_minutes' => $data['late_grace_minutes'],
            'late_fee_percent'   => $data['late_fee_percent'],
            'driver_mode'        => $data['driver_mode'],
            // Biaya supir hanya relevan jika armada menawarkan supir
            'driver_fee_per_day' => $data['driver_mode'] === 'self_drive' ? null : ($data['driver_fee_per_day'] ?? null),
            'extras_note'        => $data['extras_note'] ?? null,
            'source_locale'      => $data['source_locale'],
        ];
    }
}