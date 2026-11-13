@php
    $faq   = $faq ?? null;
    $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
    $lbl   = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    $chip  = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition';

    // Bahasa Indonesia selalu tampil. Bahasa lain tampil hanya jika sudah ada isinya
    // (data lama / old input); selain itu ditambahkan lewat tombol "Tambahkan bahasa lainnya".
    $filled = [];
    foreach ($locales as $code => $name) {
        $tr = $faq?->translations->firstWhere('locale', $code);
        $filled[$code] = filled(old("question.$code", $tr->question ?? ''))
            || filled(old("answer.$code", $tr->answer ?? ''));
    }
    $shown = fn ($code) => $code === 'id' || $filled[$code];
    $hasMore = collect($locales)->keys()->contains(fn ($code) => ! $shown($code));
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">
    {{-- KOLOM KIRI: ISI PER BAHASA --}}
    <div class="lg:col-span-2 space-y-6">
        @foreach ($locales as $code => $name)
            @php $tr = $faq?->translations->firstWhere('locale', $code); @endphp
            <div data-locale-card="{{ $code }}" class="{{ $card }} space-y-4 {{ $shown($code) ? '' : 'hidden' }}">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-extrabold text-slate-900">
                        {{ $name }} ({{ strtoupper($code) }})
                        @if ($code === 'id')
                            <span class="ml-1 text-[11px] font-bold text-red-600">wajib</span>
                        @else
                            <span class="ml-1 text-[11px] font-bold text-slate-400">opsional</span>
                        @endif
                    </h3>

                    @if ($code !== 'id' && ! $filled[$code])
                        <button type="button" data-remove-locale="{{ $code }}"
                                class="text-xs font-bold text-slate-400 hover:text-red-600 transition" aria-label="Tutup {{ $name }}">
                            <i class="fa-solid fa-xmark"></i> Tutup
                        </button>
                    @endif
                </div>

                <div>
                    <label class="{{ $lbl }}">Pertanyaan</label>
                    <input type="text" name="question[{{ $code }}]" value="{{ old("question.$code", $tr->question ?? '') }}"
                           class="{{ $input }}">
                </div>

                <div>
                    <label class="{{ $lbl }}">Jawaban</label>
                    <textarea name="answer[{{ $code }}]" rows="4" class="{{ $input }}">{{ old("answer.$code", $tr->answer ?? '') }}</textarea>
                </div>
            </div>
        @endforeach

        {{-- TAMBAHKAN BAHASA LAINNYA --}}
        <div id="faq-lang-picker" class="{{ $hasMore ? '' : 'hidden' }}">
            <button type="button" id="faq-lang-toggle"
                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 rounded-3xl border-2 border-dashed border-slate-200 text-sm font-bold text-slate-500 hover:border-sagara-blue hover:text-sagara-blue transition">
                <i class="fa-solid fa-language"></i> Tambahkan bahasa lainnya
            </button>

            <div id="faq-lang-menu" class="hidden mt-3">
                <div class="flex flex-wrap gap-2">
                    @foreach ($locales as $code => $name)
                        @if ($code !== 'id')
                            <button type="button" data-add-locale="{{ $code }}" class="{{ $chip }} {{ $shown($code) ? 'hidden' : '' }}">
                                <i class="fa-solid fa-plus"></i> {{ $name }} ({{ strtoupper($code) }})
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: PENGATURAN + AKSI --}}
    <div class="{{ $card }} space-y-5 lg:sticky lg:top-24">
        <div>
            <label class="{{ $lbl }}">Kategori</label>
            <select name="category" class="{{ $input }}">
                @foreach (['partner' => 'Partner', 'customer' => 'Customer', 'corporate' => 'Corporate'] as $val => $text)
                    <option value="{{ $val }}" @selected(old('category', $faq->category ?? 'partner') === $val)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $lbl }}">Urutan</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" class="{{ $input }}">
        </div>

        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true)) class="rounded border-slate-300 text-sagara-blue">
            Tampilkan di website
        </label>

        <div class="space-y-2 pt-5 border-t border-slate-100">
            <button class="w-full px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                <i class="fa-solid fa-check"></i> {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.faqs.index') }}" class="block w-full text-center px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                Batal
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        const picker = document.getElementById('faq-lang-picker');
        const toggle = document.getElementById('faq-lang-toggle');
        const menu   = document.getElementById('faq-lang-menu');
        if (!picker || !toggle || !menu) return;

        const addButtons = () => Array.from(document.querySelectorAll('[data-add-locale]'));
        const cardOf     = (code) => document.querySelector('[data-locale-card="' + code + '"]');

        // Sembunyikan tombol utama kalau semua bahasa sudah ditambahkan
        const refresh = () => {
            const remaining = addButtons().some((b) => !b.classList.contains('hidden'));
            picker.classList.toggle('hidden', !remaining);
            if (!remaining) menu.classList.add('hidden');
        };

        toggle.addEventListener('click', () => menu.classList.toggle('hidden'));

        // Pilih bahasa -> kolom input muncul
        addButtons().forEach((btn) => {
            btn.addEventListener('click', () => {
                const card = cardOf(btn.dataset.addLocale);
                if (!card) return;
                card.classList.remove('hidden');
                btn.classList.add('hidden');
                const first = card.querySelector('input');
                if (first) first.focus();
                refresh();
            });
        });

        // Tutup bahasa yang baru ditambahkan (isi dikosongkan)
        document.querySelectorAll('[data-remove-locale]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const code = btn.dataset.removeLocale;
                const card = cardOf(code);
                if (!card) return;
                card.querySelectorAll('input, textarea').forEach((el) => { el.value = ''; });
                card.classList.add('hidden');
                const add = document.querySelector('[data-add-locale="' + code + '"]');
                if (add) add.classList.remove('hidden');
                refresh();
            });
        });
    })();
</script>