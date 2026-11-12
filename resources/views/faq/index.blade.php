@extends('layouts.storefront')

@section('solid_nav', '1')

@section('content')
    <div class="bg-slate-50 min-h-screen">

        {{-- HEADER --}}
        <section class="bg-white border-b border-slate-200/80">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-center">
                <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-2">Pusat Bantuan</span>
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-900 leading-tight">Pertanyaan Umum</h1>
                <p class="text-slate-500 text-sm sm:text-base mt-3 max-w-xl mx-auto">
                    Temukan jawaban cepat seputar pemesanan, pembayaran, dan layanan SekalaGo.
                </p>
            </div>
        </section>

        {{-- TAB + ISI --}}
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-16">

            {{-- Tab switcher --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-1.5 grid grid-cols-2 gap-1.5" role="tablist">
                <button type="button" data-tab="customer" role="tab"
                        class="faq-tab py-3 rounded-xl text-sm font-extrabold flex items-center justify-center gap-2 transition duration-200">
                    <i class="fa-solid fa-suitcase-rolling"></i> Pelanggan
                </button>
                <button type="button" data-tab="corporate" role="tab"
                        class="faq-tab py-3 rounded-xl text-sm font-extrabold flex items-center justify-center gap-2 transition duration-200">
                    <i class="fa-solid fa-building"></i> Perusahaan
                </button>
            </div>

            {{-- Panel --}}
            <div class="mt-8">
                <div data-panel="customer" class="faq-panel">
                    @include('partials.faq-list', ['items' => $faqs->get('customer', collect())])
                </div>

                <div data-panel="corporate" class="faq-panel hidden">
                    @include('partials.faq-list', ['items' => $faqs->get('corporate', collect())])
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const tabs = document.querySelectorAll('.faq-tab');
            const panels = document.querySelectorAll('.faq-panel');
            const active = ['bg-sky-500', 'text-white', 'shadow-md', 'shadow-sky-500/25'];
            const idle = ['text-slate-500', 'hover:bg-slate-100'];

            function show(name) {
                tabs.forEach(function (t) {
                    const on = t.dataset.tab === name;
                    active.forEach(function (c) { t.classList.toggle(c, on); });
                    idle.forEach(function (c) { t.classList.toggle(c, !on); });
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                panels.forEach(function (p) {
                    p.classList.toggle('hidden', p.dataset.panel !== name);
                });
            }

            tabs.forEach(function (t) {
                t.addEventListener('click', function () { show(t.dataset.tab); });
            });

            // Bisa dibuka langsung: /faq#corporate
            show(location.hash === '#corporate' ? 'corporate' : 'customer');
        })();
    </script>
@endsection