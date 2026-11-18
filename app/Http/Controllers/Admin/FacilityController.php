<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $scope  = $request->query('scope');
        abort_unless(in_array($scope, [null, '', 'hotel', 'room', 'both'], true), 404);

        // Jumlahnya kecil dan nama berasal dari tiga sumber, jadi disaring di PHP lalu dibagi per halaman
        $all = Facility::query()
            ->withCount(['hotels', 'roomTypes'])
            ->when($scope, fn ($q, $value) => $q->where('scope', $value))
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->when($search !== '', fn ($items) => $items->filter(fn (Facility $f) =>
                Str::contains(Str::lower($f->label . ' ' . $f->code . ' ' . implode(' ', $f->names ?? [])), Str::lower($search))));

        $perPage = (int) config('sekalago.per_page', 15) + 10;
        $page    = Paginator::resolveCurrentPage();

        $facilities = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.facilities.index', [
            'facilities' => $facilities,
            'search'     => $search,
            'scope'      => $scope,
            'locales'    => config('sekalago.locales'),
            'presets'    => Facility::ICON_CHOICES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Facility::create([
            'code'       => $this->newCode($data['names']),
            'scope'      => $data['scope'],
            'names'      => $this->cleanNames($data['names']),
            'icon_class' => $data['icon_class'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.facilities.index')->with('success', __('ui.facility_saved'));
    }

    public function update(Request $request, Facility $facility)
    {
        $data = $this->validated($request);

        // Jenis yang dipersempit tidak boleh melepas fasilitas dari hotel atau kamar yang sudah memakainya
        if ($data['scope'] === 'hotel' && $facility->roomTypes()->exists()) {
            return back()->withInput()->withErrors(['scope' => __('ui.facility_scope_in_use')]);
        }

        if ($data['scope'] === 'room' && $facility->hotels()->exists()) {
            return back()->withInput()->withErrors(['scope' => __('ui.facility_scope_in_use')]);
        }

        $facility->update([
            'scope'      => $data['scope'],
            'names'      => $this->cleanNames($data['names']),
            'icon_class' => $data['icon_class'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.facilities.index')->with('success', __('ui.facility_saved'));
    }

    public function status(Facility $facility)
    {
        $facility->update(['is_active' => ! $facility->is_active]);

        return back()->with('success', __('ui.facility_saved'));
    }

    public function destroy(Facility $facility)
    {
        if ($facility->hotels()->exists() || $facility->roomTypes()->exists()) {
            return back()->with('error', __('ui.facility_in_use'));
        }

        $facility->delete();

        return redirect()->route('admin.facilities.index')->with('success', __('ui.facility_deleted'));
    }

    private function validated(Request $request): array
    {
        $rules = [
            'names'      => ['required', 'array'],
            'scope'      => ['required', Rule::in(['hotel', 'room', 'both'])],
            'icon_class' => ['required', 'string', 'max:60', 'regex:/^fa-[a-z0-9-]+$/'],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
        ];

        // Indonesia dan English wajib, bahasa lain di config('sekalago.locales') opsional
        foreach (array_keys(config('sekalago.locales')) as $code) {
            $rules["names.$code"] = [in_array($code, ['id', 'en'], true) ? 'required' : 'nullable', 'string', 'max:60'];
        }

        return $request->validate($rules);
    }

    private function cleanNames(array $names): array
    {
        return collect($names)->map(fn ($name) => trim((string) $name))->filter()->all();
    }

    /** Kode unik dari nama Inggris, misalnya "Baby Cot" menjadi baby_cot. */
    private function newCode(array $names): string
    {
        $base = Str::limit(Str::slug($names['en'], '_'), 36, '') ?: 'facility';
        $code = $base;

        for ($i = 2; Facility::where('code', $code)->exists(); $i++) {
            $code = $base . '_' . $i;
        }

        return $code;
    }
}