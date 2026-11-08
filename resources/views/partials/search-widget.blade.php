@php
    $activeTab = $activeTab ?? ((old('pickup_date') || request('tab') === 'cars') ? 'cars' : 'hotels');
    $tabBase   = 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition';
@endphp

<div id="search-widget" data-active="{{ $activeTab }}">
    <div class="flex gap-2 mb-4" role="tablist">
        <button type="button" role="tab" data-tab="hotels" class="{{ $tabBase }}">
            <i class="fa-solid fa-hotel"></i> {{ __('ui.tab_hotels') }}
        </button>
        <button type="button" role="tab" data-tab="cars" class="{{ $tabBase }}">
            <i class="fa-solid fa-car"></i> {{ __('ui.tab_cars') }}
        </button>
    </div>

    {{-- Form hotel memakai $cities dan $params dari halaman --}}
    <div data-panel="hotels">
        @include('partials.search-form', ['action' => route('home') . '#results', 'tab' => 'hotels'])
    </div>

    {{-- Form mobil memakai daftar kota dan parameter armada sendiri --}}
    <div data-panel="cars" class="hidden">
        @include('partials.car-search-form', [
            'cities' => $carCities ?? collect(),
            'params' => $carParams ?? [],
            'action' => route('home') . '#results',
            'tab'    => 'cars',
        ])
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const widget = document.getElementById('search-widget');
        if (!widget) return;

        const tabs    = widget.querySelectorAll('[data-tab]');
        const panels  = widget.querySelectorAll('[data-panel]');
        const results = document.querySelectorAll('[data-results-for]');
        const onClasses  = ['bg-sagara-navy', 'text-white', 'shadow-md'];
        const offClasses = ['bg-slate-100', 'text-slate-600', 'hover:bg-slate-200'];

        function show(name, updateUrl) {
            tabs.forEach(tab => {
                const on = tab.dataset.tab === name;
                onClasses.forEach(c => tab.classList.toggle(c, on));
                offClasses.forEach(c => tab.classList.toggle(c, !on));
                tab.setAttribute('aria-selected', on ? 'true' : 'false');
            });

            panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.panel !== name));

            // Hasil pencarian hanya tampil untuk tab yang sedang aktif
            results.forEach(block => block.classList.toggle('hidden', block.dataset.resultsFor !== name));

            // Simpan pilihan tab di alamat (#cars) tanpa memuat ulang halaman
            if (updateUrl) {
                history.replaceState(null, '', name === 'cars'
                    ? window.location.pathname + window.location.search + '#cars'
                    : window.location.pathname + window.location.search);
            }
        }

        tabs.forEach(tab => tab.addEventListener('click', () => show(tab.dataset.tab, true)));

        // Saat dimuat: ikuti #cars di alamat, kalau tidak ikuti tab dari server
        show(window.location.hash === '#cars' ? 'cars' : widget.dataset.active, false);

        // Setelah mencari, gulung ke hasil
        if (new URLSearchParams(window.location.search).has('city')) {
            document.getElementById('results')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })();
</script>
@endpush