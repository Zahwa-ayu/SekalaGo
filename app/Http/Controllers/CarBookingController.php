<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\VehicleAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CarBookingController extends Controller
{
    private const MAX_DAYS = 30;

    public function __construct(
        private BookingService $bookings,
        private VehicleAvailability $availability,
    ) {
    }

    /** Ringkasan sewa sebelum dikonfirmasi. */
    public function create(Request $request)
    {
        [$vehicle, $pickup, $return, $days, $passengers, $withDriver] = $this->resolve($request);

        $member = $this->member($request);
        $total  = $vehicle->price_per_day * $days;
        $policy = $member?->evaluatePolicy($total);

        $isOpen = Vehicle::whereKey($vehicle->id)->availableFor($pickup, $return)->exists()
            && $this->availability->freeUnits($vehicle, $pickup, $return) >= 1;

        return view('car-bookings.create', [
            'vehicle'       => $vehicle,
            'pickup'        => $pickup,
            'return'        => $return,
            'days'          => $days,
            'passengers'    => $passengers,
            'withDriver'    => $withDriver,
            'total'         => $total,
            'isOpen'        => $isOpen,
            'isCorporate'   => $member !== null,
            'isFinance'     => $member?->isFinance() ?? false,
            'policy'        => $policy,
            'limit'         => $policy['limit'] ?? null,
            'needsApproval' => $policy['needs_approval'] ?? false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['guest_name' => ['required', 'string', 'max:255']]);

        [$vehicle, $pickup, $return, , $passengers, $withDriver] = $this->resolve($request);

        $member  = $this->member($request);
        $booking = $this->bookings->createForVehicle(
            $request->user(),
            $vehicle,
            $pickup,
            $return,
            $passengers,
            $request->input('guest_name'),
            $withDriver,
            $member
        );

        if ($member) {
            $message = $booking->status === 'confirmed'
                ? __('ui.booking_confirmed_deposit')
                : __('ui.booking_submitted');

            return redirect()->route('bookings.show', $booking)->with('success', $message);
        }

        return redirect()->route('bookings.pay', $booking);
    }

    /** Data karyawan jika pemesan adalah user corporate; null untuk pelanggan biasa. */
    private function member(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'corporate') {
            return null;
        }

        $member = $user->companyUser;
        abort_unless($member, 403);

        return $member->loadMissing('company', 'position');
    }

    /** @return array{0: Vehicle, 1: Carbon, 2: Carbon, 3: int, 4: int, 5: bool} */
    private function resolve(Request $request): array
    {
        $data = $request->validate([
            'vehicle'     => ['required', 'integer', 'exists:vehicles,id'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
            'passengers'  => ['required', 'integer', 'between:1,20'],
            'with_driver' => ['required', 'in:0,1'],
        ]);

        $vehicle = Vehicle::with('translations', 'photos')->findOrFail($data['vehicle']);
        abort_unless($vehicle->status === 'approved', 404);

        $pickup     = Carbon::parse($data['pickup_date'])->startOfDay();
        $return     = Carbon::parse($data['return_date'])->startOfDay();
        $days       = (int) $pickup->diffInDays($return);
        $withDriver = $data['with_driver'] === '1';
        $passengers = (int) $data['passengers'];

        if ($days > self::MAX_DAYS) {
            throw ValidationException::withMessages(['return_date' => __('ui.max_days', ['count' => self::MAX_DAYS])]);
        }

        if ($days < $vehicle->min_days) {
            throw ValidationException::withMessages(['return_date' => __('ui.min_days_notice', ['count' => $vehicle->min_days])]);
        }

        if ($withDriver ? ! $vehicle->offersDriver() : ! $vehicle->offersSelfDrive()) {
            throw ValidationException::withMessages(['with_driver' => __('ui.driver_option_unavailable')]);
        }

        if ($passengers > $vehicle->seats) {
            throw ValidationException::withMessages(['passengers' => __('ui.too_many_passengers', ['max' => $vehicle->seats])]);
        }

        return [$vehicle, $pickup, $return, $days, $passengers, $withDriver];
    }
}