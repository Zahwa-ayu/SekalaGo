<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\NearbyPlace;
use App\Models\Partner;
use App\Services\TranslationService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HotelController extends Controller
{
    use PartnerHelpers;

    public function __construct(private TranslationService $translations)
    {
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);
        $hotels  = $partner->hotels()->with('translations')->latest()->get();

        return view('partner.hotels.index', compact('partner', 'hotels'));
    }

    public function create(Request $request)
    {
        $this->ensureVerified($request);

        return view('partner.hotels.create', [
            'hotel'      => new Hotel(),
            'facilities' => Facility::forHotel()->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $partner = $this->ensureVerified($request);
        $data    = $this->validated($request);

        $hotel = DB::transaction(function () use ($partner, $data) {
            $hotel = $partner->hotels()->create($this->hotelAttributes($data));

            $this->translations->sync(
                $hotel,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            $hotel->facilities()->sync($data['facilities'] ?? []);
            $this->syncNearby($hotel, $data['nearby'] ?? []);

            return $hotel;
        });

        ActivityLogger::record($hotel, 'created', [], ['city' => $hotel->city, 'status' => 'pending']);

        return redirect()->route('partner.hotels.photos.index', $hotel)
            ->with('success', __('ui.hotel_created_add_photos'));
    }

    public function edit(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->load('translations', 'facilities', 'nearbyPlaces');

        return view('partner.hotels.edit', [
            'hotel'      => $hotel,
            'facilities' => Facility::forHotel()->orderBy('code')->get(),
            'activities' => $hotel->activities()->limit(15)->get()
        ]);
    }

    public function update(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $data = $this->validated($request);

        DB::transaction(function () use ($hotel, $data) {
            $changes = $this->hotelAttributes($data);

            // Hotel yang ditolak diajukan ulang setelah diperbaiki mitra
            if ($hotel->status === 'rejected') {
                $changes['status']           = 'pending';
                $changes['rejection_reason'] = null;
            }

            $hotel->update($changes);

            $this->translations->sync(
                $hotel,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            $hotel->facilities()->sync($data['facilities'] ?? []);
            $this->syncNearby($hotel, $data['nearby'] ?? []);
        });

        return redirect()->route('partner.hotels.index')->with('success', __('ui.saved'));
    }

    public function destroy(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->delete();   // foto ikut dihapus lewat event model

        return redirect()->route('partner.hotels.index')->with('success', __('ui.deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'area'                    => ['nullable', 'string', 'max:100'],
            'address'                 => ['nullable', 'string', 'max:255'],
            'latitude'                => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude'               => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'star_rating'             => ['nullable', 'integer', 'between:1,5'],
            'check_in_time'           => ['required', 'date_format:H:i'],
            'check_out_time'          => ['required', 'date_format:H:i'],
            'facilities'              => ['nullable', 'array'],
            'facilities.*'            => [Rule::exists('facilities', 'id')->whereIn('scope', ['hotel', 'both'])],
            'nearby'                  => ['nullable', 'array', 'max:10'],
            'nearby.*.name'           => ['nullable', 'string', 'max:120'],
            'nearby.*.type'           => ['nullable', Rule::in(NearbyPlace::TYPES)],
            'nearby.*.distance_km'    => ['nullable', 'numeric', 'min:0', 'max:999'],
            'source_locale'           => ['required', Rule::in(array_keys(config('sekalago.locales')))],
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string', 'max:2000'],
            'overrides'               => ['nullable', 'array'],
            'overrides.*.name'        => ['nullable', 'string', 'max:255'],
            'overrides.*.description' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function hotelAttributes(array $data): array
    {
        return [
            'city_id' => $data['city_id'],
            'city'    => \App\Models\City::findOrFail($data['city_id'])->name,
            'area'           => $data['area'] ?? null,
            'address'        => $data['address'] ?? null,
            'latitude'       => $data['latitude'] ?? null,
            'longitude'      => $data['longitude'] ?? null,
            'star_rating'    => $data['star_rating'] ?? null,
            'check_in_time'  => $data['check_in_time'],
            'check_out_time' => $data['check_out_time'],
            'source_locale'  => $data['source_locale'],
        ];
    }

    /** Daftar tempat terdekat diganti seluruhnya; baris tanpa nama dilewati. */
    private function syncNearby(Hotel $hotel, array $rows): void
    {
        $hotel->nearbyPlaces()->delete();

        foreach ($rows as $row) {
            if (blank($row['name'] ?? null)) {
                continue;
            }

            $hotel->nearbyPlaces()->create([
                'name'        => trim($row['name']),
                'type'        => $row['type'] ?? 'other',
                'distance_km' => $row['distance_km'] ?? null,
            ]);
        }
    }

    private function ensureVerified(Request $request): Partner
    {
        $partner = $this->partner($request);
        abort_unless($partner->verification_status === 'verified', 403, __('ui.not_verified_notice'));

        return $partner;
    }
}