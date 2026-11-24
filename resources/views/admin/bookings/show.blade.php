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
        $payBadge = match ($booking->payment_status) {
            'paid' => 'bg-emerald-100 text-emerald-700',
            'refunded' => 'bg-sky-100 text-sky-700',
            'failed' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };

        // Rincian pesanan
        $stay = [
            [__('ui.partner'), $booking->partner?->company_name],
            [$isCar ? 'Kendaraan' : __('ui.hotel'), $booking->hotel_name],
            [$isCar ? 'Layanan' : __('ui.room_name'), $booking->room_name],
            [$isCar ? 'Mulai sewa' : __('ui.check_in'), $fmt($booking->check_in, 'd/m/Y')],
            [$isCar ? 'Selesai sewa' : __('ui.check_out'), $fmt($booking->check_out, 'd/m/Y')],
            [$isCar ? 'Lama sewa' : 'Malam', $booking->nights . ($isCar ? ' hari' : ' malam')],
            [$isCar ? 'Penumpang' : 'Tamu', $booking->guests],
            ['Dibuat', $fmt($booking->created_at)],
        ];

        // Khusus mobil
        $car = [
            ['Dengan supir', $yesNo($booking->with_driver)],
            ['Tarif supir / hari', $booking->with_driver ? $money($booking->driver_fee_per_day) : null],
            ['Lokasi jemput', $booking->pickup_location],
            ['Tujuan', $booking->destination],
            ['Keperluan', $booking->trip_purpose],
            ['SIM dikonfirmasi', $yesNo($booking->license_confirmed)],
            ['Batas pengembalian', $fmt($booking->due_at)],
            ['Diambil', $fmt($booking->picked_up_at)],
            ['Dikembalikan', $fmt($booking->returned_at)],
            ['Toleransi terlambat', $booking->late_grace_minutes . ' menit'],
            ['Terlambat', $booking->late_minutes > 0 ? $booking->late_minutes . ' menit' : null],
        ];

        // Pemesan & kontak
        $contact = [
            ['Nama tamu', $booking->guest_name],
            ['Akun pemesan', $booking->user?->name],
            ['Email akun', $booking->user?->email],
            ['Nama kontak', $booking->contact_name],
            ['Email kontak', $booking->contact_email],
            ['Telepon kontak', $booking->contact_phone],
            ['Perkiraan tiba', $booking->arrival_time],
            ['Departemen', $booking->department],
            ['Cost center', $booking->cost_center],
            ['Catatan permintaan', $booking->special_requests],
        ];

        // Pembayaran
        $payment = [
            ['Metode', $booking->payment_method],
            ['Referensi', $booking->payment_reference],
            ['Dibayar pada', $fmt($booking->paid_at)],
            ['Batas bayar', $booking->payment_status !== 'paid' ? $fmt($booking->expires_at) : null],
        ];

        $statusLabel = fn ($s) => __('ui.booking_status_' . $s);
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">

        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:underline">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.all_bookings') }}
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
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1.5 text-xs font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ $statusLabel($booking->status) }}</span>
                <span class="px-3 py-1.5 text-xs font-bold rounded-full whitespace-nowrap {{ $payBadge }}">
                    <i class="fa-solid fa-wallet mr-1"></i>{{ ucfirst($booking->payment_status) }}
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-5 py-4 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

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

                    <div class="flex flex-wrap gap-4 mt-4 text-xs font-bold">
                        @if ($booking->partner_id)
                            <a href="{{ route('admin.partners.show', $booking->partner_id) }}" class="text-sagara-blue hover:underline">
                                <i class="fa-solid fa-handshake mr-1"></i> Lihat mitra
                            </a>
                        @endif
                        @if (! $isCar && $booking->hotel_id)
                            <a href="{{ route('admin.hotels.show', $booking->hotel_id) }}" class="text-sagara-blue hover:underline">
                                <i class="fa-solid fa-hotel mr-1"></i> Lihat hotel
                            </a>
                        @endif
                    </div>
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
                    <h2 class="{{ $h2 }}">Pemesan &amp; kontak</h2>
                    <dl>
                        @foreach ($contact as [$label, $value])
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
                    <h2 class="{{ $h2 }}">Rincian biaya</h2>
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
                            <dd class="text-lg font-black text-slate-900">{{ $money($booking->total_price) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                            <dt class="text-slate-500">{{ __('ui.platform_commission') }}</dt>
                            <dd class="font-bold text-sagara-blue">{{ $money($booking->platform_fee) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2">
                            <dt class="text-slate-500">{{ __('ui.partner_payout') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $money($booking->vendor_revenue) }}</dd>
                        </div>
                        @if ($booking->payout_id)
                            <div class="mt-1 text-[11px] text-slate-400"><i class="fa-solid fa-circle-check text-emerald-500"></i> Sudah masuk payout #{{ $booking->payout_id }}</div>
                        @endif

                        @if ($booking->cancel_fee > 0 || $booking->refund_amount > 0)
                            <div class="mt-3 pt-3 border-t border-slate-100 space-y-1">
                                @if ($booking->cancel_fee > 0)
                                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Denda pembatalan</dt><dd class="font-semibold text-red-600">{{ $money($booking->cancel_fee) }}</dd></div>
                                @endif
                                @if ($booking->refund_amount > 0)
                                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('ui.refund_amount') }}</dt><dd class="font-semibold text-slate-900">{{ $money($booking->refund_amount) }}</dd></div>
                                    @if ($booking->refunded_at || $booking->refund_reference)
                                        <div class="text-[11px] text-slate-400 text-right">{{ $fmt($booking->refunded_at) }} {{ $booking->refund_reference }}</div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="{{ $card }}">
                    <h2 class="{{ $h2 }}">Pembayaran</h2>
                    <dl class="text-sm">
                        @foreach ($payment as [$label, $value])
                            @if (filled($value))
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100 last:border-0">
                                    <dt class="text-slate-500 shrink-0">{{ $label }}</dt>
                                    <dd class="font-semibold text-slate-900 text-right break-all min-w-0">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>

                {{-- AKSI ADMIN --}}
                @if ($booking->canBeCancelledByAdmin())
                    <div class="{{ $card }}">
                        <h2 class="{{ $h2 }}">Tindakan</h2>
                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}"
                              onsubmit="return confirm(@js(__('ui.cancel_confirm')))" class="space-y-2">
                            @csrf
                            <textarea name="cancellation_reason" rows="3" maxlength="500" required
                                      placeholder="{{ __('ui.admin_cancel_reason') }}"
                                      class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">{{ old('cancellation_reason') }}</textarea>
                            <button class="w-full px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition">
                                <i class="fa-solid fa-ban mr-1"></i> {{ __('ui.admin_cancel') }}
                            </button>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection