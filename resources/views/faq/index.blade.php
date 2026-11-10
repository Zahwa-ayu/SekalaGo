@extends('layouts.storefront')

@section('solid_nav', '1')

@section('content')
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="text-center mb-10">
            <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-2">Bantuan</span>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900">Pertanyaan Umum</h1>
        </div>

        <div class="space-y-10">
            <div>
                <h2 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-suitcase-rolling text-sky-600"></i> Pelanggan
                </h2>
                @include('partials.faq-list', ['items' => $faqs->get('customer', collect())])
            </div>

            <div>
                <h2 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-building text-sky-600"></i> Perusahaan (Corporate)
                </h2>
                @include('partials.faq-list', ['items' => $faqs->get('corporate', collect())])
            </div>
        </div>
    </section>
@endsection