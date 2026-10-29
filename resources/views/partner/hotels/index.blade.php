@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <a href="{{ route('partner.hotels.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition mb-4">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.my_hotels') }}
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.room_types') }}: {{ $hotel->name }}</h1>

            <a href="{{ route('partner.hotels.room-types.create', $hotel) }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue rounded-xl shadow-md transition">
                <i class="fa-solid fa-plus"></i> {{ __('ui.add_room_type') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.room_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.price_per_night') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.capacity') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.total_rooms') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($roomTypes as $roomType)
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $roomType->name }}</td>
                                <td class="px-6 py-4 font-bold text-sagara-blue whitespace-nowrap">{{ format_price($roomType->base_price) }}</td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap"><i class="fa-solid fa-users text-slate-400"></i> {{ $roomType->capacity }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $roomType->total_rooms }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('partner.room-types.stocks.edit', $roomType) }}"
                                           class="px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-lg transition">
                                            <i class="fa-solid fa-boxes-stacked"></i> {{ __('ui.manage_stock') }}
                                        </a>
                                        <a href="{{ route('partner.room-types.edit', $roomType) }}"
                                           class="px-3 py-1.5 text-xs font-bold border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                                            <i class="fa-solid fa-pen"></i> {{ __('ui.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('partner.room-types.destroy', $roomType) }}"
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
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-bed text-3xl mb-2 block"></i>
                                    {{ __('ui.no_room_types') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection