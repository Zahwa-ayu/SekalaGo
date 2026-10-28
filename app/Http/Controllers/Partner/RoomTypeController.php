<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\TranslationService;
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
        $roomTypes = $hotel->roomTypes()->with('translations')->get();

        return view('partner.room-types.index', compact('hotel', 'roomTypes'));
    }

    public function create(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->load('translations');

        return view('partner.room-types.create', [
            'hotel'    => $hotel,
            'roomType' => new RoomType(),
        ]);
    }

    public function store(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $data = $this->validated($request);

        DB::transaction(function () use ($hotel, $data) {
            $roomType = $hotel->roomTypes()->create(
                Arr::only($data, ['base_price', 'capacity', 'total_rooms', 'source_locale'])
            );

            $this->translations->sync(
                $roomType,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );
        });

        return redirect()->route('partner.hotels.room-types.index', $hotel)
            ->with('success', __('ui.saved'));
    }

    public function edit(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $roomType->load('translations', 'hotel.translations');

        return view('partner.room-types.edit', [
            'roomType' => $roomType,
            'hotel'    => $roomType->hotel,
        ]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $data = $this->validated($request);

        DB::transaction(function () use ($roomType, $data) {
            $roomType->update(
                Arr::only($data, ['base_price', 'capacity', 'total_rooms', 'source_locale'])
            );

            $this->translations->sync(
                $roomType,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );
        });

        return redirect()->route('partner.hotels.room-types.index', $roomType->hotel)
            ->with('success', __('ui.saved'));
    }

    public function destroy(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $hotel = $roomType->hotel;
        $roomType->delete();

        return redirect()->route('partner.hotels.room-types.index', $hotel)
            ->with('success', __('ui.deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'base_price'              => ['required', 'integer', 'min:0'],
            'capacity'                => ['required', 'integer', 'between:1,20'],
            'total_rooms'             => ['required', 'integer', 'between:1,1000'],
            'source_locale'           => ['required', Rule::in(array_keys(config('sekalago.locales')))],
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'overrides'               => ['nullable', 'array'],
            'overrides.*.name'        => ['nullable', 'string', 'max:255'],
            'overrides.*.description' => ['nullable', 'string'],
        ]);
    }
}