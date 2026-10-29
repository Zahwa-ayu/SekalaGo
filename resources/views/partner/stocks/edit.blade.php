@extends('layouts.app')

@section('content')
    @php
        $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
    @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <a href="{{ route('partner.hotels.room-types.index', $roomType->hotel) }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.room_types') }}
        </a>

        <div>
            <span class="text-[11px] font-extrabold text-sagara-blue uppercase tracking-wider">{{ __('ui.stock') }}</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $roomType->hotel->name }} / {{ $roomType->name }}</h1>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- FORM STOK MASSAL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <h2 class="text-lg font-extrabold text-slate-900 mb-4"><i class="fa-solid fa-layer-group text-sagara-blue"></i> {{ __('ui.bulk_stock') }}</h2>

            <form method="POST" action="{{ route('partner.room-types.stocks.update', $roomType) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-calendar-days text-sagara-blue"></i> {{ __('ui.start_date') }}</label>
                        <input type="date" name="start_date" min="{{ today()->toDateString() }}" required class="{{ $input }}"
                               value="{{ old('start_date', today()->toDateString()) }}">
                    </div>

                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-calendar-check text-sagara-coral"></i> {{ __('ui.end_date') }}</label>
                        <input type="date" name="end_date" min="{{ today()->toDateString() }}" required class="{{ $input }}"
                               value="{{ old('end_date', today()->addDays(29)->toDateString()) }}">
                    </div>
                </div>

                <div class="{{ $box }}">
                    <label class="{{ $label }}"><i class="fa-solid fa-bed text-sagara-blue"></i> {{ __('ui.available') }} (0 - {{ $roomType->total_rooms }})</label>
                    <input type="number" name="available" min="0" max="{{ $roomType->total_rooms }}" required class="{{ $input }}"
                           value="{{ old('available', $roomType->total_rooms) }}">
                </div>
                <p class="text-[11px] text-slate-500 -mt-2"><i class="fa-solid fa-circle-info"></i> {{ __('ui.stock_hint') }}</p>

                <fieldset class="border border-slate-200 rounded-2xl p-4">
                    <legend class="px-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <i class="fa-solid fa-calendar-week text-sagara-blue"></i> {{ __('ui.apply_to_days') }}
                    </legend>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach ([1, 2, 3, 4, 5, 6, 0] as $day)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="days[]" value="{{ $day }}" class="peer sr-only"
                                       @checked(in_array($day, old('days', [0, 1, 2, 3, 4, 5, 6])))>
                                <span class="inline-block px-3 py-1.5 text-xs font-semibold rounded-full border border-slate-200 text-slate-600 bg-slate-50 hover:border-sagara-blue transition
                                             peer-checked:bg-sagara-blue peer-checked:text-white peer-checked:border-sagara-blue peer-focus-visible:ring-2 peer-focus-visible:ring-sagara-teal">
                                    {{ __('ui.days')[$day] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <button class="px-8 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> {{ __('ui.save') }}
                </button>
            </form>
        </div>

        {{-- STOK SAAT INI --}}
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.current_stock') }}</h2>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-4 font-bold">{{ __('ui.date') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.available') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ranges as $range)
                                <tr class="hover:bg-sky-50/50 transition">
                                    <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                        @if ($range['from']->isSameDay($range['to']))
                                            {{ $range['from']->format('d/m/Y') }}
                                        @else
                                            {{ $range['from']->format('d/m/Y') }} – {{ $range['to']->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ $range['available'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $range['available'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                                        {{ __('ui.no_stock') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection