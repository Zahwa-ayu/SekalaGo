@extends('layouts.app')

@section('content')
    @php
        $locales  = config('sekalago.locales');
        $partner  = $hotel->partner;
        $map      = ($hotel->latitude !== null && $hotel->longitude !== null)
            ? 'https://www.google.com/maps?q=' . $hotel->latitude . ',' . $hotel->longitude : null;
        $statusTone = [
            'pending'  => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-red-100 text-red-700',
        ][$hotel->status] ?? 'bg-slate-100 text-slate-600';

        $localeName = fn ($code) => (string) (is_array($n = config('sekalago.locales.' . $code, $code)) ? ($n['name'] ?? $code) : $n);
        $time       = fn ($value) => $value ? substr((string) $value, 0, 5) : '-';
        $distance   = fn ($km) => $km === null ? '-' : rtrim(rtrim(number_format((float) $km, 1, '.', ''), '0'), '.') . ' km';

        $checks = [
            ['review_check_cover', $hotel->photos->isNotEmpty()],
            ['review_check_names', $hotel->translations->filter(fn ($t) => filled($t->name))->count() >= count($locales)],
            ['review_check_address', filled($hotel->address) && filled($hotel->city)],
            ['review_check_coordinates', $hotel->latitude !== null && $hotel->longitude !== null],
            ['review_check_rooms', $hotel->roomTypes->isNotEmpty()],
            ['review_check_room_photos', $hotel->roomTypes->isNotEmpty() && $hotel->roomTypes->every(fn ($r) => $r->photos->isNotEmpty())],
            ['review_check_nearby', $hotel->nearbyPlaces->isNotEmpty()],
            ['review_check_mou', (bool) $partner?->hasSignedAgreement()],
        ];

        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4';
        $title = 'text-[11px] font-bold uppercase tracking-wider text-slate-400';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('admin.hotels.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.review_hotels') }}
            </a>
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $hotel->name }}</h1>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusTone }}">{{ __('ui.status_' . $hotel->status) }}</span>
                @if ($hotel->star_rating)
                    <span class="text-amber-400 text-sm">@for ($i = 0; $i < $hotel->star_rating; $i++)<i class="fa-solid fa-star"></i>@endfor</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">{{ $hotel->area ? $hotel->area . ', ' : '' }}{{ $hotel->city }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">

            {{-- ============ KOLOM UTAMA ============ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- FOTO --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $title }}">{{ __('ui.photos') }} ({{ $hotel->photos->count() }})</h2>

                    @if ($hotel->photos->isEmpty())
                        <p class="text-sm text-slate-400">{{ __('ui.no_photos') }}</p>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach ($hotel->photos as $photo)
                                <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="relative block aspect-[4/3] rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                                    <img src="{{ $photo->url() }}" alt="{{ $photo->caption }}" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                                    @if ($photo->is_cover)
                                        <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-sky-500 text-white text-[10px] font-bold">{{ __('ui.cover') }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- INFORMASI DASAR --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $title }}">{{ __('ui.review_basic_info') }}</h2>

                    <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                        <div><dt class="text-slate-500">{{ __('ui.city_name') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $hotel->city }}@if ($hotel->cityMaster?->province), {{ $hotel->cityMaster->province }}@endif</dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.area') }}</dt><dd class="font-semibold text-slate-900">{{ $hotel->area ?: '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('ui.address') }}</dt><dd class="font-semibold text-slate-900">{{ $hotel->address ?: '-' }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.latitude') }} / {{ __('ui.longitude') }}</dt>
                            <dd class="font-semibold text-slate-900 font-mono text-xs">
                                @if ($map){{ $hotel->latitude }}, {{ $hotel->longitude }}
                                    <a href="{{ $map }}" target="_blank" rel="noopener" class="ml-2 font-sans text-sky-600 hover:underline"><i class="fa-solid fa-map-location-dot"></i> {{ __('ui.review_open_map') }}</a>
                                @else - @endif
                            </dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.star_rating') }}</dt><dd class="font-semibold text-slate-900">{{ $hotel->star_rating ?: '-' }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.check_in_time') }}</dt><dd class="font-semibold text-slate-900">{{ $time($hotel->check_in_time) }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.check_out_time') }}</dt><dd class="font-semibold text-slate-900">{{ $time($hotel->check_out_time) }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.review_source_language') }}</dt><dd class="font-semibold text-slate-900">{{ $localeName($hotel->source_locale) }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('ui.review_submitted') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $hotel->created_at->format('d/m/Y H:i') }}
                                <span class="text-xs font-normal text-slate-400">({{ __('ui.review_updated', ['date' => $hotel->updated_at->format('d/m/Y H:i')]) }})</span></dd></div>
                    </dl>

                    @if ($hotel->rejection_reason)
                        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                            <strong>{{ __('ui.rejection_reason_label') }}:</strong> {{ $hotel->rejection_reason }}
                        </div>
                    @endif
                </div>

                {{-- NAMA DAN DESKRIPSI --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $title }}">{{ __('ui.translations') }}</h2>

                    @foreach ($hotel->translations as $t)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold uppercase">{{ $localeName($t->locale) }}</span>
                                @if ($t->is_auto_translated)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">{{ __('ui.auto_translated') }}</span>
                                @endif
                            </div>
                            <div class="font-bold text-slate-900">{{ $t->name }}</div>
                            <p class="text-sm text-slate-600 whitespace-pre-line">{{ $t->description ?: '-' }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- FASILITAS DAN TEMPAT TERDEKAT --}}
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="{{ $card }}">
                        <h2 class="{{ $title }}">{{ __('ui.facilities') }} ({{ $hotel->facilities->count() }})</h2>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($hotel->facilities as $facility)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100 text-xs font-semibold">
                                    <i class="fa-solid {{ $facility->icon() }}"></i> {{ $facility->label }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">-</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="{{ $card }}">
                        <h2 class="{{ $title }}">{{ __('ui.nearby_places') }} ({{ $hotel->nearbyPlaces->count() }})</h2>
                        <ul class="space-y-1.5 text-sm">
                            @forelse ($hotel->nearbyPlaces as $place)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="text-slate-800">{{ $place->name }}
                                        <span class="text-[11px] text-slate-400">&middot; {{ __('ui.nearby_type_' . $place->type) }}</span></span>
                                    <span class="font-semibold text-slate-600 whitespace-nowrap">{{ $distance($place->distance_km) }}</span>
                                </li>
                            @empty
                                <li class="text-slate-400">-</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- TIPE KAMAR --}}
                <div class="space-y-4">
                    <h2 class="{{ $title }}">{{ __('ui.room_types') }} ({{ $hotel->roomTypes->count() }})</h2>

                    @forelse ($hotel->roomTypes as $roomType)
                        <div class="{{ $card }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="font-extrabold text-slate-900 text-lg">{{ $roomType->name }}</div>
                                    <div class="text-xs text-slate-500">{{ __('ui.review_source_language') }}: {{ $localeName($roomType->source_locale) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-slate-900">{{ format_price($roomType->base_price) }}</div>
                                    <div class="text-[11px] text-slate-500">{{ __('ui.price_per_night') }}</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700"><i class="fa-solid fa-user-group"></i> {{ $roomType->capacity }} {{ __('ui.guests') }}</span>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700"><i class="fa-solid fa-bed"></i> {{ $roomType->bed_count }} &times; {{ __('ui.bed_type_' . $roomType->bed_type) }}</span>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700"><i class="fa-solid fa-door-closed"></i> {{ $roomType->total_rooms }} {{ __('ui.review_rooms') }}</span>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700"><i class="fa-solid fa-utensils"></i> {{ __('ui.meal_plan_' . $roomType->meal_plan) }}</span>
                                <span class="px-2.5 py-1 rounded-full {{ $roomType->smoking_allowed ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    <i class="fa-solid {{ $roomType->smoking_allowed ? 'fa-smoking' : 'fa-ban-smoking' }}"></i> {{ __($roomType->smoking_allowed ? 'ui.smoking_yes' : 'ui.smoking_no') }}
                                </span>
                            </div>

                            @if ($roomType->photos->isNotEmpty())
                                <div class="flex gap-2 overflow-x-auto pb-1">
                                    @foreach ($roomType->photos as $photo)
                                        <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="relative shrink-0 w-28 h-20 rounded-lg overflow-hidden bg-slate-100 border border-slate-200">
                                            <img src="{{ $photo->url() }}" alt="" loading="lazy" class="w-full h-full object-cover">
                                            @if ($photo->is_cover)<span class="absolute top-1 left-1 px-1.5 py-0.5 rounded-full bg-sky-500 text-white text-[9px] font-bold">{{ __('ui.cover') }}</span>@endif
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs font-semibold text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.review_no_room_photo') }}</p>
                            @endif

                            @if ($roomType->facilities->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($roomType->facilities as $facility)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-100 text-[11px] font-semibold">
                                            <i class="fa-solid {{ $facility->icon() }}"></i> {{ $facility->label }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="grid md:grid-cols-2 gap-3">
                                @foreach ($roomType->translations as $t)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2 space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="px-1.5 py-0.5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold uppercase">{{ $localeName($t->locale) }}</span>
                                            @if ($t->is_auto_translated)<span class="text-[10px] font-bold text-amber-600">{{ __('ui.auto_translated') }}</span>@endif
                                        </div>
                                        <div class="text-sm font-bold text-slate-900">{{ $t->name }}</div>
                                        @if ($t->description)<p class="text-xs text-slate-600 whitespace-pre-line">{{ $t->description }}</p>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="{{ $card }} text-sm text-slate-400">{{ __('ui.no_room_types') }}</div>
                    @endforelse
                </div>

                {{-- RIWAYAT --}}
                @includeIf('partials.activity-timeline', ['activities' => $activities, 'showActor' => true])
            </div>

            {{-- ============ KOLOM SAMPING ============ --}}
            <div class="space-y-6 lg:sticky lg:top-24">

                {{-- KEPUTUSAN --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $title }}">{{ __('ui.decision') }}</h2>

                    @if ($hotel->status !== 'approved')
                        <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">
                            @csrf
                            <button class="w-full px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition">
                                <i class="fa-solid fa-circle-check mr-1"></i> {{ __('ui.approve') }}
                            </button>
                        </form>
                    @endif

                    @if ($hotel->status !== 'rejected')
                        <form method="POST" action="{{ route('admin.hotels.reject', $hotel) }}" class="space-y-2">
                            @csrf
                            <textarea name="rejection_reason" rows="3" maxlength="500" required placeholder="{{ __('ui.rejection_reason_label') }}"
                                      class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">{{ old('rejection_reason') }}</textarea>
                            <button class="w-full px-5 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition">
                                <i class="fa-solid fa-ban mr-1"></i> {{ __('ui.reject') }}
                            </button>
                        </form>
                    @endif
                </div>

                {{-- KELENGKAPAN --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $title }}">{{ __('ui.review_completeness') }}</h2>
                    <ul class="space-y-1.5 text-sm">
                        @foreach ($checks as [$key, $ok])
                            <li class="flex items-start gap-2 {{ $ok ? 'text-slate-700' : 'text-amber-700 font-semibold' }}">
                                <i class="fa-solid {{ $ok ? 'fa-circle-check text-emerald-500' : 'fa-triangle-exclamation text-amber-500' }} mt-0.5"></i>
                                {{ __('ui.' . $key) }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="text-[11px] text-slate-400">{{ __('ui.review_completeness_note') }}</p>
                </div>

                {{-- MITRA --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $title }}">{{ __('ui.partner') }}</h2>
                    <div class="text-sm space-y-1">
                        <a href="{{ route('admin.partners.show', $partner) }}" class="font-extrabold text-sky-600 hover:underline">{{ $partner->company_name }}</a>
                        @if ($partner->contact_name)<div class="text-slate-600">{{ $partner->contact_name }}</div>@endif
                        <div class="text-slate-500 text-xs">{{ $partner->user?->email }}@if ($partner->user?->phone_number) &middot; {{ $partner->user->phone_number }}@endif</div>
                        <div class="pt-1 text-xs">
                            @if ($partner->hasSignedAgreement())
                                <span class="font-bold text-emerald-600"><i class="fa-solid fa-file-signature"></i> {{ __('ui.review_mou_signed', ['percent' => rtrim(rtrim(number_format($partner->commissionRate() * 100, 2, '.', ''), '0'), '.')]) }}</span>
                            @else
                                <span class="font-bold text-amber-600"><i class="fa-solid fa-file-circle-exclamation"></i> {{ __('ui.review_mou_not_signed') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection