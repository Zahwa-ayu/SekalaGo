<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\TranslationService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    use PartnerHelpers;

    public function __construct(private TranslationService $translations)
    {
    }

    public function index(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->load('translations');
        $roomTypes = $hotel->roomTypes()->with('translations', 'photos')->get();

        return view('partner.room-types.index', compact('hotel', 'roomTypes'));
    }

    public function create(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->load('translations');

        return view('partner.room-types.create', [
            'hotel'      => $hotel,
            'roomType'   => new RoomType(),
            'facilities' => Facility::forRoom()->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $data = $this->validated($request);

        $roomType = DB::transaction(function () use ($hotel, $data) {
            $roomType = $hotel->roomTypes()->create($this->roomAttributes($data));

            $this->translations->sync(
                $roomType,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            $roomType->facilities()->sync($data['facilities'] ?? []);

            return $roomType;
        });

        ActivityLogger::record($roomType, 'created', [], ['base_price' => $roomType->base_price, 'meal_plan' => $roomType->meal_plan]);
        return redirect()->route('partner.room-types.photos.index', $roomType)
            ->with('success', __('ui.room_created_add_photos'));
    }

    public function edit(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $roomType->load('translations', 'facilities', 'hotel.translations');

        return view('partner.room-types.edit', [
            'roomType'   => $roomType,
            'hotel'      => $roomType->hotel,
            'facilities' => Facility::forRoom()->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $data = $this->validated($request);

        DB::transaction(function () use ($roomType, $data) {
            $roomType->update($this->roomAttributes($data));

            $this->translations->sync(
                $roomType,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            $roomType->facilities()->sync($data['facilities'] ?? []);
        });

        return redirect()->route('partner.hotels.room-types.index', $roomType->hotel)
            ->with('success', __('ui.saved'));
    }

    public function destroy(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $hotel = $roomType->hotel;
        $roomType->delete();   // foto kamar ikut dihapus lewat event model

        return redirect()->route('partner.hotels.room-types.index', $hotel)
            ->with('success', __('ui.deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'base_price'              => ['required', 'integer', 'min:0'],
            'capacity'                => ['required', 'integer', 'between:1,20'],
            'bed_type'                => ['required', Rule::in(RoomType::BED_TYPES)],
            'bed_count'               => ['required', 'integer', 'between:1,6'],
            'smoking_allowed'         => ['required', 'in:0,1'],
            'meal_plan'               => ['required', Rule::in(RoomType::MEAL_PLANS)],
            'total_rooms'             => ['required', 'integer', 'between:1,1000'],
            'facilities'              => ['nullable', 'array'],
            'facilities.*'            => [Rule::exists('facilities', 'id')->whereIn('scope', ['room', 'both'])],
            'source_locale'           => ['required', Rule::in(array_keys(config('sekalago.locales')))],
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string', 'max:2000'],
            'overrides'               => ['nullable', 'array'],
            'overrides.*.name'        => ['nullable', 'string', 'max:255'],
            'overrides.*.description' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function roomAttributes(array $data): array
    {
        return Arr::only($data, [
            'base_price', 'capacity', 'bed_type', 'bed_count', 'meal_plan', 'total_rooms', 'source_locale',
        ]) + ['smoking_allowed' => (bool) (int) $data['smoking_allowed']];
    }
}