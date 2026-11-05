@extends('layouts.app')

@section('content')
    @php $chip = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold'; @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.review_vehicles') }}
        </a>

        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $vehicle->name }}</h1>
            <p class="text-sm text-slate-500">{{ $vehicle->partner->company_name }} &middot; {{ __('ui.status_' . $vehicle->status) }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- FOTO --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
            <div class="flex flex-wrap gap-3">
                @forelse ($vehicle->photos as $photo)
                    <img src="{{ $photo->url() }}" alt="" class="w-32 h-24 object-cover rounded-xl {{ $photo->is_cover ? 'ring-2 ring-sagara-teal' : '' }}">
                @empty
                    <span class="text-sm text-slate-400">{{ __('ui.no_photos') }}</span>
                @endforelse
            </div>
        </div>

        {{-- DETAIL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
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

            <div class="text-sm text-slate-700 space-y-1">
                <div><strong>{{ __('ui.pickup_location') }}:</strong> {{ collect([$vehicle->address, $vehicle->area, $vehicle->city])->filter()->join(', ') }}</div>
                <div><strong>{{ __('ui.price_per_day') }}:</strong> {{ format_price($vehicle->price_per_day, 'IDR') }}
                    &middot; <strong>{{ __('ui.units_available') }}:</strong> {{ $vehicle->units }}
                    &middot; <strong>{{ __('ui.min_days') }}:</strong> {{ $vehicle->min_days }}</div>
                @if ($vehicle->driver_fee_per_day !== null)
                    <div><strong>{{ __('ui.driver_fee') }}:</strong> {{ format_price($vehicle->driver_fee_per_day, 'IDR') }}</div>
                @endif
                @if ($vehicle->extras_note)
                    <div><strong>{{ __('ui.extras_note') }}:</strong> {{ $vehicle->extras_note }}</div>
                @endif
                @if ($vehicle->rejection_reason)
                    <div class="text-red-600"><strong>{{ __('ui.rejection_reason_label') }}:</strong> {{ $vehicle->rejection_reason }}</div>
                @endif
            </div>

            <table class="w-full text-sm text-left border border-slate-100 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-2 font-bold">{{ __('ui.language') }}</th>
                        <th class="px-4 py-2 font-bold">{{ __('ui.vehicle_name_short') }}</th>
                        <th class="px-4 py-2 font-bold">{{ __('ui.description') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($vehicle->translations as $t)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ config('sekalago.locales.' . $t->locale, $t->locale) }}
                                @if ($t->is_auto_translated) <span class="text-[11px] text-slate-400">({{ __('ui.auto_translated') }})</span> @endif
                            </td>
                            <td class="px-4 py-2">{{ $t->name }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $t->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($vehicle->blackouts->isNotEmpty())
                <div class="text-xs text-slate-500">
                    <strong>{{ __('ui.blackout_dates') }}:</strong>
                    {{ $vehicle->blackouts->map(fn ($b) => $b->start_date->format('d/m/Y') . ' - ' . $b->end_date->format('d/m/Y'))->join(', ') }}
                </div>
            @endif
        </div>

        {{-- KEPUTUSAN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            <h2 class="font-extrabold text-slate-900">{{ __('ui.decision') }}</h2>

            @if ($vehicle->status !== 'approved')
                <form method="POST" action="{{ route('admin.vehicles.approve', $vehicle) }}">
                    @csrf
                    <button class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-check"></i> {{ __('ui.approve') }}
                    </button>
                </form>
            @endif

            @if ($vehicle->status !== 'rejected')
                <form method="POST" action="{{ route('admin.vehicles.reject', $vehicle) }}" class="space-y-2">
                    @csrf
                    <textarea name="rejection_reason" rows="3" maxlength="500" required
                              placeholder="{{ __('ui.rejection_reason_label') }}"
                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">{{ old('rejection_reason') }}</textarea>
                    <button class="px-6 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition">
                        <i class="fa-solid fa-xmark"></i> {{ __('ui.reject') }}
                    </button>
                </form>
            @endif
        </div>

        @include('partials.activity-timeline', ['activities' => $activities, 'showActor' => true])
    </section>
@endsection 