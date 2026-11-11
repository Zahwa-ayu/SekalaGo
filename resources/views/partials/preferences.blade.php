@inject('currencyService', 'App\Services\CurrencyService')

{{-- Bahasa & mata uang dalam satu dropdown ikon bola. Tanpa tombol simpan: berubah langsung saat dipilih. --}}
<details class="pref-dd relative">
    <summary class="pref-btn nav-link-text list-none cursor-pointer select-none flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-white/10 transition"
             aria-label="{{ __('ui.language') }} / {{ __('ui.currency') }}">
        <i class="fa-solid fa-globe"></i>
        <span>{{ strtoupper(app()->getLocale()) }} · {{ $currencyService->current()->code }}</span>
        <i class="fa-solid fa-chevron-down text-[9px] opacity-70"></i>
    </summary>

    <form method="POST" action="{{ route('preferences.update') }}"
          class="mt-2 lg:absolute lg:right-0 lg:top-full lg:w-56 z-50 p-3 space-y-3 rounded-xl bg-white text-slate-800 border border-slate-200 shadow-xl">
        @csrf

        <label class="block">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-language mr-1 text-sky-600"></i> {{ __('ui.language') }}
            </span>
            <select name="locale" onchange="this.form.submit()"
                    class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-sky-500">
                @foreach (config('sekalago.locales') as $code => $label)
                    <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-coins mr-1 text-sky-600"></i> {{ __('ui.currency') }}
            </span>
            <select name="currency" onchange="this.form.submit()"
                    class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-sky-500">
                @foreach ($currencyService->all() as $c)
                    <option value="{{ $c->code }}" @selected($currencyService->current()->code === $c->code)>
                        {{ $c->code }} ({{ $c->symbol }})
                    </option>
                @endforeach
            </select>
        </label>

        <noscript><button class="w-full py-1.5 rounded-lg bg-sky-500 text-white text-xs font-bold">OK</button></noscript>
    </form>
</details>

@once
    @push('scripts')
        <script>
            // Tutup dropdown saat klik di luar atau tekan Esc
            document.addEventListener('click', (e) => {
                document.querySelectorAll('.pref-dd[open]').forEach((d) => { if (!d.contains(e.target)) d.removeAttribute('open'); });
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') document.querySelectorAll('.pref-dd[open]').forEach((d) => d.removeAttribute('open'));
            });
        </script>
    @endpush
@endonce