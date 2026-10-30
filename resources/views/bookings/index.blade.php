@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.my_bookings') }}</h1>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.booking_code') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.hotel') }} / {{ __('ui.room_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.total_price') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            @php
                                $badge = match ($booking->status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'pending_payment', 'pending_approval' => 'bg-amber-100 text-amber-700',
                                    'cancelled', 'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-sagara-blue whitespace-nowrap">{{ $booking->booking_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $booking->hotel_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $booking->room_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ $booking->displayTotal() }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">
                                        {{ __('ui.booking_status_' . $booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold space-x-3">
                                    <a href="{{ route('bookings.show', $booking) }}" class="text-sagara-blue hover:underline">{{ __('ui.view') }}</a>

                                    @if ($booking->status === 'pending_payment' && ! $booking->isCorporate())
                                        <a href="{{ route('bookings.pay', $booking) }}" class="text-amber-600 hover:underline">{{ __('ui.pay_now') }}</a>
                                    @elseif ($booking->status === 'confirmed')
                                        <a href="{{ route('bookings.ticket', $booking) }}" class="text-emerald-600 hover:underline">{{ __('ui.download_ticket') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
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