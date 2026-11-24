@extends('layouts.app')

@section('content')
    @php
        $isCar = $booking->isCar();
        $fmt   = fn ($d, $f = 'd/m/Y H:i') => $d ? \Illuminate\Support\Carbon::parse($d)->format($f) : null;
        $money = fn ($v) => format_price($v, 'IDR');
        $yesNo = fn ($v) => $v ? 'Ya' : 'Tidak';

        $card = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $h2   = 'font-extrabold text-slate-900 mb-3';

        $badge = match ($booking->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-700',
            'pending_payment', 'pending_approval' => 'bg-amber-100 text-amber-700',
            'cancelled', 'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };

        // Rincian pesanan
        $stay = [
            [$isCar ? 'Kendaraan' : __('ui.hotel'), $booking->hotel_name],
            [$isCar ? 'Layanan' : __('ui.room_name'), $booking->room_name],
            [$isCar ? 'Mulai sewa' : __('ui.check_in'), $fmt($booking->check_in, 'd/m/Y')],
            [$isCar ? 'Selesai sewa' : __('ui.check_out'), $fmt($booking->check_out, 'd/m/Y')],
            [$isCar ? 'Lama sewa' : 'Malam', $booking->nights . ($isCar ? ' hari' : ' malam')],
            [$isCar ? 'Penumpang' : 'Tamu', $booking->guests],
            ['Dipesan pada', $fmt($booking->created_at)],
        ];

        // Khusus mobil
        $car = [
            ['Dengan supir', $yesNo($booking->with_driver)],
            ['Lokasi jemput', $booking->pickup_location],
            ['Tujuan', $booking->destination],
            ['SIM dikonfirmasi', $yesNo($booking->license_confirmed)],
            ['Batas pengembalian', $fmt($booking->due_at)],
            ['Diambil', $fmt($booking->picked_up_at)],
            ['Dikembalikan', $fmt($booking->returned_at)],
            ['Terlambat', $booking->late_minutes > 0 ? $booking->late_minutes . ' menit' : null],
        ];

        // Tamu (tanpa data akun pemesan)
        $guest = [
            ['Nama tamu', $booking->guest_name],
            ['Nama kontak', $booking->contact_name],
            ['Telepon', $booking->contact_phone],
            ['Perkiraan tiba', $booking->arrival_time],
            ['Permintaan khusus', $booking->special_requests],
        ];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">

        <a href="{{ route('partner.bookings.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:underline">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.incoming_bookings') }}
        </a>

        {{-- HEADER --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="font-mono text-xs font-bold text-sagara-blue">{{ $booking->booking_code }}</div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 break-words">
                    <i class="fa-solid {{ $booking->productIcon() }} text-sagara-blue mr-1" title="{{ $booking->productLabel() }}"></i>{{ $booking->hotel_name }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">{{ $booking->room_name }}</p>
            </div>
            <span class="px-3 py-1.5 text-xs font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:items-start">

            {{-- ============ KOLOM KIRI ============ --}}
            <div class="lg:col-span-2 min-w-0 space-y-6">

                <div class="{{ $card }}">
                    <h2 class="{{ $h2 }}">Rincian pesanan</h2>
                    <dl>
                        @foreach ($stay as [$label, $value])
                            @if (filled($value))
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100 last:border-0 text-sm">
                                    <dt class="text-slate-500 shrink-0">{{ $label }}</dt>
                                    <dd class="font-semibold text-slate-900 text-right break-words min-w-0">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>

                @if ($isCar)
                    <div class="{{ $card }}">
                        <h2 class="{{ $h2 }}">Detail sewa mobil</h2>
                        <dl>
                            @foreach ($car as [$label, $value])
                                @if (filled($value))
                                    <div class="flex justify-between gap-4 py-2 border-b border-slate-100 last:border-0 text-sm">
                                        <dt class="text-slate-500 shrink-0">{{ $label }}</dt>
                                        <dd class="font-semibold text-slate-900 text-right break-words min-w-0">{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="{{ $card }}">
                    <h2 class="{{ $h2 }}">Tamu</h2>
                    <dl>
                        @foreach ($guest as [$label, $value])
                            @if (filled($value))
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100 last:border-0 text-sm">
                                    <dt class="text-slate-500 shrink-0">{{ $label }}</dt>
                                    <dd class="font-semibold text-slate-900 text-right break-words min-w-0 whitespace-pre-line">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>

                {{-- PEMBATALAN --}}
                @if ($booking->status === 'cancelled')
                    <div class="{{ $card }} border-red-100">
                        <h2 class="{{ $h2 }} text-red-600"><i class="fa-solid fa-ban mr-1"></i> Pembatalan</h2>
                        <dl class="text-sm">
                            <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                <dt class="text-slate-500">Dibatalkan oleh</dt>
                                <dd class="font-semibold text-slate-900">
                                    {{ $booking->cancelled_by === 'admin' ? __('ui.cancelled_by_admin') : __('ui.cancelled_by_customer') }}
                                </dd>
                            </div>
                            @if ($booking->cancelled_at)
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                    <dt class="text-slate-500">Waktu</dt>
                                    <dd class="font-semibold text-slate-900">{{ $fmt($booking->cancelled_at) }}</dd>
                                </div>
                            @endif
                            @if ($booking->cancellation_reason)
                                <div class="py-2">
                                    <dt class="text-slate-500 mb-1">Alasan</dt>
                                    <dd class="font-semibold text-slate-900 whitespace-pre-line break-words">{{ $booking->cancellation_reason }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
            </div>

            {{-- ============ KOLOM KANAN ============ --}}
            <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">

                <div class="{{ $card }}">
                    <h2 class="{{ $h2 }}">Rincian pendapatan</h2>
                    <dl class="text-sm">
                        <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                            <dt class="text-slate-500">{{ $money($booking->price_per_night) }} &times; {{ $booking->nights }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $money($booking->price_per_night * $booking->nights) }}</dd>
                        </div>
                        @if ($booking->late_fee > 0)
                            <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                <dt class="text-slate-500">Denda terlambat</dt>
                                <dd class="font-semibold text-slate-900">{{ $money($booking->late_fee) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4 py-3 border-b border-slate-100">
                            <dt class="font-bold text-slate-900">{{ __('ui.total_price') }}</dt>
                            <dd class="font-black text-slate-900">{{ $money($booking->total_price) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                            <dt class="text-slate-500">{{ __('ui.platform_commission') }}</dt>
                            <dd class="font-semibold text-slate-700">- {{ $money($booking->platform_fee) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="font-bold text-slate-900">{{ __('ui.net_revenue') }}</dt>
                            <dd class="text-lg font-black text-sagara-blue">{{ $money($booking->vendor_revenue) }}</dd>
                        </div>

                        @if ($booking->cancel_fee > 0)
                            <div class="flex justify-between gap-4 py-2 border-t border-slate-100">
                                <dt class="text-slate-500">Denda pembatalan</dt>
                                <dd class="font-semibold text-slate-900">{{ $money($booking->cancel_fee) }}</dd>
                            </div>
                        @endif

                        @if ($booking->payout_id)
                            <div class="mt-1 text-[11px] text-slate-400">
                                <i class="fa-solid fa-circle-check text-emerald-500"></i> Termasuk dalam payout #{{ $booking->payout_id }}
                            </div>
                        @endif
                    </dl>
                    <p class="text-[11px] text-slate-400 mt-3"><i class="fa-solid fa-circle-info"></i> {{ __('ui.revenue_note') }}</p>
                </div>

                {{-- PENGELOLAAN --}}
                @if ($booking->status === 'confirmed')
                    <div class="{{ $card }}">
                        <h2 class="{{ $h2 }}">Pengelolaan</h2>
                        <a href="{{ $isCar ? route('partner.rentals.index') : route('partner.stays.index') }}"
                           class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:underline">
                            <i class="fa-solid {{ $isCar ? 'fa-car' : 'fa-bed' }}"></i>
                            {{ $isCar ? 'Kelola pengambilan & pengembalian' : 'Kelola kedatangan & keberangkatan tamu' }}
                        </a>
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection