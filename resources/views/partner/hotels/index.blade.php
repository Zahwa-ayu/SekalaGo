@extends('layouts.app')

@section('content')
    @php
        $th   = 'px-6 py-4 font-bold';
        $td   = 'px-6 py-4';
        $chip = 'px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap';
    @endphp

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.my_hotels') }}</h1>
        <a href="{{ route('partner.hotels.create') }}"
           class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> {{ __('ui.add_hotel') }}
        </a>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TABEL --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="{{ $th }}">{{ __('ui.hotel_name') }}</th>
                        <th class="{{ $th }}">{{ __('ui.city') }}</th>
                        <th class="{{ $th }}">{{ __('ui.status') }}</th>
                        <th class="{{ $th }}">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($hotels as $hotel)
                        @php
                            $badge = match ($hotel->status) {
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default    => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="{{ $td }} min-w-48 font-bold text-slate-900">{{ $hotel->name }}</td>
                            <td class="{{ $td }} text-slate-600">{{ $hotel->city }}</td>
                            <td class="{{ $td }}">
                                <span class="{{ $chip }} {{ $badge }}">
                                    {{ __('ui.status_' . $hotel->status) }}
                                </span>
                                @if ($hotel->status === 'rejected' && $hotel->rejection_reason)
                                    <div class="text-[11px] text-red-600 mt-1">
                                        {{ __('ui.rejection_reason_label') }}: {{ $hotel->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td class="{{ $td }} whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('partner.hotels.room-types.index', $hotel) }}" class="text-sagara-blue hover:underline">
                                        {{ __('ui.room_types') }}
                                    </a>
                                    <a href="{{ route('partner.hotels.photos.index', $hotel) }}" class="text-sagara-blue hover:underline">
                                        {{ __('ui.photos') }}
                                    </a>
                                    <a href="{{ route('partner.hotels.edit', $hotel) }}" class="text-sagara-blue hover:underline">
                                        {{ __('ui.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('partner.hotels.destroy', $hotel) }}"
                                          onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
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