@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.review_hotels') }}</h1>

        {{-- FILTER STATUS --}}
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (['pending', 'approved', 'rejected'] as $s)
                <a href="{{ route('admin.hotels.index', ['status' => $s]) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm flex items-center gap-2 transition
                          {{ $status === $s ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                    {{ __('ui.status_' . $s) }}
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $status === $s ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ $counts->get($s, 0) }}</span>
                </a>
            @endforeach
            <a href="{{ route('admin.hotels.index', ['status' => 'all']) }}"
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
                            <th class="px-6 py-4 font-bold">{{ __('ui.hotel_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.partner') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.city') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.submitted_at') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($hotels as $hotel)
                            @php
                                $badge = match ($hotel->status) {
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $hotel->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $hotel->partner->company_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $hotel->city }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ $badge }}">{{ __('ui.status_' . $hotel->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $hotel->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.hotels.show', $hotel) }}"
                                       class="px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-lg transition whitespace-nowrap">
                                        {{ __('ui.review') }} <i class="fa-solid fa-chevron-right ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-hotel text-3xl mb-2 block"></i>
                                    {{ __('ui.no_hotels') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection