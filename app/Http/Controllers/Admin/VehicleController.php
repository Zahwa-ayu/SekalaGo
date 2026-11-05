<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'all'];

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $vehicles = Vehicle::with(['partner', 'translations', 'photos'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $counts = Vehicle::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.vehicles.index', compact('vehicles', 'status', 'counts'));
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['partner', 'translations', 'photos', 'blackouts']);

        return view('admin.vehicles.show', [
            'vehicle'    => $vehicle,
            'activities' => $vehicle->activities()->limit(20)->get(),
        ]);
    }

    public function approve(Vehicle $vehicle)
    {
        if (! $vehicle->photos()->exists()) {
            return back()->withErrors(['approve' => __('ui.approve_vehicle_needs_photo')]);
        }

        $vehicle->update(['status' => 'approved', 'rejection_reason' => null]);

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', __('ui.vehicle_approved'));
    }

    public function reject(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $vehicle->update(['status' => 'rejected', 'rejection_reason' => $data['rejection_reason']]);

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', __('ui.vehicle_rejected'));
    }
}