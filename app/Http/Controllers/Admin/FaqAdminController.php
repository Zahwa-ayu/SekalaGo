<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqAdminController extends Controller
{
    // Bahasa yang wajib diisi. Bahasa lain di config('sekalago.locales') opsional.
    private const REQUIRED_LOCALES = ['id', 'en'];

    public function index()
    {
        $faqs = Faq::with('translations')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.create', ['locales' => config('sekalago.locales')]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $faq = Faq::create([
                'category'   => $data['category'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active'  => $request->boolean('is_active'),
            ]);
            $this->saveTranslations($faq, $request);
        });

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function edit(Faq $faq)
    {
        $faq->load('translations');

        return view('admin.faqs.edit', [
            'faq'     => $faq,
            'locales' => config('sekalago.locales'),
        ]);
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request, $faq) {
            $faq->update([
                'category'   => $data['category'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active'  => $request->boolean('is_active'),
            ]);
            $this->saveTranslations($faq, $request);
        });

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete(); // translasi ikut terhapus (cascade)

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $rules = [
            'category'   => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ];

        foreach (array_keys(config('sekalago.locales')) as $code) {
            if (in_array($code, self::REQUIRED_LOCALES, true)) {
                $rules["question.$code"] = 'required|string|max:255';
                $rules["answer.$code"]   = 'required|string';
            } else {
                // Opsional, tapi kalau salah satu diisi, pasangannya wajib ikut diisi
                $rules["question.$code"] = "nullable|required_with:answer.$code|string|max:255";
                $rules["answer.$code"]   = "nullable|required_with:question.$code|string";
            }
        }

        return $request->validate($rules);
    }

    private function saveTranslations(Faq $faq, Request $request): void
    {
        foreach (array_keys(config('sekalago.locales')) as $locale) {
            $q = trim((string) $request->input("question.$locale"));
            $a = trim((string) $request->input("answer.$locale"));

            if ($q !== '' && $a !== '') {
                FaqTranslation::updateOrCreate(
                    ['faq_id' => $faq->id, 'locale' => $locale],
                    ['question' => $q, 'answer' => $a]
                );
            } elseif (! in_array($locale, self::REQUIRED_LOCALES, true)) {
                // dikosongkan -> hapus, supaya fallback ke bahasa wajib
                $faq->translations()->where('locale', $locale)->delete();
            }
        }
    }
}