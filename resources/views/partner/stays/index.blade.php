@extends('layouts.app')

@section('content')
    @php
        // [kunci, judul, data, border kartu, ikon, warna ikon]
        $sections = [
            ['arrivals', 'stay_arrivals_today',    $arrivals, 'border-emerald-200', 'fa-door-open',          'bg-emerald-100 text-emerald-600'],
            ['tomorrow', 'stay_arrivals_tomorrow', $tomorrow, 'border-slate-100',   'fa-calendar-day',       'bg-amber-100 text-amber-600'],
            ['in_house', 'stay_in_house',          $inHouse,  'border-slate-100',   'fa-bed',                'bg-sky-100 text-sky-600'],
            ['recent',   'stay_recent',            $recent,   'border-slate-100',   'fa-clock-rotate-left',  'bg-slate-100 text-slate-600'],
        ];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.guests_page') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.guests_page_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- RINGKASAN (klik untuk lompat ke bagiannya) --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($sections as [$key, $title, $items, $border, $icon, $tone])
                <a href="#stay-{{ $key }}" class="bg-white rounded-2xl border border-slate-100 shadow-md hover:shadow-lg transition p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0 {{ $tone }}">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-slate-900 leading-none">{{ $items->count() }}</div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-1 leading-tight">{{ __('ui.' . $title) }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        @foreach ($sections as [$key, $title, $items, $border, $icon, $tone])
            <section id="stay-{{ $key }}" class="scroll-mt-24">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0 {{ $tone }}">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900">{{ __('ui.' . $title) }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">{{ $items->count() }}</span>
                </div>

                @forelse ($items as $booking)
                    @include('partner.stays._card', ['booking' => $booking, 'section' => $key, 'border' => $border])
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 px-6 py-8 text-center text-sm text-slate-400">
                        <i class="fa-solid {{ $icon }} text-2xl mb-2 block text-slate-300"></i>
                        {{ __('ui.rentals_empty') }}
                    </div>
                @endforelse
            </section>
        @endforeach
    </section>
@endsection