<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactInfoController extends Controller
{
    public function index(): View
    {
        // Jumlahnya kecil (footer hanya muat beberapa), jadi tanpa paginasi
        $contacts = ContactInfo::orderBy('order')->orderBy('id')->get();
        $types    = config('contact_types');

        return view('admin.contact-info.index', compact('contacts', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        ContactInfo::create($this->validated($request));

        return redirect()->route('admin.contact-info.index')->with('success', 'Kontak berhasil ditambahkan.');
    }

    public function update(Request $request, ContactInfo $contactInfo): RedirectResponse
    {
        $contactInfo->update($this->validated($request));

        return redirect()->route('admin.contact-info.index')->with('success', 'Kontak berhasil diperbarui.');
    }

    public function updateStatus(ContactInfo $contactInfo): RedirectResponse
    {
        $contactInfo->update(['is_active' => ! $contactInfo->is_active]);

        return back()->with('success', $contactInfo->is_active ? 'Kontak ditampilkan.' : 'Kontak disembunyikan.');
    }

    public function destroy(ContactInfo $contactInfo): RedirectResponse
    {
        $contactInfo->delete();

        return redirect()->route('admin.contact-info.index')->with('success', 'Kontak berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        // Aturan isian menyesuaikan tipe yang dipilih
        $valueRules = match ($request->input('type')) {
            'email'             => ['required', 'email:rfc', 'max:255'],
            'phone', 'whatsapp' => ['required', 'regex:/^\+?[0-9\s().-]{6,25}$/'],
            default             => ['required', 'string', 'max:255'],
        };

        $data = $request->validate([
            'type'  => ['required', Rule::in(array_keys(config('contact_types')))],
            'label' => ['nullable', 'string', 'max:100'],
            'value' => $valueRules,
            'order' => ['required', 'integer', 'min:0'],
        ]);

        return [
            'type'      => $data['type'],
            'label'     => filled($data['label'] ?? null) ? trim($data['label']) : null,
            'value'     => trim($data['value']),
            'order'     => $data['order'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}