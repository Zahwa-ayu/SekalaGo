@extends('layouts.app')

@section('content')
    @php
        $photos    = $hotel->photos;
        $hasCoords = $hotel->latitude !== null && $hotel->longitude !== null;
        $mapQuery  = $hasCoords
            ? $hotel->latitude . ',' . $hotel->longitude
            : rawurlencode(trim($hotel->name . ' ' . $hotel->address . ' ' . $hotel->city));
        $chip  = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold';
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $input = 'w-full min-w-0 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sagara-blue';

        $minPrice  = $roomTypes->min('base_price');
        $openCount = $params ? $roomTypes->where('is_open', true)->count() : null;
        $backUrl   = $params
            ? route('hotels.search', $params + ['city' => $params['city'] ?? $hotel->city])
            : route('home');
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.search_results') }}
        </a>

        {{-- items-start: kolom kanan tidak ditarik setinggi kolom kiri, supaya sticky bekerja --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:items-start">

            {{-- ============ KOLOM KIRI ============ --}}
            <div class="lg:col-span-2 min-w-0 space-y-6">

                {{-- GALERI: foto utama + deretan thumbnail di bawahnya --}}
                <div class="space-y-3">
                    <div class="w-full rounded-3xl overflow-hidden bg-slate-200 aspect-[16/10]">
                        @if ($photos->isNotEmpty())
                            <img id="hotel-main-photo" src="{{ $photos->first()->url() }}" alt="{{ $hotel->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-sagara-navy to-sagara-blue text-white/70 text-6xl">
                                <i class="fa-solid fa-hotel"></i>
                            </div>
                        @endif
                    </div>

                    @if ($photos->count() > 1)
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach ($photos->take(6) as $photo)
                                <button type="button"
                                        onclick="document.getElementById('hotel-main-photo').src='{{ $photo->url() }}'"
                                        class="rounded-xl overflow-hidden aspect-[4/3] bg-slate-200 hover:ring-2 hover:ring-sagara-teal transition">
                                    <img src="{{ $photo->url() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- JUDUL + CHIP --}}
                <div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 break-words">{{ $hotel->name }}</h1>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @if ($hotel->star_rating)
                            <span class="{{ $chip }}">
                                <span class="text-sagara-gold">
                                    @for ($i = 0; $i < $hotel->star_rating; $i++)<i class="fa-solid fa-star"></i>@endfor
                                </span>
                            </span>
                        @endif
                        <span class="{{ $chip }}">
                            <i class="fa-solid fa-location-dot text-sagara-blue"></i>
                            {{ collect([$hotel->area, $hotel->city])->filter()->join(', ') }}
                        </span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-right-to-bracket text-sagara-blue"></i> {{ __('ui.check_in_from', ['time' => $hotel->checkInLabel()]) }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-right-from-bracket text-sagara-blue"></i> {{ __('ui.check_out_until', ['time' => $hotel->checkOutLabel()]) }}</span>
                    </div>
                </div>

                @if ($hotel->description)
                    <div class="{{ $card }}">
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line break-words">{{ $hotel->description }}</p>
                    </div>
                @endif

                @if ($hotel->facilities->isNotEmpty())
                    <div class="{{ $card }}">
                        <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.hotel_facilities') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($hotel->facilities as $facility)
                                <span class="{{ $chip }}"><i class="fa-solid {{ $facility->icon() }} text-sagara-blue"></i> {{ $facility->label }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- TIPE KAMAR --}}
                <div class="space-y-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">{{ __('ui.room_types') }}</h2>
                        @if ($params)
                            <p class="text-sm font-semibold text-slate-600 mt-1">
                                {{ __('ui.stay_summary', [
                                    'in' => \Illuminate\Support\Carbon::parse($params['check_in'])->format('d/m/Y'),
                                    'out' => \Illuminate\Support\Carbon::parse($params['check_out'])->format('d/m/Y'),
                                    'count' => $nights,
                                    'guests' => $params['guests'],
                                ]) }}
                            </p>
                        @else
                            <p class="text-sm text-slate-500 mt-1">{{ __('ui.pick_dates_hint') }}</p>
                        @endif
                    </div>

                    @forelse ($roomTypes as $roomType)
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden md:flex">
                            <div class="md:w-60 shrink-0 bg-slate-200 aspect-[4/3] md:aspect-auto relative">
                                @if ($roomType->photos->isNotEmpty())
                                    <img src="{{ $roomType->photos->first()->url() }}" alt="{{ $roomType->name }}" class="w-full h-full object-cover" loading="lazy">
                                    @if ($roomType->photos->count() > 1)
                                        <span class="absolute bottom-2 right-2 px-2.5 py-1 rounded-full bg-black/60 text-white text-[11px] font-bold">
                                            <i class="fa-solid fa-images"></i> {{ $roomType->photos->count() }}
                                        </span>
                                    @endif
                                @else
                                    <div class="w-full h-full min-h-40 flex items-center justify-center bg-gradient-to-br from-sky-100 to-sky-200 text-sagara-blue/40 text-4xl">
                                        <i class="fa-solid fa-bed"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 p-6 space-y-3">
                                <div>
                                    <h3 class="text-lg font-extrabold text-slate-900 break-words">{{ $roomType->name }}</h3>
                                    @if ($roomType->description)
                                        <p class="text-sm text-slate-600 mt-1 break-words">{{ $roomType->description }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="{{ $chip }}"><i class="fa-solid fa-bed text-sagara-blue"></i> {{ $roomType->bed_count }} &times; {{ __('ui.bed_type_' . $roomType->bed_type) }}</span>
                                    <span class="{{ $chip }}"><i class="fa-solid fa-user-group text-sagara-blue"></i> {{ $roomType->capacity }}</span>
                                    <span class="{{ $chip }}">
                                        <i class="fa-solid {{ $roomType->smoking_allowed ? 'fa-smoking' : 'fa-ban-smoking' }} text-sagara-blue"></i>
                                        {{ $roomType->smoking_allowed ? __('ui.smoking_yes') : __('ui.smoking_no') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ $roomType->meal_plan === 'room_only' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-700' }}">
                                        <i class="fa-solid fa-utensils"></i> {{ __('ui.meal_plan_' . $roomType->meal_plan) }}
                                    </span>
                                </div>

                                @if ($roomType->facilities->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($roomType->facilities as $facility)
                                            <span class="{{ $chip }}"><i class="fa-solid {{ $facility->icon() }} text-sagara-blue"></i> {{ $facility->label }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex flex-wrap items-end justify-between gap-3 pt-3 border-t border-slate-100">
                                    <div>
                                        <div class="text-xl font-black text-slate-900">{{ format_price($roomType->base_price) }}<span class="text-xs font-semibold text-slate-400">{{ __('ui.per_night') }}</span></div>
                                        @if ($params)
                                            <div class="text-xs text-slate-500">
                                                {{ __('ui.total_price') }}: <strong>{{ format_price($roomType->base_price * $nights) }}</strong>
                                                ({{ __('ui.for_nights', ['count' => $nights]) }})
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        @if ($params)
                                            <span class="text-xs font-bold {{ $roomType->is_open ? 'text-emerald-600' : 'text-red-500' }}">
                                                <i class="fa-solid {{ $roomType->is_open ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                                {{ $roomType->is_open ? __('ui.available') : __('ui.not_available') }}
                                            </span>
                                        @endif

                                        @if ($params && $roomType->is_open)
                                            <a href="{{ route('bookings.create', [
                                                'room_type' => $roomType->id,
                                                'check_in'  => $params['check_in'],
                                                'check_out' => $params['check_out'],
                                                'guests'    => $params['guests'],
                                            ]) }}"
                                               class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-sagara-blue to-sagara-teal text-white text-sm font-bold shadow-lg hover:from-sagara-navy hover:to-sagara-blue transition">
                                                {{ __('ui.book') }}
                                            </a>
                                        @else
                                            <button disabled class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 text-sm font-bold cursor-not-allowed">{{ __('ui.book') }}</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('ui.no_room_types') }}</p>
                    @endforelse
                </div>

                {{-- TEMPAT TERDEKAT --}}
                <div class="{{ $card }}">
                    <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.nearby_places') }}</h2>
                    @forelse ($hotel->nearbyPlaces as $place)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 last:border-0 text-sm">
                            <span class="text-slate-700 flex items-start gap-3 min-w-0">
                                <i class="fa-solid {{ $place->icon() }} text-sagara-blue w-5 mt-0.5 text-center shrink-0"></i>
                                <span class="break-words">
                                    {{ $place->name }}
                                    <span class="text-[11px] text-slate-400">({{ __('ui.nearby_type_' . $place->type) }})</span>
                                </span>
                            </span>
                            <span class="font-bold text-slate-900 shrink-0">{{ $place->distanceLabel() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('ui.no_nearby') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- ============ KOLOM KANAN ============
                 Seluruh kolom sticky, jadi kartu di dalamnya bergerak bersama dan tidak saling menimpa. --}}
            <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">
                <div class="{{ $card }} space-y-4">
                    @if ($minPrice !== null)
                        <div>
                            <span class="text-[11px] text-slate-400 block">{{ __('ui.from_price') }}</span>
                            <div class="text-2xl font-black text-slate-900">
                                {{ format_price($minPrice) }}<span class="text-sm font-semibold text-slate-400">{{ __('ui.per_night') }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($params)
                        <div class="text-sm text-slate-600">
                            {{ __('ui.stay_summary', [
                                'in' => \Illuminate\Support\Carbon::parse($params['check_in'])->format('d/m/Y'),
                                'out' => \Illuminate\Support\Carbon::parse($params['check_out'])->format('d/m/Y'),
                                'count' => $nights,
                                'guests' => $params['guests'],
                            ]) }}
                        </div>

                        <div class="text-sm font-bold {{ $openCount > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            <i class="fa-solid {{ $openCount > 0 ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                            {{ $openCount > 0 ? __('ui.available') : __('ui.not_available') }}
                            @if ($openCount > 0)
                                <span class="text-xs font-semibold text-slate-500">({{ $openCount }} / {{ $roomTypes->count() }} {{ __('ui.room_types') }})</span>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-500">{{ __('ui.pick_dates_hint') }}</p>
                    @endif

                    {{-- Ubah tanggal & jumlah tamu --}}
                    <form method="GET" action="{{ route('hotels.show', $hotel) }}" class="grid grid-cols-2 gap-2">
                        @if (! empty($params['city']))
                            <input type="hidden" name="city" value="{{ $params['city'] }}">
                        @endif

                        <input type="date" name="check_in" required min="{{ today()->toDateString() }}"
                               value="{{ $params['check_in'] ?? today()->addDay()->toDateString() }}"
                               class="{{ $input }}">
                        <input type="date" name="check_out" required min="{{ today()->addDay()->toDateString() }}"
                               value="{{ $params['check_out'] ?? today()->addDays(2)->toDateString() }}"
                               class="{{ $input }}">

                        <div class="col-span-2 relative">
                            <i class="fa-solid fa-user-group absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <input type="number" name="guests" min="1" max="20" value="{{ $params['guests'] ?? 2 }}"
                                   class="{{ $input }} pl-9">
                        </div>

                        <button class="col-span-2 px-4 py-2 rounded-xl bg-sky-100 text-sagara-blue text-xs font-bold hover:bg-sky-200 transition">
                            {{ __('ui.check_availability') }}
                        </button>
                    </form>

                    @if ($errors->any())
                        <ul class="text-xs text-red-600 list-disc list-inside">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    @endif
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.hotel_info') }}</h2>
                    <div class="space-y-2 text-sm text-slate-700">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-right-to-bracket text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                            <span>{{ __('ui.check_in_from', ['time' => $hotel->checkInLabel()]) }}</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-right-from-bracket text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                            <span>{{ __('ui.check_out_until', ['time' => $hotel->checkOutLabel()]) }}</span>
                        </div>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.location') }}</h2>

                    <p class="text-sm text-slate-700 flex items-start gap-3 mb-3">
                        <i class="fa-solid fa-location-dot text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                        <span class="break-words">{{ collect([$hotel->address, $hotel->area, $hotel->city])->filter()->join(', ') }}</span>
                    </p>

                    @if ($hasCoords)
                        @php
                            $d    = 0.006;
                            $bbox = ($hotel->longitude - $d) . ',' . ($hotel->latitude - $d) . ',' . ($hotel->longitude + $d) . ',' . ($hotel->latitude + $d);
                        @endphp
                        <iframe class="w-full h-56 rounded-2xl border-0" loading="lazy"
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&layer=mapnik&marker={{ $hotel->latitude }},{{ $hotel->longitude }}"></iframe>
                    @endif

                    <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener"
                       class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:underline">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('ui.view_map') }}
                    </a>
                </div>
            </aside>
        </div>
    </section>
@endsection