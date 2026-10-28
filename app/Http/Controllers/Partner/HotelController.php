<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Partner;
use App\Services\TranslationService;
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
            'facilities' => Facility::orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $partner = $this->ensureVerified($request);
        $data    = $this->validated($request);

        DB::transaction(function () use ($partner, $data) {
            $hotel = $partner->hotels()->create([
                'city'          => $data['city'],
                'address'       => $data['address'] ?? null,
                'star_rating'   => $data['star_rating'] ?? null,
                'source_locale' => $data['source_locale'],
            ]);

            $this->translations->sync(
                $hotel,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            $hotel->facilities()->sync($data['facilities'] ?? []);
        });

        return redirect()->route('partner.hotels.index')->with('success', __('ui.saved'));
    }

    public function edit(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->load('translations', 'facilities');

        return view('partner.hotels.edit', [
            'hotel'      => $hotel,
            'facilities' => Facility::orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $data = $this->validated($request);

        DB::transaction(function () use ($hotel, $data) {
            $hotel->update([
                'city'          => $data['city'],
                'address'       => $data['address'] ?? null,
                'star_rating'   => $data['star_rating'] ?? null,
                'source_locale' => $data['source_locale'],
            ]);

            $this->translations->sync(
                $hotel,
                $data['source_locale'],
                $this->sourceText($data),
                $data['overrides'] ?? []
            );

            $hotel->facilities()->sync($data['facilities'] ?? []);
        });

        return redirect()->route('partner.hotels.index')->with('success', __('ui.saved'));
    }

    public function destroy(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->delete();

        return redirect()->route('partner.hotels.index')->with('success', __('ui.deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'city'                    => ['required', 'string', 'max:100'],
            'address'                 => ['nullable', 'string', 'max:255'],
            'star_rating'             => ['nullable', 'integer', 'between:1,5'],
            'facilities'              => ['nullable', 'array'],
            'facilities.*'            => ['exists:facilities,id'],
            'source_locale'           => ['required', Rule::in(array_keys(config('sekalago.locales')))],
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'overrides'               => ['nullable', 'array'],
            'overrides.*.name'        => ['nullable', 'string', 'max:255'],
            'overrides.*.description' => ['nullable', 'string'],
        ]);
    }

    private function ensureVerified(Request $request): Partner
    {
        $partner = $this->partner($request);
        abort_unless($partner->verification_status === 'verified', 403, __('ui.not_verified_notice'));

        return $partner;
    }
}