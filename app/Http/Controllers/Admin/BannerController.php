<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $rows = SiteBanner::all()->keyBy('key');

        $banners = collect(SiteBanner::DEFAULTS)->map(fn ($default, $key) => [
            'key'        => $key,
            'custom'     => $rows->has($key),
            'url'        => SiteBanner::urlFor($key, 900),
            'default'    => SiteBanner::defaultUrl($key, 900),
            'updated_at' => $rows->get($key)?->updated_at,
        ])->values();

        return view('admin.banners.index', compact('banners'));
    }

    public function update(Request $request, string $key)
    {
        $this->ensureKnown($key);

        // Kesalahan ditampilkan di kartu banner yang bersangkutan
        $request->validateWithBag("banner_{$key}", [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=1200,min_height=500'],
        ]);

        $banner = SiteBanner::firstOrNew(['key' => $key]);
        $old    = $banner->image_path;

        $banner->image_path = $request->file('image')->store('banners', 'public');
        $banner->updated_by = $request->user()->id;
        $banner->save();

        if ($old) {
            Storage::disk('public')->delete($old);
        }

        SiteBanner::flush();

        return back()->with('success', __('ui.banner_saved'));
    }

    /** Kembali ke gambar bawaan. */
    public function reset(string $key)
    {
        $this->ensureKnown($key);

        $banner = SiteBanner::where('key', $key)->first();

        if ($banner) {
            Storage::disk('public')->delete($banner->image_path);
            $banner->delete();
        }

        SiteBanner::flush();

        return back()->with('success', __('ui.banner_reset_done'));
    }

    private function ensureKnown(string $key): void
    {
        abort_unless(array_key_exists($key, SiteBanner::DEFAULTS), 404);
    }
} 