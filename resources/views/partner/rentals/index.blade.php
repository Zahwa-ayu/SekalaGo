@extends('layouts.app')

@section('content')
    @php
        $sections = [
            ['overdue',  'rentals_overdue',  $overdue],
            ['ongoing',  'rentals_ongoing',  $ongoing],
            ['upcoming', 'rentals_upcoming', $upcoming],
            ['returned', 'rentals_returned', $returned],
        ];
    @endphp

    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.rentals') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.rentals_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- PESANAN TERANCAM --}}
        @foreach ($conflicts as $conflict)
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 space-y-3">
                <div>
                    <h2 class="font-extrabold text-red-700">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        {{ __('ui.rental_conflict_title', ['vehicle' => $conflict['vehicle']->name]) }}
                    </h2>
                    <p class="text-sm text-red-700 mt-1">{{ __('ui.rental_conflict_hint') }}</p>
                </div>

                @foreach ($conflict['atRisk'] as $risk)
                    <div class="bg-white rounded-xl border border-red-100 p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <div class="text-sm">
                            <span class="font-mono text-xs text-slate-400">{{ $risk->booking_code }}</span>
                            <div class="font-bold text-slate-900">{{ $risk->guest_name }}</div>
                            <div class="text-xs text-slate-500">{{ $risk->check_in->format('d/m/Y') }} - {{ $risk->check_out->format('d/m/Y') }} &middot; {{ $risk->room_name }}</div>
                        </div>

                        <form method="POST" action="{{ route('partner.rentals.cancel', $risk) }}" class="flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input name="cancellation_reason" required maxlength="500" placeholder="{{ __('ui.rental_cancel_reason_placeholder') }}"
                                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:w-72 focus:outline-none focus:ring-2 focus:ring-red-300">
                            <button class="px-4 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition whitespace-nowrap">
                                {{ __('ui.rental_cancel_refund') }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endforeach

        {{-- DAFTAR PER TAHAP --}}
        @foreach ($sections as [$stage, $title, $items])
            <section>
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-500 mb-3">
                    {{ __('ui.' . $title) }}
                    <span class="ml-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px]">{{ $items->count() }}</span>
                </h2>

                @forelse ($items as $booking)
                    @include('partner.rentals._card', ['booking' => $booking, 'stage' => $stage, 'lateInfo' => $late[$booking->id] ?? null])
                @empty
                    <p class="text-sm text-slate-400">{{ __('ui.rentals_empty') }}</p>
                @endforelse
            </section>
        @endforeach
    </div>
@endsection