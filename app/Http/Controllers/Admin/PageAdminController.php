<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageAdminController extends Controller
{
    // Bahasa yang wajib diisi. Bahasa lain di config('sekalago.locales') opsional.
    private const REQUIRED_LOCALES = ['id', 'en'];

    public function index()
    {
        $pages = Page::with('translations')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create', ['locales' => config('sekalago.locales')]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $page = Page::create([
                'slug'           => $this->newSlug($data['title']['en']),
                'template'       => $data['template'],
                'sort_order'     => $data['sort_order'] ?? 0,
                'is_active'      => $request->boolean('is_active'),
                'show_in_footer' => $request->boolean('show_in_footer'),
            ]);
            $this->saveTranslations($page, $request);
        });

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil ditambahkan.');
    }

    public function edit(Page $page)
    {
        $page->load('translations');

        return view('admin.pages.edit', [
            'page'    => $page,
            'locales' => config('sekalago.locales'),
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validated($request);

        // Slug tidak ikut berubah supaya tautan yang sudah tersebar tetap bekerja
        DB::transaction(function () use ($data, $request, $page) {
            $page->update([
                'template'       => $data['template'],
                'sort_order'     => $data['sort_order'] ?? 0,
                'is_active'      => $request->boolean('is_active'),
                'show_in_footer' => $request->boolean('show_in_footer'),
            ]);
            $this->saveTranslations($page, $request);
        });

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(Page $page)
    {
        $page->delete(); // terjemahan ikut terhapus (cascade)

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $rules = [
            'template'   => 'required|in:default,about',
            'sort_order' => 'nullable|integer|min:0',
        ];

        foreach (array_keys(config('sekalago.locales')) as $code) {
            if (in_array($code, self::REQUIRED_LOCALES, true)) {
                $rules["title.$code"] = 'required|string|max:150';
                $rules["body.$code"]  = 'required|string|max:20000';
            } else {
                // Opsional, tapi kalau salah satu diisi, pasangannya wajib ikut diisi
                $rules["title.$code"] = "nullable|required_with:body.$code|string|max:150";
                $rules["body.$code"]  = "nullable|required_with:title.$code|string|max:20000";
            }

            // Bagian tambahan template Tentang Kami: semuanya opsional
            $rules["extras.$code.stats_title"]          = 'nullable|string|max:120';
            $rules["extras.$code.stats.*.value"]        = 'nullable|string|max:20';
            $rules["extras.$code.stats.*.label"]        = 'nullable|string|max:80';
            $rules["extras.$code.highlights_title"]     = 'nullable|string|max:120';
            $rules["extras.$code.highlights.*.title"]   = 'nullable|string|max:100';
            $rules["extras.$code.highlights.*.text"]    = 'nullable|string|max:500';
        }

        return $request->validate($rules);
    }

    private function saveTranslations(Page $page, Request $request): void
    {
        foreach (array_keys(config('sekalago.locales')) as $locale) {
            $title = trim((string) $request->input("title.$locale"));
            $body  = trim((string) $request->input("body.$locale"));

            if ($title !== '' && $body !== '') {
                PageTranslation::updateOrCreate(
                    ['page_id' => $page->id, 'locale' => $locale],
                    [
                        'title'  => $title,
                        'body'   => $body,
                        // Bagian tambahan hanya disimpan untuk template Tentang Kami
                        'extras' => $page->template === 'about'
                            ? $this->cleanExtras((array) $request->input("extras.$locale", []))
                            : null,
                    ]
                );
            } elseif (! in_array($locale, self::REQUIRED_LOCALES, true)) {
                // dikosongkan -> hapus, supaya fallback ke English
                $page->translations()->where('locale', $locale)->delete();
            }
        }
    }

    /** Buang baris yang tidak lengkap; kalau semuanya kosong, simpan null. */
    private function cleanExtras(array $raw): ?array
    {
        $stats = collect($raw['stats'] ?? [])
            ->map(fn ($row) => [
                'value' => trim((string) ($row['value'] ?? '')),
                'label' => trim((string) ($row['label'] ?? '')),
            ])
            ->filter(fn ($row) => $row['value'] !== '' && $row['label'] !== '')
            ->values()
            ->all();

        $highlights = collect($raw['highlights'] ?? [])
            ->map(fn ($row) => [
                'title' => trim((string) ($row['title'] ?? '')),
                'text'  => trim((string) ($row['text'] ?? '')),
            ])
            ->filter(fn ($row) => $row['title'] !== '' && $row['text'] !== '')
            ->values()
            ->all();

        $extras = array_filter([
            'stats_title'      => trim((string) ($raw['stats_title'] ?? '')),
            'stats'            => $stats,
            'highlights_title' => trim((string) ($raw['highlights_title'] ?? '')),
            'highlights'       => $highlights,
        ], fn ($value) => $value !== '' && $value !== []);

        return $extras ?: null;
    }

    /** Slug unik dari judul English, misalnya "About Us" menjadi about-us. */
    private function newSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title), 60, '') ?: 'page';
        $slug = $base;

        for ($i = 2; Page::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}