@extends('layouts.app')

@section('content')
    @php
        $isCar    = $booking->isCar();
        $employee = $booking->user?->companyUser;
        $fmt      = fn ($d, $f = 'd/m/Y H:i') => $d ? \Illuminate\Support\Carbon::parse($d)->format($f) : null;
        $money    = fn ($v) => format_price($v, 'IDR');
        $yesNo    = fn ($v) => $v ? 'Ya' : 'Tidak';

        $card = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $h2   = 'font-extrabold text-slate-900 mb-3';

        $badge = match ($booking->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-700',
            'pending_approval', 'pending_payment' => 'bg-amber-100 text-amber-700',
            'cancelled', 'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };

        $overLimit = $booking->policy_limit !== null && $booking->total_price > $booking->policy_limit;

        // Rincian pesanan
        $stay = [
            [$isCar ? 'Kendaraan' : __('ui.hotel'), $booking->hotel_name],
            [$isCar ? 'Layanan' : __('ui.room_name'), $booking->room_name],
            [$isCar ? 'Mulai sewa' : __('ui.check_in'), $fmt($booking->check_in, 'd/m/Y')],
            [$isCar ? 'Selesai sewa' : __('ui.check_out'), $fmt($booking->check_out, 'd/m/Y')],
            [$isCar ? 'Lama sewa' : 'Malam', $booking->nights . ($isCar ? ' hari' : ' malam')],
            [$isCar ? 'Penumpang' : 'Tamu', $booking->guests],
            ['Nama tamu', $booking->guest_name],
            ['Dipesan pada', $fmt($booking->created_at)],
        ];

        // Khusus mobil
        $car = [
            ['Dengan supir', $yesNo($booking->with_driver)],
            ['Lokasi jemput', $booking->pickup_location],
            ['Tujuan', $booking->destination],
            ['Batas pengembalian', $fmt($booking->due_at)],
            ['Diambil', $fmt($booking->picked_up_at)],
            ['Dikembalikan', $fmt($booking->returned_at)],
            ['Terlambat', $booking->late_minutes > 0 ? $booking->late_minutes . ' menit' : null],
        ];

        // Karyawan & perjalanan dinas
        $trip = [
            ['Karyawan', $employee?->displayName() ?? $booking->user?->email],
            ['Email', $booking->user?->email],
            ['Departemen', $booking->department],
            ['Cost center', $booking->cost_center],
            ['Keperluan', $booking->trip_purpose],
            ['Telepon kontak', $booking->contact_phone],
            ['Permintaan khusus', $booking->special_requests],
        ];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">

        <a href="{{ route('corporate.bookings.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:underline">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.company_bookings') }}
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
                    <h2 class="{{ $h2 }}">Karyawan &amp; perjalanan dinas</h2>
                    <dl>
                        @foreach ($trip as [$label, $value])
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
                            @if ($booking->cancelled_at)
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                    <dt class="text-slate-500">Waktu</dt>
                                    <dd class="font-semibold text-slate-900">{{ $fmt($booking->cancelled_at) }}</dd>
                                </div>
                            @endif
                            @if ($booking->cancel_fee > 0)
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                    <dt class="text-slate-500">Denda pembatalan</dt>
                                    <dd class="font-semibold text-red-600">{{ $money($booking->cancel_fee) }}</dd>
                                </div>
                            @endif
                            @if ($booking->refund_amount > 0)
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                    <dt class="text-slate-500">{{ __('ui.refund_amount') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ $money($booking->refund_amount) }}</dd>
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

                {{-- BIAYA & KEBIJAKAN --}}
                <div class="{{ $card }}">
                    <h2 class="{{ $h2 }}">Biaya &amp; kebijakan</h2>
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
                            <dd class="text-lg font-black {{ $overLimit ? 'text-red-600' : 'text-slate-900' }}">{{ $money($booking->total_price) }}</dd>
                        </div>
                        @if ($booking->policy_limit !== null)
                            <div class="flex justify-between gap-4 py-2">
                                <dt class="text-slate-500">{{ __('ui.limit_label') }}</dt>
                                <dd class="font-semibold text-slate-900">{{ $money($booking->policy_limit) }}</dd>
                            </div>
                            @if ($overLimit)
                                <div class="mt-1 text-[11px] font-bold text-red-600"><i class="fa-solid fa-triangle-exclamation"></i> Melebihi batas kebijakan</div>
                            @endif
                        @endif
                    </dl>
                </div>

                {{-- PERSETUJUAN --}}
                @if ($booking->approval_reason || $booking->decision_note || $booking->decided_at || $booking->status === 'pending_approval')
                    <div class="{{ $card }}">
                        <h2 class="{{ $h2 }}">Persetujuan</h2>
                        <dl class="text-sm">
                            @if ($booking->approval_reason)
                                <div class="py-2 border-b border-slate-100">
                                    <dt class="text-slate-500 mb-1">Perlu persetujuan karena</dt>
                                    <dd class="font-semibold text-amber-600">{{ __('ui.approval_reason_' . $booking->approval_reason) }}</dd>
                                </div>
                            @endif
                            @if ($booking->decided_at)
                                <div class="flex justify-between gap-4 py-2 border-b border-slate-100">
                                    <dt class="text-slate-500">Diputuskan</dt>
                                    <dd class="font-semibold text-slate-900">{{ $fmt($booking->decided_at) }}</dd>
                                </div>
                            @endif
                            @if ($booking->decision_note)
                                <div class="py-2">
                                    <dt class="text-slate-500 mb-1">Catatan</dt>
                                    <dd class="font-semibold text-slate-900 whitespace-pre-line break-words">{{ $booking->decision_note }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($booking->status === 'pending_approval')
                            <div class="mt-4 space-y-3">
                                <form method="POST" action="{{ route('corporate.bookings.approve', $booking) }}">
                                    @csrf
                                    <button class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                                        <i class="fa-solid fa-check mr-1"></i> {{ __('ui.approve') }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('corporate.bookings.reject', $booking) }}" class="space-y-2">
                                    @csrf
                                    <textarea name="decision_note" rows="2" maxlength="500" required
                                              placeholder="{{ __('ui.rejection_reason_label') }}"
                                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                                    <button class="w-full px-4 py-2.5 rounded-xl bg-red-50 text-red-600 text-sm font-bold hover:bg-red-100 transition">
                                        <i class="fa-solid fa-xmark mr-1"></i> {{ __('ui.reject') }}
                                    </button>
                                </form>

                                @if ($booking->expires_at)
                                    <div class="text-[11px] text-slate-400">{{ __('ui.hold_until', ['time' => $booking->expires_at->format('d/m H:i')]) }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection