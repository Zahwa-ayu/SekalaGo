@extends('layouts.app')

@section('content')
    @php
        $chip   = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold';
        $card   = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $label  = 'text-[11px] font-bold uppercase tracking-wider text-slate-400';
        $photos = $vehicle->photos->sortByDesc('is_cover')->values();
        $address = collect([$vehicle->address, $vehicle->area, $vehicle->city])->filter()->join(', ');
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.review_vehicles') }}
        </a>

        {{-- HEADER --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words">{{ $vehicle->name }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $vehicle->partner->company_name }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $vehicle->status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700' }}">
                @if ($vehicle->status === 'pending')<i class="fa-solid fa-hourglass-half"></i>@endif
                {{ __('ui.status_' . $vehicle->status) }}
            </span>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- FOTO --}}
        <div class="{{ $card }}">
            @if ($photos->isEmpty())
                <div class="py-10 text-center text-sm text-slate-400">
                    <i class="fa-regular fa-image text-3xl mb-2 block"></i>
                    {{ __('ui.no_photos') }}
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($photos as $photo)
                        <img src="{{ $photo->url() }}" alt="{{ $vehicle->name }}" loading="lazy"
                             class="w-full object-cover rounded-xl {{ $loop->first ? 'col-span-2 row-span-2 h-full min-h-48 sm:min-h-64' : 'h-28 sm:h-32' }} {{ $photo->is_cover ? 'ring-2 ring-sagara-teal' : '' }}">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- KOLOM KIRI: DETAIL --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- SPESIFIKASI --}}
                <div class="{{ $card }} space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <span class="{{ $chip }}"><i class="fa-solid fa-car-side text-sagara-blue"></i> {{ __('ui.vehicle_type_' . $vehicle->type) }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-chair text-sagara-blue"></i> {{ $vehicle->seats }} {{ __('ui.seats_short') }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-gears text-sagara-blue"></i> {{ __('ui.transmission_' . $vehicle->transmission) }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-gas-pump text-sagara-blue"></i> {{ __('ui.fuel_type_' . $vehicle->fuel_type) }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-id-card-clip text-sagara-blue"></i> {{ __('ui.driver_mode_' . $vehicle->driver_mode) }}</span>
                        @foreach ($vehicle->features ?? [] as $feature)
                            <span class="{{ $chip }}"><i class="fa-solid {{ \App\Models\Vehicle::FEATURE_ICONS[$feature] ?? 'fa-circle-check' }} text-sagara-blue"></i> {{ __('ui.feature_' . $feature) }}</span>
                        @endforeach
                    </div>

                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 text-sm pt-4 border-t border-slate-100">
                        <div>
                            <dt class="{{ $label }}">{{ __('ui.price_per_day') }}</dt>
                            <dd class="mt-0.5 font-bold text-slate-900">{{ format_price($vehicle->price_per_day, 'IDR') }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $label }}">{{ __('ui.units_available') }}</dt>
                            <dd class="mt-0.5 font-bold text-slate-900">{{ $vehicle->units }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $label }}">{{ __('ui.min_days') }}</dt>
                            <dd class="mt-0.5 font-bold text-slate-900">{{ $vehicle->min_days }}</dd>
                        </div>
                        @if ($vehicle->driver_fee_per_day !== null)
                            <div>
                                <dt class="{{ $label }}">{{ __('ui.driver_fee') }}</dt>
                                <dd class="mt-0.5 font-bold text-slate-900">{{ format_price($vehicle->driver_fee_per_day, 'IDR') }}</dd>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <dt class="{{ $label }}">{{ __('ui.pickup_location') }}</dt>
                            <dd class="mt-0.5 text-slate-700">{{ $address }}</dd>
                        </div>
                        @if ($vehicle->extras_note)
                            <div class="sm:col-span-2">
                                <dt class="{{ $label }}">{{ __('ui.extras_note') }}</dt>
                                <dd class="mt-0.5 text-slate-700">{{ $vehicle->extras_note }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($vehicle->rejection_reason)
                        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600">
                            <strong>{{ __('ui.rejection_reason_label') }}:</strong> {{ $vehicle->rejection_reason }}
                        </div>
                    @endif

                    @if ($vehicle->blackouts->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100">
                            <div class="{{ $label }} mb-2">{{ __('ui.blackout_dates') }}</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($vehicle->blackouts as $b)
                                    <span class="{{ $chip }}">
                                        <i class="fa-regular fa-calendar text-sagara-blue"></i>
                                        {{ $b->start_date->format('d/m/Y') }} - {{ $b->end_date->format('d/m/Y') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- TERJEMAHAN --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <h2 class="px-6 pt-6 font-extrabold text-slate-900">{{ __('ui.description') }}</h2>

                    <div class="overflow-x-auto mt-3">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-6 py-2.5 font-bold">{{ __('ui.language') }}</th>
                                    <th class="px-3 py-2.5 font-bold">{{ __('ui.vehicle_name_short') }}</th>
                                    <th class="px-6 py-2.5 font-bold">{{ __('ui.description') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($vehicle->translations as $t)
                                    <tr class="align-top">
                                        <td class="px-6 py-3 whitespace-nowrap font-semibold text-slate-800">
                                            {{ config('sekalago.locales.' . $t->locale, $t->locale) }}
                                            @if ($t->is_auto_translated)
                                                <div class="text-[11px] font-normal text-slate-400">({{ __('ui.auto_translated') }})</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 min-w-32">{{ $t->name }}</td>
                                        <td class="px-6 py-3 min-w-56 text-slate-600">{{ $t->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: KEPUTUSAN --}}
            <div class="{{ $card }} space-y-4 lg:sticky lg:top-24">
                <h2 class="font-extrabold text-slate-900">{{ __('ui.decision') }}</h2>

                @if ($vehicle->status !== 'approved')
                    <form method="POST" action="{{ route('admin.vehicles.approve', $vehicle) }}">
                        @csrf
                        <button class="w-full px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                            <i class="fa-solid fa-check"></i> {{ __('ui.approve') }}
                        </button>
                    </form>
                @endif

                @if ($vehicle->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.vehicles.reject', $vehicle) }}" class="space-y-2 {{ $vehicle->status !== 'approved' ? 'pt-4 border-t border-slate-100' : '' }}">
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

        @include('partials.activity-timeline', ['activities' => $activities, 'showActor' => true])
    </section>
@endsection