@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.dashboard') }}</h1>

        {{-- PERLU TINDAKAN --}}
        <div>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('ui.needs_action') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['fa-handshake', __('ui.pending_partners_label'), $pendingPartners, route('admin.partners.index')],
                    ['fa-hotel', __('ui.pending_hotels_label'), $pendingHotels, route('admin.hotels.index')],
                    ['fa-file-signature', __('ui.mou_waiting_label'), $unsignedMou, route('admin.partners.index', ['status' => 'verified'])],
                    ['fa-car-side', __('ui.pending_vehicles_label'), $pendingVehicles, route('admin.vehicles.index')],
                ] as [$icon, $label, $count, $url])
                    <a href="{{ $url }}"
                       class="bg-white rounded-2xl border {{ $count > 0 ? 'border-amber-200' : 'border-slate-100' }} shadow-md p-5 flex items-center gap-4 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl {{ $count > 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
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
                    ['fa-percent', __('ui.platform_commission'), format_price($monthly->fee, 'IDR'), true],
                    ['fa-building', __('ui.deposit_total') . ' (' . $companyCount . ')', format_price($depositTotal, 'IDR'), false],
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

        {{-- PESANAN TERBARU --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <span class="font-extrabold text-slate-900">{{ __('ui.recent_bookings') }}</span>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs font-bold text-sagara-blue hover:underline">{{ __('ui.all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recent as $booking)
                            @php
                                $badge = match ($booking->status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'pending_payment', 'pending_approval' => 'bg-amber-100 text-amber-700',
                                    'cancelled', 'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-3 font-mono text-xs font-bold text-sagara-blue whitespace-nowrap">{{ $booking->booking_code }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-900">{{ $booking->hotel_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $booking->partner?->company_name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3 font-bold text-slate-900 whitespace-nowrap">{{ format_price($booking->total_price, 'IDR') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-6 py-10 text-center text-slate-400">{{ __('ui.no_bookings') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection