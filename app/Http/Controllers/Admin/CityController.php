<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $like   = '%' . addcslashes($search, '%_\\') . '%';

        $cities = City::query()
            ->withCount(['hotels', 'vehicles'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', $like)
                ->orWhere('province', 'like', $like)
                ->orWhere('aliases', 'like', $like)))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.cities.index', compact('cities', 'search'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $city = new City($this->attributes($request, $data));

        if ($request->hasFile('image')) {
            $city->image_path = $request->file('image')->store('cities', 'public');
        }

        $city->save();

        return redirect()->route('admin.cities.index')->with('success', __('ui.city_saved'));
    }

    public function edit(City $city)
    {
        $city->loadCount(['hotels', 'vehicles']);

        return view('admin.cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $data    = $this->validated($request, $city);
        $oldName = $city->name;
        $oldPath = $city->image_path;

        DB::transaction(function () use ($request, $city, $data, $oldName) {
            $city->fill($this->attributes($request, $data));

            if ($request->hasFile('image')) {
                $city->image_path = $request->file('image')->store('cities', 'public');
            } elseif ($request->boolean('remove_image')) {
                $city->image_path = null;
            }

            $city->save();

            // Nama kota yang tersalin di hotel dan armada ikut diperbarui
            if ($city->name !== $oldName) {
                Hotel::where('city_id', $city->id)->update(['city' => $city->name]);
                Vehicle::where('city_id', $city->id)->update(['city' => $city->name]);
            }
        });

        if ($oldPath && $oldPath !== $city->image_path) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.cities.index')->with('success', __('ui.city_saved'));
    }

    public function destroy(City $city)
    {
        if ($city->hotels()->exists() || $city->vehicles()->exists()) {
            return back()->with('error', __('ui.city_in_use'));
        }

        if ($city->image_path) {
            Storage::disk('public')->delete($city->image_path);
        }

        $city->delete();

        return redirect()->route('admin.cities.index')->with('success', __('ui.city_deleted'));
    }

    private function validated(Request $request, ?City $city = null): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:100', Rule::unique('cities', 'name')->ignore($city?->id)],
            'province'     => ['nullable', 'string', 'max:100'],
            'aliases'      => ['nullable', 'string', 'max:255'],
            'sort_order'   => ['nullable', 'integer', 'between:0,9999'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=800,min_height=500'],
        ]);
    }

    /** Isian kota dari form; nama lain dirapikan (tanpa duplikat dan spasi berlebih). */
    private function attributes(Request $request, array $data): array
    {
        $aliases = collect(explode(',', (string) ($data['aliases'] ?? '')))
            ->map(fn ($alias) => trim($alias))
            ->filter()
            ->unique(fn ($alias) => mb_strtolower($alias))
            ->implode(', ');

        return [
            'name'        => $data['name'],
            'province'    => $data['province'] ?? null,
            'aliases'     => $aliases !== '' ? $aliases : null,
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'is_featured' => $request->boolean('is_featured'),
            'is_active'   => $request->boolean('is_active'),
        ];
    }
}