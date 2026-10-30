@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.my_hotels') }}</h1>

            <a href="{{ route('partner.hotels.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sagara-navy text-white text-sm font-bold shadow-md hover:bg-sagara-blue transition">
                <i class="fa-solid fa-plus"></i> {{ __('ui.add_hotel') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.hotel_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.city') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
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
                            <tr class="hover:bg-sky-50/50 transition align-top">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $hotel->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $hotel->city }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">
                                        {{ __('ui.status_' . $hotel->status) }}
                                    </span>
                                    @if ($hotel->status === 'rejected' && $hotel->rejection_reason)
                                        <div class="text-[11px] text-red-600 mt-1">
                                            {{ __('ui.rejection_reason_label') }}: {{ $hotel->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold space-x-3">
                                    <a href="{{ route('partner.hotels.room-types.index', $hotel) }}" class="text-sagara-blue hover:underline">{{ __('ui.room_types') }}</a>
                                    <a href="{{ route('partner.hotels.edit', $hotel) }}" class="text-slate-600 hover:underline">{{ __('ui.edit') }}</a>
                                    <form method="POST" action="{{ route('partner.hotels.destroy', $hotel) }}" class="inline"
                                          onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
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