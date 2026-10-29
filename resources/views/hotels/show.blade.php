@extends('layouts.app')

@section('content')
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <a href="{{ $params ? route('hotels.search', $params + ['city' => $params['city'] ?? $hotel->city]) : route('home') }}"
           class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.search_results') }}
        </a>

        {{-- HEADER HOTEL --}}
        <div class="bg-gradient-to-r from-sagara-navy via-sagara-blue to-sagara-navy rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <h1 class="text-2xl sm:text-3xl font-extrabold">
                {{ $hotel->name }}
                @if ($hotel->star_rating)
                    <span class="text-base font-bold text-amber-300 whitespace-nowrap"><i class="fa-solid fa-star"></i> {{ $hotel->star_rating }}</span>
                @endif
            </h1>
            <p class="text-sky-200 text-sm mt-2 flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-sagara-teal"></i>
                {{ $hotel->city }}@if ($hotel->address), {{ $hotel->address }}@endif
            </p>

            @if ($params)
                <p class="text-sm mt-4 pt-4 border-t border-white/15 flex items-start gap-2">
                    <i class="fa-solid fa-calendar-days text-sagara-teal mt-0.5"></i>
                    {{ __('ui.stay_summary', [
                        'in' => \Illuminate\Support\Carbon::parse($params['check_in'])->format('d/m/Y'),
                        'out' => \Illuminate\Support\Carbon::parse($params['check_out'])->format('d/m/Y'),
                        'count' => $nights,
                        'guests' => $params['guests'],
                    ]) }}
                </p>
            @endif
        </div>

        @unless ($params)
            <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i> {{ __('ui.pick_dates_hint') }}
            </div>
        @endunless

        {{-- DESKRIPSI & FASILITAS --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            @if ($hotel->description)
                <p class="text-sm text-slate-600 leading-relaxed mb-5">{{ $hotel->description }}</p>
            @endif

            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                <i class="fa-solid fa-concierge-bell text-sagara-blue"></i> {{ __('ui.facilities') }}
            </div>
            @forelse ($hotel->facilities as $f)
                <span class="inline-block px-3 py-1 mr-1 mb-1 text-xs font-semibold rounded-full bg-sky-100 text-sagara-blue">{{ $f->label }}</span>
            @empty
                <span class="text-sm text-slate-500">-</span>
            @endforelse
        </div>

        {{-- TIPE KAMAR --}}
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.room_types') }}</h2>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-4 font-bold">{{ __('ui.room_name') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.capacity') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.price_per_night') }}</th>
                                @if ($params)
                                    <th class="px-6 py-4 font-bold">{{ __('ui.total_price') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                                @endif
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($roomTypes as $roomType)
                                <tr class="hover:bg-sky-50/50 transition align-top">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $roomType->name }}</div>
                                        @if ($roomType->description)
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $roomType->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap"><i class="fa-solid fa-users text-slate-400"></i> {{ $roomType->capacity }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($roomType->base_price) }}</td>
                                    @if ($params)
                                        <td class="px-6 py-4 font-bold text-sagara-blue whitespace-nowrap">{{ format_price($roomType->base_price * $nights) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $roomType->is_open ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $roomType->is_open ? __('ui.available') : __('ui.not_available') }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 text-right">
                                        @if ($params && $roomType->is_open)
                                            <a href="{{ route('bookings.create', [
                                                'room_type' => $roomType->id,
                                                'check_in' => $params['check_in'],
                                                'check_out' => $params['check_out'],
                                                'guests' => $params['guests'],
                                            ]) }}"
                                               class="inline-block px-4 py-2 text-xs font-bold bg-sagara-blue hover:bg-sagara-navy text-white rounded-xl transition">
                                                {{ __('ui.book') }}
                                            </a>
                                        @else
                                            <button disabled class="px-4 py-2 text-xs font-bold bg-slate-100 text-slate-400 rounded-xl cursor-not-allowed">{{ __('ui.book') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">{{ __('ui.no_room_types') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection