<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleBlackout;
use App\Services\VehicleAvailability;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BlackoutController extends Controller
{
    use PartnerHelpers;

    public function index(Request $request, Vehicle $vehicle)
    {
        $this->ownVehicle($request, $vehicle);
        $vehicle->load('translations');

        return view('partner.vehicles.blackouts', [
            'vehicle'   => $vehicle,
            'blackouts' => $vehicle->blackouts()->orderBy('start_date')->get(),
        ]);
    }

    public function store(Request $request, Vehicle $vehicle, VehicleAvailability $availability)
    {
        $this->ownVehicle($request, $vehicle);

        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        // Tanggal akhir ikut diblokir, jadi rentang yang diperiksa berakhir sehari sesudahnya
        $conflict = $availability->overlapping(
            [$vehicle->id],
            Carbon::parse($data['start_date']),
            Carbon::parse($data['end_date'])->addDay()
        )->isNotEmpty();

        if ($conflict) {
            throw ValidationException::withMessages(['start_date' => __('ui.blackout_conflict')]);
        }

        $vehicle->blackouts()->create($data);

        ActivityLogger::record($vehicle, 'blackout_added', [], ['from' => $data['start_date'], 'to' => $data['end_date']]);

        return redirect()->route('partner.vehicles.blackouts.index', $vehicle)->with('success', __('ui.blackout_added'));
    }

    public function destroy(Request $request, VehicleBlackout $blackout)
    {
        $vehicle = $blackout->vehicle;
        $this->ownVehicle($request, $vehicle);

        $blackout->delete();

        ActivityLogger::record($vehicle, 'blackout_removed', [], [
            'from' => $blackout->start_date->toDateString(),
            'to'   => $blackout->end_date->toDateString(),
        ]);

        return back()->with('success', __('ui.blackout_removed'));
    }
}