@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.my_hotels') }}</h1>

            @if ($partner->verification_status === 'verified')
                <a href="{{ route('partner.hotels.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue rounded-xl shadow-md transition">
                    <i class="fa-solid fa-plus"></i> {{ __('ui.add_hotel') }}
                </a>
            @endif
        </div>

        @if ($partner->verification_status === 'rejected')
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex flex-wrap items-center gap-x-2 gap-y-1">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>
                    {{ __('ui.partner_rejected_notice') }}
                    @if ($partner->verification_note)
                        ({{ $partner->verification_note }})
                    @endif
                </span>
                <a href="{{ route('partner.profile.edit') }}" class="font-bold underline">{{ __('ui.partner_profile') }}</a>
            </div>
        @elseif ($partner->verification_status !== 'verified')
            <div class="mb-6 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-hourglass-half"></i> {{ __('ui.not_verified_notice') }}
            </div>
        @endif

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
                                    'approved', 'active', 'published', 'verified' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $hotel->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $hotel->city }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ $badge }}">{{ __('ui.status_' . $hotel->status) }}</span>
                                    @if ($hotel->status === 'rejected' && $hotel->rejection_reason)
                                        <p class="text-[11px] text-slate-500 mt-1">{{ __('ui.rejection_reason_label') }}: {{ $hotel->rejection_reason }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('partner.hotels.room-types.index', $hotel) }}"
                                           class="px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-lg transition">
                                            <i class="fa-solid fa-bed"></i> {{ __('ui.room_types') }}
                                        </a>
                                        <a href="{{ route('partner.hotels.edit', $hotel) }}"
                                           class="px-3 py-1.5 text-xs font-bold border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                                            <i class="fa-solid fa-pen"></i> {{ __('ui.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('partner.hotels.destroy', $hotel) }}"
                                              class="inline"
                                              onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1.5 text-xs font-bold border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <i class="fa-solid fa-trash"></i> {{ __('ui.delete') }}
                                            </button>
                                        </form>
                                    </div>
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