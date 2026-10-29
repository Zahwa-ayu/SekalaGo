@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.incoming_bookings') }}</h1>

        {{-- RINGKASAN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['fa-receipt', __('ui.orders_count'), $summary->orders, false],
                ['fa-coins', __('ui.gross_sales'), format_price($summary->gross, 'IDR'), false],
                ['fa-percent', __('ui.platform_commission'), format_price($summary->fee, 'IDR'), false],
                ['fa-hand-holding-dollar', __('ui.net_revenue'), format_price($summary->net, 'IDR'), true],
            ] as [$icon, $label, $value, $highlight])
                <div class="{{ $highlight ? 'bg-gradient-to-br from-sagara-navy to-sagara-blue text-white shadow-xl' : 'bg-white border border-slate-100 shadow-md' }} rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg mb-3 {{ $highlight ? 'bg-white/15 text-sagara-teal' : 'bg-sky-100 text-sagara-blue' }}">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ $highlight ? 'text-sky-200' : 'text-slate-400' }}">{{ $label }}</div>
                    <div class="text-xl sm:text-2xl font-black mt-1 {{ $highlight ? '' : 'text-slate-900' }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-500 mt-3 mb-8"><i class="fa-solid fa-circle-info"></i> {{ __('ui.revenue_note') }}</p>

        {{-- FILTER STATUS --}}
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (['confirmed', 'pending_payment', 'cancelled', 'expired'] as $s)
                <a href="{{ route('partner.bookings.index', ['status' => $s]) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                          {{ $status === $s ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                    {{ __('ui.booking_status_' . $s) }}
                </a>
            @endforeach
            <a href="{{ route('partner.bookings.index', ['status' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                      {{ $status === 'all' ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                {{ __('ui.all') }}
            </a>
        </div>

        {{-- TABEL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.booking_code') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.hotel') }} / {{ __('ui.room_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.guest_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.total_price') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.platform_commission') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.net_revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            @php
                                $badge = match ($booking->status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'pending_payment' => 'bg-amber-100 text-amber-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-sagara-blue whitespace-nowrap">{{ $booking->booking_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $booking->hotel_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $booking->room_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-700 whitespace-nowrap">
                                    {{ $booking->guest_name }}
                                    <span class="text-slate-400 text-xs"><i class="fa-solid fa-users"></i> {{ $booking->guests }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($booking->total_price, 'IDR') }}</td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ format_price($booking->platform_fee, 'IDR') }}</td>
                                <td class="px-6 py-4 font-bold text-sagara-blue whitespace-nowrap">{{ format_price($booking->vendor_revenue, 'IDR') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                    {{ __('ui.no_bookings') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection