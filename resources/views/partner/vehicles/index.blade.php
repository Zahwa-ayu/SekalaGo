@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.my_vehicles') }}</h1>
            <a href="{{ route('partner.vehicles.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sagara-navy text-white text-sm font-bold shadow-md hover:bg-sagara-blue transition">
                <i class="fa-solid fa-plus"></i> {{ __('ui.add_vehicle') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.vehicle_name_short') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.city') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.units_available') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.price_per_day') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vehicles as $vehicle)
                            @php
                                $badge = match ($vehicle->status) {
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-20 h-14 rounded-xl overflow-hidden bg-slate-200 shrink-0 flex items-center justify-center text-slate-400">
                                            @if ($vehicle->coverUrl())
                                                <img src="{{ $vehicle->coverUrl() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                <i class="fa-solid fa-car text-xl"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $vehicle->name }}</div>
                                            <div class="text-[11px] text-slate-500">
                                                {{ __('ui.vehicle_type_' . $vehicle->type) }} &middot;
                                                {{ $vehicle->seats }} {{ __('ui.seats_short') }} &middot;
                                                {{ __('ui.transmission_' . $vehicle->transmission) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $vehicle->city }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $vehicle->units }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                    {{ format_price($vehicle->price_per_day, 'IDR') }}<span class="text-xs font-semibold text-slate-400">{{ __('ui.per_day') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.status_' . $vehicle->status) }}</span>
                                    @if ($vehicle->status === 'rejected' && $vehicle->rejection_reason)
                                        <div class="text-[11px] text-red-600 mt-1">{{ __('ui.rejection_reason_label') }}: {{ $vehicle->rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold space-x-3">
                                    <a href="{{ route('partner.vehicles.photos.index', $vehicle) }}" class="text-sagara-blue hover:underline">{{ __('ui.photos') }}</a>
                                    <a href="{{ route('partner.vehicles.blackouts.index', $vehicle) }}" class="text-sagara-blue hover:underline">{{ __('ui.blackout_dates') }}</a>
                                    <a href="{{ route('partner.vehicles.edit', $vehicle) }}" class="text-slate-600 hover:underline">{{ __('ui.edit') }}</a>
                                    <form method="POST" action="{{ route('partner.vehicles.destroy', $vehicle) }}" class="inline"
                                          onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-car text-3xl mb-2 block"></i>
                                    {{ __('ui.no_vehicles') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection