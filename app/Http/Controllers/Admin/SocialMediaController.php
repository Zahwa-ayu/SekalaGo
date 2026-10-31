<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SocialMediaController extends Controller
{
    private const CUSTOM = 'Lainnya (Kustom)';

    public function index(): View
    {
        $socialMedia = SocialMedia::orderBy('order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $platforms = config('social_platforms');

        return view('admin.social-media.index', compact('socialMedia', 'platforms'));
    }

    public function store(Request $request): RedirectResponse
    {
        SocialMedia::create($this->resolvePlatformData($request));

        return redirect()->route('admin.social-media.index')
            ->with('success', __('ui.sm_added'));
    }

    public function update(Request $request, SocialMedia $socialMedia): RedirectResponse
    {
        $socialMedia->update($this->resolvePlatformData($request));

        return redirect()->route('admin.social-media.index')
            ->with('success', __('ui.sm_updated'));
    }

    public function updateStatus(SocialMedia $socialMedia): RedirectResponse
    {
        $socialMedia->update([
            'status' => $socialMedia->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);

        return redirect()->route('admin.social-media.index')->with(
            'success',
            $socialMedia->status === 'aktif' ? __('ui.sm_activated') : __('ui.sm_deactivated')
        );
    }

    public function destroy(SocialMedia $socialMedia): RedirectResponse
    {
        $socialMedia->delete();

        return redirect()->route('admin.social-media.index')
            ->with('success', __('ui.sm_deleted'));
    }

    private function resolvePlatformData(Request $request): array
    {
        $platforms = config('social_platforms');

        $data = $request->validate([
            'platform_choice'      => ['required', Rule::in([...array_keys($platforms), self::CUSTOM])],
            'url'                  => 'required|url|max:255',
            'order'                => 'required|integer|min:0',
            'custom_platform_name' => ['nullable', 'required_if:platform_choice,' . self::CUSTOM, 'string', 'max:255'],
            'custom_icon_class'    => ['nullable', 'required_if:platform_choice,' . self::CUSTOM, 'string', 'max:255'],
            'custom_color'         => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $common = [
            'url'    => $data['url'],
            'order'  => $data['order'],
            'status' => $request->boolean('status') ? 'aktif' : 'nonaktif',
        ];

        if ($data['platform_choice'] === self::CUSTOM) {
            return $common + [
                'platform'   => $data['custom_platform_name'],
                'icon_class' => $data['custom_icon_class'],
                'color'      => $data['custom_color'] ?? null,
            ];
        }

        $meta = $platforms[$data['platform_choice']];

        return $common + [
            'platform'   => $data['platform_choice'],
            'icon_class' => $meta['icon'] ?? 'fas fa-link',
            'color'      => $meta['color'] ?? null,
        ];
    }
}
