@extends('layouts.app')

@section('content')
    @php
        $sections = [
            ['arrivals', 'stay_arrivals_today', $arrivals, 'border-emerald-200'],
            ['tomorrow', 'stay_arrivals_tomorrow', $tomorrow, 'border-slate-100'],
            ['in_house', 'stay_in_house', $inHouse, 'border-slate-100'],
            ['recent', 'stay_recent', $recent, 'border-slate-100'],
        ];
    @endphp

    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.guests_page') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.guests_page_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        @foreach ($sections as [$key, $title, $items, $border])
            <section>
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-500 mb-3">
                    {{ __('ui.' . $title) }}
                    <span class="ml-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px]">{{ $items->count() }}</span>
                </h2>

                @forelse ($items as $booking)
                    @include('partner.stays._card', ['booking' => $booking, 'section' => $key, 'border' => $border])
                @empty
                    <p class="text-sm text-slate-400">{{ __('ui.rentals_empty') }}</p>
                @endforelse
            </section>
        @endforeach
    </div>
@endsection