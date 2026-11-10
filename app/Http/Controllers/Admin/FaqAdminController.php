<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqAdminController extends Controller
{
    // Bahasa yang bisa diisi admin. Tambah sesuai bahasa di navbar.
    private array $locales = [
        'id' => 'Indonesia',
        'en' => 'English',
    ];

    public function index()
    {
        $faqs = Faq::with('translations')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.create', ['locales' => $this->locales]);
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
            'locales' => $this->locales,
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
        return $request->validate([
            'category'    => 'required|string|max:50',
            'sort_order'  => 'nullable|integer|min:0',
            'question.id' => 'required|string|max:255', // Indonesia wajib
            'answer.id'   => 'required|string',
            'question.*'  => 'nullable|string|max:255',
            'answer.*'    => 'nullable|string',
        ]);
    }

    private function saveTranslations(Faq $faq, Request $request): void
    {
        foreach (array_keys($this->locales) as $locale) {
            $q = trim((string) $request->input("question.$locale"));
            $a = trim((string) $request->input("answer.$locale"));

            if ($q !== '' && $a !== '') {
                FaqTranslation::updateOrCreate(
                    ['faq_id' => $faq->id, 'locale' => $locale],
                    ['question' => $q, 'answer' => $a]
                );
            } elseif ($locale !== 'id') {
                // dikosongkan -> hapus, supaya fallback ke Indonesia
                $faq->translations()->where('locale', $locale)->delete();
            }
        }
    }
}