@extends('layouts.app')

@section('content')
    @php
        $chip  = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold';
        $badge = $hotel->status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700';
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md';
        $label = 'text-[11px] font-bold text-slate-400 uppercase tracking-wider';
        $th    = 'px-6 py-3 font-bold';
        $td    = 'px-6 py-3';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.hotels.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.review_hotels') }}
        </a>

        {{-- HEADER --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words">{{ $hotel->name }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $hotel->partner->company_name }}</p>
            </div>
            <span class="{{ $chip }} {{ $badge }}">
                @if ($hotel->status === 'pending')<i class="fa-solid fa-hourglass-half"></i>@endif
                {{ __('ui.status_' . $hotel->status) }}
            </span>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- KOLOM KIRI --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- DETAIL --}}
                <div class="{{ $card }} p-6">
                    @if ($hotel->rejection_reason)
                        <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm">
                            <strong>{{ __('ui.rejection_reason_label') }}:</strong> {{ $hotel->rejection_reason }}
                        </div>
                    @endif

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                        @foreach ([
                            ['fa-handshake', __('ui.partner'), $hotel->partner->company_name],
                            ['fa-location-dot', __('ui.city'), $hotel->city],
                            ['fa-star', __('ui.star_rating'), $hotel->star_rating ?? '-'],
                            ['fa-map-pin', __('ui.address'), $hotel->address ?? '-'],
                        ] as [$icon, $name, $value])
                            <div class="{{ $icon === 'fa-map-pin' ? 'sm:col-span-2' : '' }}">
                                <dt class="{{ $label }} mb-1"><i class="fa-solid {{ $icon }} text-sagara-blue"></i> {{ $name }}</dt>
                                <dd class="font-semibold text-slate-800">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <div class="{{ $label }} mb-2"><i class="fa-solid fa-concierge-bell text-sagara-blue"></i> {{ __('ui.facilities') }}</div>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($hotel->facilities as $f)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">{{ $f->label }}</span>
                            @empty
                                <span class="text-sm text-slate-500">-</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ROOM TYPES --}}
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.room_types') }}</h2>
                    <div class="{{ $card }} overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                    <tr>
                                        <th class="{{ $th }}">{{ __('ui.room_name') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.price_per_night') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.capacity') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.total_rooms') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($hotel->roomTypes as $roomType)
                                        <tr>
                                            <td class="{{ $td }} font-bold text-slate-900">{{ $roomType->name }}</td>
                                            <td class="{{ $td }} font-bold text-slate-900 whitespace-nowrap">{{ format_price($roomType->base_price) }}</td>
                                            <td class="{{ $td }} text-slate-600">{{ $roomType->capacity }}</td>
                                            <td class="{{ $td }} text-slate-600">{{ $roomType->total_rooms }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">{{ __('ui.no_room_types') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TRANSLATIONS --}}
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.translations') }}</h2>
                    <div class="{{ $card }} overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                    <tr>
                                        <th class="{{ $th }}">{{ __('ui.language') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.hotel_name') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($hotel->translations as $t)
                                        <tr class="align-top">
                                            <td class="{{ $td }} whitespace-nowrap font-semibold text-slate-800">
                                                {{ config('sekalago.locales.' . $t->locale, $t->locale) }}
                                                @if ($t->is_auto_translated)
                                                    <div class="text-[11px] font-normal text-slate-400">({{ __('ui.auto_translated') }})</div>
                                                @endif
                                            </td>
                                            <td class="{{ $td }} min-w-32 text-slate-700">{{ $t->name }}</td>
                                            <td class="{{ $td }} min-w-56 text-slate-600">{{ $t->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: KEPUTUSAN --}}
            <div class="{{ $card }} p-6 space-y-5 lg:sticky lg:top-24">
                <h2 class="text-lg font-extrabold text-slate-900">{{ __('ui.decision') }}</h2>

                @if ($hotel->status !== 'approved')
                    <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">
                        @csrf
                        <button class="w-full px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                            <i class="fa-solid fa-check"></i> {{ __('ui.approve') }}
                        </button>
                    </form>
                @endif

                @if ($hotel->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.hotels.reject', $hotel) }}" class="space-y-3 {{ $hotel->status !== 'approved' ? 'pt-5 border-t border-slate-100' : '' }}">
                        @csrf
                        <textarea name="rejection_reason" rows="4" maxlength="500" required
                                  placeholder="{{ __('ui.rejection_reason_label') }}"
                                  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">{{ old('rejection_reason') }}</textarea>
                        <button class="w-full px-6 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition">
                            <i class="fa-solid fa-xmark"></i> {{ __('ui.reject') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- RIWAYAT AKTIVITAS --}}
        @include('partials.activity-timeline', ['activities' => $activities, 'showActor' => true])
    </section>
@endsection