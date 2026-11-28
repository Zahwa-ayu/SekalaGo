@php
    // Urutan prioritas kontak bantuan
    $priority = ['whatsapp', 'phone', 'email'];

    $helpContact = \App\Models\ContactInfo::active()
        ->whereIn('type', $priority)
        ->orderBy('order')
        ->orderBy('id')
        ->get()
        ->filter(fn ($contact) => filled($contact->href))
        // sortBy mempertahankan kolom Urutan untuk tipe yang sama
        ->sortBy(fn ($contact) => array_search($contact->type, $priority))
        ->first();
@endphp

@if ($helpContact)
    <div class="p-4 rounded-2xl bg-black/30 backdrop-blur-md border border-white/15 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-info text-sky-300 text-base"></i>
            <span class="text-xs font-semibold text-white/90">Butuh bantuan pendaftaran?</span>
        </div>
        <a href="{{ $helpContact->href }}"
           @if (str_starts_with($helpContact->href, 'http')) target="_blank" rel="noopener noreferrer" @endif
           class="text-xs font-extrabold text-sky-300 hover:text-sky-200 hover:underline flex items-center gap-1.5">
            <i class="{{ $helpContact->icon }}"></i>
            Hubungi Kami
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>
@endif