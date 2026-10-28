<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HotelController extends Controller
{
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
                'city'        => $data['city'],
                'address'     => $data['address'] ?? null,
                'star_rating' => $data['star_rating'] ?? null,
            ]);

            $this->saveTranslations($hotel, $data['translations']);
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
                'city'        => $data['city'],
                'address'     => $data['address'] ?? null,
                'star_rating' => $data['star_rating'] ?? null,
            ]);

            $this->saveTranslations($hotel, $data['translations']);
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

    // ---------------------------------------------------------------

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'city'                       => ['required', 'string', 'max:100'],
            'address'                    => ['nullable', 'string', 'max:255'],
            'star_rating'                => ['nullable', 'integer', 'between:1,5'],
            'facilities'                 => ['nullable', 'array'],
            'facilities.*'               => ['exists:facilities,id'],
            'translations'               => ['required', 'array'],
            'translations.*.name'        => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
        ]);

        // Minimal satu bahasa harus punya nama hotel
        $hasName = collect(array_keys(config('sekalago.locales')))
            ->contains(fn ($locale) => filled($data['translations'][$locale]['name'] ?? null));

        if (! $hasName) {
            throw ValidationException::withMessages([
                'translations' => __('ui.hotel_name_required'),
            ]);
        }

        return $data;
    }

    /** Simpan terjemahan per bahasa; bahasa yang namanya dikosongkan dihapus. */
    private function saveTranslations(Hotel $hotel, array $input): void
    {
        foreach (array_keys(config('sekalago.locales')) as $locale) {
            $name = $input[$locale]['name'] ?? null;

            if (filled($name)) {
                $hotel->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name'               => $name,
                        'description'        => $input[$locale]['description'] ?? null,
                        'is_auto_translated' => false,
                    ]
                );
            } else {
                $hotel->translations()->where('locale', $locale)->delete();
            }
        }
    }

    private function partner(Request $request): Partner
    {
        $partner = $request->user()->partner;
        abort_unless($partner, 403);

        return $partner;
    }

    private function ensureVerified(Request $request): Partner
    {
        $partner = $this->partner($request);
        abort_unless($partner->verification_status === 'verified', 403, __('ui.not_verified_notice'));

        return $partner;
    }

    private function ownHotel(Request $request, Hotel $hotel): void
    {
        abort_unless($hotel->partner_id === $this->partner($request)->id, 403);
    }
}