@extends('layouts.app')

@section('content')
    @php
        $badge = match ($hotel->status) {
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
        $th = 'px-6 py-3 font-bold';
        $td = 'px-6 py-3';
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <a href="{{ route('admin.hotels.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.review_hotels') }}
        </a>

        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $hotel->name }}</h1>
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $badge }}">{{ __('ui.status_' . $hotel->status) }}</span>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- DETAIL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            @if ($hotel->rejection_reason)
                <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <strong>{{ __('ui.rejection_reason_label') }}:</strong> {{ $hotel->rejection_reason }}
                </div>
            @endif

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                @foreach ([
                    ['fa-handshake', __('ui.partner'), $hotel->partner->company_name],
                    ['fa-location-dot', __('ui.city'), $hotel->city],
                    ['fa-map-pin', __('ui.address'), $hotel->address ?? '-'],
                    ['fa-star', __('ui.star_rating'), $hotel->star_rating ?? '-'],
                ] as [$icon, $label, $value])
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid {{ $icon }} text-sagara-blue"></i> {{ $label }}</dt>
                        <dd class="font-semibold text-slate-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-5 pt-5 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2"><i class="fa-solid fa-concierge-bell text-sagara-blue"></i> {{ __('ui.facilities') }}</div>
                @forelse ($hotel->facilities as $f)
                    <span class="inline-block px-3 py-1 mr-1 mb-1 text-xs font-semibold rounded-full bg-sky-100 text-sagara-blue">{{ $f->label }}</span>
                @empty
                    <span class="text-sm text-slate-500">-</span>
                @endforelse
            </div>
        </div>

        {{-- TRANSLATIONS --}}
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.translations') }}</h2>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
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
                                            <span class="ml-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">{{ __('ui.auto_translated') }}</span>
                                        @endif
                                    </td>
                                    <td class="{{ $td }} text-slate-700">{{ $t->name }}</td>
                                    <td class="{{ $td }} text-slate-600">{{ $t->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ROOM TYPES --}}
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.room_types') }}</h2>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
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
                                    <td class="{{ $td }} font-bold text-sagara-blue">{{ format_price($roomType->base_price) }}</td>
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

        {{-- DECISION --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <h2 class="text-lg font-extrabold text-slate-900 mb-4">{{ __('ui.decision') }}</h2>

            <div class="space-y-6">
                @if ($hotel->status !== 'approved')
                    <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">
                        @csrf
                        <button class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i> {{ __('ui.approve') }}
                        </button>
                    </form>
                @endif

                @if ($hotel->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.hotels.reject', $hotel) }}" class="space-y-3">
                        @csrf
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-coral transition">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                <i class="fa-solid fa-comment-dots text-sagara-coral"></i> {{ __('ui.rejection_reason_label') }}
                            </label>
                            <textarea name="rejection_reason" rows="3" maxlength="500" required
                                      placeholder="{{ __('ui.rejection_reason_label') }}"
                                      class="w-full bg-transparent text-slate-800 text-sm focus:outline-none resize-y">{{ old('rejection_reason') }}</textarea>
                        </div>
                        <button class="px-8 py-3 bg-gradient-to-r from-sagara-coral to-red-500 hover:from-red-600 hover:to-red-500 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                            <i class="fa-solid fa-circle-xmark"></i> {{ __('ui.reject') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection