@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.review_vehicles') }}</h1>

        <div class="flex flex-wrap gap-2">
            @foreach (['pending', 'approved', 'rejected'] as $s)
                <a href="{{ route('admin.vehicles.index', ['status' => $s]) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                          {{ $status === $s ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                    {{ __('ui.status_' . $s) }} <span class="ml-1 opacity-70">({{ $counts->get($s, 0) }})</span>
                </a>
            @endforeach
            <a href="{{ route('admin.vehicles.index', ['status' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                      {{ $status === 'all' ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                {{ __('ui.all') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.vehicle_name_short') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.partner') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.city') }}</th>
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
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $vehicle->name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ __('ui.vehicle_type_' . $vehicle->type) }} &middot; {{ $vehicle->seats }} {{ __('ui.seats_short') }}
                                        &middot; {{ $vehicle->units }} {{ __('ui.units_short') }}
                                        &middot; <i class="fa-solid fa-image"></i> {{ $vehicle->photos->count() }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $vehicle->partner->company_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $vehicle->city }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($vehicle->price_per_day, 'IDR') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.status_' . $vehicle->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold">
                                    {{-- <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="text-sagara-blue hover:underline">{{ __('ui.review') }}</a> --}}
                                <a href="{{ route('admin.vehicles.show', $vehicle) }}"
                                    class="px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-lg transition whitespace-nowrap">
                                    {{ __('ui.review') }} <i class="fa-solid fa-chevron-right ml-1"></i>
                                </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">{{ __('ui.no_vehicles') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection