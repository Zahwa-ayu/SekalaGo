@extends('layouts.app')

@section('content')
    @php
        $badge = match ($booking->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-700',
            'pending_payment', 'pending_approval' => 'bg-amber-100 text-amber-700',
            'cancelled', 'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };
    @endphp

    {{-- BANNER GELAP --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-sky-900 pt-24 pb-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ route('bookings.index') }}" class="text-xs font-bold text-sky-300 hover:text-white transition">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.my_bookings') }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-mono tracking-wide">{{ $booking->booking_code }}</h1>
            <span class="inline-block px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
        </div>
    </div>

    <section class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 pb-12 space-y-6">

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm shadow-md">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- DETAIL PESANAN (hotel atau rental mobil) --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-lg p-6 sm:p-8 space-y-5">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">{{ $booking->hotel_name }}</h2>
                <p class="text-sm text-slate-500">{{ $booking->room_name }}</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 text-sm">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-calendar-days"></i></div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ $booking->isCar() ? __('ui.pickup_date') . ' - ' . __('ui.return_date') : __('ui.check_in') . ' - ' . __('ui.check_out') }}
                        </div>
                        <div class="font-bold text-slate-900">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</div>
                        <div class="text-xs text-slate-500">{{ $booking->durationLabel() }}, {{ $booking->guests }} {{ $booking->isCar() ? __('ui.passengers') : __('ui.guests') }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $booking->isCar() ? __('ui.renter_name') : __('ui.guest_name') }}</div>
                        <div class="font-bold text-slate-900">{{ $booking->guest_name }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-credit-card"></i></div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payment') }}</div>
                        <div class="font-bold text-slate-900">{{ __('ui.payment_status_' . $booking->payment_status) }}</div>
                        <div class="text-xs text-slate-500">
                            @if ($booking->payment_method === 'deposit') {{ __('ui.paid_by_deposit') }} @endif
                            @if ($booking->payment_reference) ({{ $booking->payment_reference }}) @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-coins"></i></div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.total_price') }}</div>
                        <div class="text-xl font-black text-slate-900">{{ $booking->displayTotal() }}</div>
                        <div class="text-xs text-slate-500">{{ __('ui.charged_in_idr', ['amount' => format_price($booking->total_price, 'IDR')]) }}</div>
                    </div>
                </div>
            </div>

            @if ($booking->isCar())
                <div class="pt-5 border-t border-slate-100 text-sm text-slate-600 space-y-2">
                    <div><i class="fa-solid fa-location-dot text-sky-500 w-5"></i> {{ $booking->pickup_location }}</div>

                    @if ($booking->with_driver && $booking->driver_fee_per_day !== null)
                        <div>
                            <i class="fa-solid fa-user-tie text-sky-500 w-5"></i>
                            {{ __('ui.driver_fee_estimate_notice', ['amount' => format_price($booking->driver_fee_per_day, 'IDR')]) }}
                        </div>
                    @endif

                    <div><i class="fa-solid fa-id-card text-sky-500 w-5"></i> {{ __('ui.bring_id_notice') }}</div>

                    @foreach ($booking->extensions as $extension)
                        <div class="text-xs text-slate-500">
                            <i class="fa-solid fa-calendar-plus text-sky-500 w-5"></i>
                            {{ __('ui.extension_history', [
                                'days'   => $extension->days,
                                'date'   => $extension->new_check_out->format('d/m/Y'),
                                'amount' => format_price($extension->amount, 'IDR'),
                            ]) }}
                        </div>
                    @endforeach

                    @if ($stage = $booking->rentalStage())
                        <div class="pt-1">
                            <i class="fa-solid fa-flag-checkered text-sky-500 w-5"></i>
                            <strong class="text-slate-900">{{ __('ui.rental_stage_' . $stage) }}</strong>
                            @if ($stage === 'returned' && $booking->late_fee > 0)
                                &middot; {{ __('ui.late_fee') }}: {{ format_price($booking->late_fee, 'IDR') }}
                                ({{ __('ui.extras_paid_to_vendor') }})
                            @endif
                        </div>

                        @if ($stage === 'upcoming' && $booking->isAtRisk())
                            <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold px-4 py-3">
                                {{ __('ui.rental_at_risk_notice') }}
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        {{-- DIBATALKAN --}}
        @if ($booking->status === 'cancelled')
            <div class="bg-white rounded-3xl border border-red-100 shadow-md p-6 text-sm space-y-1">
                <div class="font-extrabold text-red-600">
                    {{ match ($booking->cancelled_by) {
                        'admin'   => __('ui.cancelled_by_admin'),
                        'partner' => __('ui.cancelled_by_partner'),
                        default   => __('ui.cancelled_by_customer'),
                    } }}
                    <span class="font-semibold text-slate-400">({{ $booking->cancelled_at->format('d/m/Y H:i') }})</span>
                </div>
                @if ($booking->cancellation_reason)
                    <div class="text-slate-600">{{ __('ui.cancellation_reason_label') }}: {{ $booking->cancellation_reason }}</div>
                @endif
                @if ($booking->refund_amount > 0)
                    <div class="text-slate-600">
                        {{ __('ui.refund_amount') }}: <strong class="text-slate-900">{{ format_price($booking->refund_amount, 'IDR') }}</strong>
                        ({{ $booking->refund_reference }})
                    </div>
                @endif
            </div>
        @endif

        {{-- DITOLAK FINANCE --}}
        @if ($booking->status === 'rejected')
            <div class="rounded-3xl border border-red-200 bg-red-50 text-red-700 px-6 py-5 text-sm shadow-md">
                <strong>{{ __('ui.rejected_by_finance') }}</strong>
                @if ($booking->decision_note): {{ $booking->decision_note }} @endif
            </div>
        @endif

        {{-- MENUNGGU PERSETUJUAN FINANCE --}}
        @if ($booking->status === 'pending_approval')
            <div class="bg-white rounded-3xl border border-amber-200 shadow-md p-6 sm:p-8 space-y-4">
                <div class="text-sm text-amber-700">
                    <i class="fa-regular fa-clock mr-1"></i>
                    {{ __('ui.awaiting_approval_notice', ['time' => $booking->expires_at->format('d/m/Y H:i')]) }}
                    <div class="mt-1 text-slate-600">{{ __('ui.your_limit') }}: <strong>{{ format_price($booking->policy_limit, 'IDR') }}</strong></div>
                </div>

                <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                      onsubmit="return confirm(@js(__('ui.cancel_confirm')))">
                    @csrf
                    <button class="px-5 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition">
                        {{ __('ui.withdraw_request') }}
                    </button>
                </form>
            </div>

        {{-- MENUNGGU PEMBAYARAN --}}
        @elseif ($booking->status === 'pending_payment')
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('bookings.pay', $booking) }}"
                   class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                    <i class="fa-solid fa-credit-card mr-1"></i> {{ __('ui.pay_now') }}
                </a>

                <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                      onsubmit="return confirm(@js(__('ui.cancel_confirm')))">
                    @csrf
                    <button class="px-5 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition">
                        {{ __('ui.cancel_booking') }}
                    </button>
                </form>
            </div>

        {{-- TERKONFIRMASI --}}
        @elseif ($booking->status === 'confirmed')
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-6">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('bookings.ticket', $booking) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition">
                        <i class="fa-solid fa-download"></i> {{ __('ui.download_ticket') }}
                    </a>

                    @if ($booking->canBeExtended())
                        <a href="{{ route('bookings.extend', $booking) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md transition">
                            <i class="fa-solid fa-calendar-plus"></i> {{ __('ui.extend_rental') }}
                        </a>
                    @endif
                </div>

                <div class="pt-6 border-t border-slate-100 space-y-3">
                    <h2 class="text-base font-extrabold text-slate-900">{{ __('ui.cancel_booking') }}</h2>

                    <p class="text-sm text-slate-600">
                        {{ __('ui.free_cancel_until', ['date' => $booking->freeCancelDeadline()->format('d/m/Y')]) }}
                    </p>

                    @if ($booking->canBeCancelledByCustomer())
                        <p class="text-sm text-slate-600">{{ __('ui.refund_full_notice', ['amount' => format_price($booking->total_price, 'IDR')]) }}</p>

                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                              onsubmit="return confirm(@js(__('ui.cancel_confirm')))" class="space-y-3">
                            @csrf
                            <textarea name="cancellation_reason" rows="2" maxlength="500"
                                      placeholder="{{ __('ui.cancel_reason') }}"
                                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500"></textarea>
                            <button class="px-5 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition">
                                {{ __('ui.cancel_booking') }}
                            </button>
                        </form>
                    @else
                        <p class="text-sm font-semibold text-red-600">{{ __('ui.cancel_not_allowed') }}</p>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection