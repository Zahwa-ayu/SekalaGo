@php
    $page  = $page ?? null;
    $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
    $lbl   = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    $chip  = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition';

    // Indonesia dan English wajib, selalu tampil. Bahasa lain tampil hanya jika sudah ada isinya
    // (data lama / old input); selain itu ditambahkan lewat tombol "Tambahkan bahasa lainnya".
    $requiredLocales = ['id', 'en'];
    $isRequired = fn ($code) => in_array($code, $requiredLocales, true);

    $filled = [];
    foreach ($locales as $code => $name) {
        $tr = $page?->translations->firstWhere('locale', $code);
        $filled[$code] = filled(old("title.$code", $tr->title ?? ''))
            || filled(old("body.$code", $tr->body ?? ''));
    }
    $shown = fn ($code) => $isRequired($code) || $filled[$code];
    $hasMore = collect($locales)->keys()->contains(fn ($code) => ! $shown($code));

    // Tampilan halaman: 'default' (teks biasa) atau 'about' (Tentang Kami berbagian)
    $template = old('template', $page->template ?? 'default');

    // Checkbox: setelah validasi gagal, ikuti isian terakhir (kosong = tidak dicentang)
    $checked = fn (string $key, bool $default) => session()->hasOldInput() ? (bool) old($key) : $default;
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
            @php $tr = $page?->translations->firstWhere('locale', $code); @endphp
            <div data-locale-card="{{ $code }}" class="{{ $card }} space-y-4 {{ $shown($code) ? '' : 'hidden' }}">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-extrabold text-slate-900">
                        {{ $name }} ({{ strtoupper($code) }})
                        @if ($isRequired($code))
                            <span class="ml-1 text-[11px] font-bold text-red-600">wajib</span>
                        @else
                            <span class="ml-1 text-[11px] font-bold text-slate-400">opsional</span>
                        @endif
                    </h3>

                    @if (! $isRequired($code) && ! $filled[$code])
                        <button type="button" data-remove-locale="{{ $code }}"
                                class="text-xs font-bold text-slate-400 hover:text-red-600 transition" aria-label="Tutup {{ $name }}">
                            <i class="fa-solid fa-xmark"></i> Tutup
                        </button>
                    @endif
                </div>

                <div>
                    <label class="{{ $lbl }}">Judul</label>
                    <input type="text" name="title[{{ $code }}]" value="{{ old("title.$code", $tr->title ?? '') }}"
                           maxlength="150" @required($isRequired($code)) class="{{ $input }}">
                </div>

                <div>
                    <label class="{{ $lbl }}">Isi</label>
                    <textarea name="body[{{ $code }}]" rows="12" @required($isRequired($code)) class="{{ $input }}">{{ old("body.$code", $tr->body ?? '') }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Baris kosong memisahkan paragraf. Untuk Tentang Kami, paragraf pertama tampil sebagai pengantar di bagian atas.</p>
                </div>

                {{-- BAGIAN TAMBAHAN (hanya untuk tampilan Tentang Kami) --}}
                @php $ex = $tr?->extras ?? []; @endphp
                <div data-about-only class="space-y-4 pt-4 border-t border-slate-100 {{ $template === 'about' ? '' : 'hidden' }}">
                    <p class="text-xs text-slate-500">Bagian tambahan Tentang Kami. Baris yang tidak diisi lengkap tidak ditampilkan.</p>

                    <div>
                        <label class="{{ $lbl }}">Judul bagian angka</label>
                        <input type="text" name="extras[{{ $code }}][stats_title]" maxlength="120"
                               value="{{ old("extras.$code.stats_title", $ex['stats_title'] ?? '') }}" class="{{ $input }}">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="grid grid-cols-[90px_1fr] gap-2">
                                <input type="text" name="extras[{{ $code }}][stats][{{ $i }}][value]" maxlength="20" placeholder="100+"
                                       value="{{ old("extras.$code.stats.$i.value", $ex['stats'][$i]['value'] ?? '') }}" class="{{ $input }}">
                                <input type="text" name="extras[{{ $code }}][stats][{{ $i }}][label]" maxlength="80" placeholder="Keterangan angka"
                                       value="{{ old("extras.$code.stats.$i.label", $ex['stats'][$i]['label'] ?? '') }}" class="{{ $input }}">
                            </div>
                        @endfor
                    </div>

                    <div>
                        <label class="{{ $lbl }}">Judul bagian keunggulan</label>
                        <input type="text" name="extras[{{ $code }}][highlights_title]" maxlength="120"
                               value="{{ old("extras.$code.highlights_title", $ex['highlights_title'] ?? '') }}" class="{{ $input }}">
                    </div>
                    <div class="space-y-3">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="space-y-2">
                                <input type="text" name="extras[{{ $code }}][highlights][{{ $i }}][title]" maxlength="100" placeholder="Judul poin {{ $i + 1 }}"
                                       value="{{ old("extras.$code.highlights.$i.title", $ex['highlights'][$i]['title'] ?? '') }}" class="{{ $input }}">
                                <textarea name="extras[{{ $code }}][highlights][{{ $i }}][text]" rows="2" maxlength="500" placeholder="Penjelasan singkat poin {{ $i + 1 }}"
                                          class="{{ $input }}">{{ old("extras.$code.highlights.$i.text", $ex['highlights'][$i]['text'] ?? '') }}</textarea>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @endforeach

        {{-- TAMBAHKAN BAHASA LAINNYA --}}
        <div id="page-lang-picker" class="{{ $hasMore ? '' : 'hidden' }}">
            <button type="button" id="page-lang-toggle"
                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 rounded-3xl border-2 border-dashed border-slate-200 text-sm font-bold text-slate-500 hover:border-sagara-blue hover:text-sagara-blue transition">
                <i class="fa-solid fa-language"></i> Tambahkan bahasa lainnya
            </button>

            <div id="page-lang-menu" class="hidden mt-3">
                <div class="flex flex-wrap gap-2">
                    @foreach ($locales as $code => $name)
                        @if (! $isRequired($code))
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
        @if ($page)
            <div>
                <label class="{{ $lbl }}">Alamat halaman</label>
                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" rel="noopener"
                   class="text-xs font-mono text-sagara-blue hover:underline break-all">/page/{{ $page->slug }}</a>
                <p class="text-[11px] text-slate-400 mt-1">Tidak berubah walau judul diganti.</p>
            </div>
        @else
            <p class="text-xs text-slate-500">Alamat halaman dibuat otomatis dari judul English, misalnya "About Us" menjadi <span class="font-mono">/page/about-us</span>.</p>
        @endif

        <div>
            <label class="{{ $lbl }}">Tampilan halaman</label>
            <select name="template" id="page-template" class="{{ $input }}">
                <option value="default" @selected($template === 'default')>Teks biasa (Syarat, Privasi, dll.)</option>
                <option value="about" @selected($template === 'about')>Tentang Kami (berbagian)</option>
            </select>
        </div>

        <div>
            <label class="{{ $lbl }}">Urutan</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="{{ $input }}">
        </div>

        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600 cursor-pointer">
            <input type="checkbox" name="show_in_footer" value="1" @checked($checked('show_in_footer', $page->show_in_footer ?? true)) class="rounded border-slate-300 text-sagara-blue">
            Tampil di footer
        </label>

        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" @checked($checked('is_active', $page->is_active ?? true)) class="rounded border-slate-300 text-sagara-blue">
            Tampilkan di website
        </label>

        <div class="space-y-2 pt-5 border-t border-slate-100">
            <button class="w-full px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                <i class="fa-solid fa-check"></i> {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.pages.index') }}" class="block w-full text-center px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                Batal
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        // Bagian tambahan Tentang Kami hanya tampil saat tampilan "Tentang Kami" dipilih
        const tpl = document.getElementById('page-template');
        if (tpl) {
            const syncTemplate = () => document.querySelectorAll('[data-about-only]')
                .forEach((el) => el.classList.toggle('hidden', tpl.value !== 'about'));
            tpl.addEventListener('change', syncTemplate);
        }

        const picker = document.getElementById('page-lang-picker');
        const toggle = document.getElementById('page-lang-toggle');
        const menu   = document.getElementById('page-lang-menu');
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