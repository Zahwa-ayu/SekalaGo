@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        @if ($overdueRentals > 0)
            <a href="{{ route('partner.rentals.index') }}" class="block rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 hover:bg-red-100 transition">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.overdue_rentals_alert', ['count' => $overdueRentals]) }}
            </a>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.dashboard') }}</h1>
            <a href="{{ route('partner.hotels.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sagara-navy text-white text-sm font-bold shadow-md hover:bg-sagara-blue transition">
                <i class="fa-solid fa-plus"></i> {{ __('ui.add_hotel') }}
            </a>
        </div>

        {{-- HOTEL --}}
        <div>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('ui.my_hotels') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['fa-circle-check', __('ui.status_approved'), $hotelStatus->get('approved', 0), 'bg-emerald-100 text-emerald-600'],
                    ['fa-hourglass-half', __('ui.status_pending'), $hotelStatus->get('pending', 0), 'bg-amber-100 text-amber-600'],
                    ['fa-circle-xmark', __('ui.status_rejected'), $hotelStatus->get('rejected', 0), 'bg-red-100 text-red-600'],
                ] as [$icon, $label, $count, $tone])
                    <a href="{{ route('partner.hotels.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 flex items-center gap-4 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div>
                            <div class="text-2xl font-black text-slate-900">{{ $count }}</div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ARMADA --}}
        <div>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('ui.my_vehicles') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['fa-circle-check', __('ui.status_approved'), $vehicleStatus->get('approved', 0), 'bg-emerald-100 text-emerald-600'],
                    ['fa-hourglass-half', __('ui.status_pending'), $vehicleStatus->get('pending', 0), 'bg-amber-100 text-amber-600'],
                    ['fa-circle-xmark', __('ui.status_rejected'), $vehicleStatus->get('rejected', 0), 'bg-red-100 text-red-600'],
                ] as [$icon, $label, $count, $tone])
                    <a href="{{ route('partner.vehicles.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 flex items-center gap-4 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div>
                            <div class="text-2xl font-black text-slate-900">{{ $count }}</div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- BULAN INI --}}
        <div>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('ui.this_month') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['fa-receipt', __('ui.orders_count'), $monthly->orders, false],
                    ['fa-coins', __('ui.gross_sales'), format_price($monthly->gross, 'IDR'), false],
                    ['fa-percent', __('ui.platform_commission'), format_price($monthly->fee, 'IDR'), false],
                    ['fa-hand-holding-dollar', __('ui.net_revenue'), format_price($monthly->net, 'IDR'), true],
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
        </div>

        {{-- CHECK-IN MENDATANG --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <span class="font-extrabold text-slate-900">{{ __('ui.upcoming_checkins') }}</span>
                <a href="{{ route('partner.bookings.index') }}" class="text-xs font-bold text-sagara-blue hover:underline">{{ __('ui.all') }}</a>
            </div>
            <table class="w-full text-sm text-left">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($upcoming as $booking)
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-3 font-mono text-xs font-bold text-sagara-blue whitespace-nowrap">{{ $booking->booking_code }}</td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900"><i class="fa-solid {{ $booking->productIcon() }} text-sagara-blue mr-1"></i>{{ $booking->guest_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $booking->hotel_name }} / {{ $booking->room_name }}</div>
                            </td>
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $booking->check_in->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-6 py-10 text-center text-slate-400">{{ __('ui.no_bookings') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection