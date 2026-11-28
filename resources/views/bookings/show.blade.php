@extends('layouts.app')

@section('content')
    @php
        $badge = match ($booking->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-700',
            'pending_payment', 'pending_approval' => 'bg-amber-100 text-amber-700',
            'cancelled', 'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };

        $reviewState     = $booking->reviewStatus();
        $issueCategories = $booking->issueCategories();
        $myReview        = $booking->review;
        $myReports       = $booking->issueReports;

        $iconBox = 'w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0';
        $label   = 'text-[11px] font-bold uppercase tracking-wider text-slate-400';
        $btn     = 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition';
        $primary = $btn . ' bg-sky-500 hover:bg-sky-600 text-white shadow-lg shadow-sky-500/20';
        $outline = $btn . ' bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-700';
        $danger  = $btn . ' bg-white border border-red-200 text-red-600 hover:bg-red-50';
        $input   = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
    @endphp

    {{-- BANNER BIRU --}}
    <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white hover:underline">
                <i class="fa-solid fa-arrow-left"></i> {{ __('ui.my_bookings') }}
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-mono tracking-wide break-all">{{ $booking->booking_code }}</h1>
                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
            </div>
        </div>
    </div>

    <section class="relative z-10 w-full px-4 sm:px-6 lg:px-8 -mt-12 pb-12 space-y-6">

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm shadow-md">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SATU KARTU --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-xl divide-y divide-slate-100">

            {{-- DETAIL PESANAN --}}
            <div class="p-6 sm:p-8 space-y-5">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 break-words">{{ $booking->hotel_name }}</h2>
                    <p class="text-sm text-slate-500 break-words">{{ $booking->room_name }}</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 text-sm">
                    <div class="flex items-start gap-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-calendar-days"></i></div>
                        <div class="min-w-0">
                            <div class="{{ $label }}">{{ $booking->isCar() ? __('ui.pickup_date') . ' - ' . __('ui.return_date') : __('ui.check_in') . ' - ' . __('ui.check_out') }}</div>
                            <div class="font-bold text-slate-900">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-500">{{ $booking->durationLabel() }}, {{ $booking->guests }} {{ $booking->isCar() ? __('ui.passengers') : __('ui.guests') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-user"></i></div>
                        <div class="min-w-0">
                            <div class="{{ $label }}">{{ $booking->isCar() ? __('ui.renter_name') : __('ui.guest_name') }}</div>
                            <div class="font-bold text-slate-900 break-words">{{ $booking->guest_name }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-credit-card"></i></div>
                        <div class="min-w-0">
                            <div class="{{ $label }}">{{ __('ui.payment') }}</div>
                            <div class="font-bold text-slate-900">{{ __('ui.payment_status_' . $booking->payment_status) }}</div>
                            <div class="text-xs text-slate-500 break-all">
                                @if ($booking->payment_method === 'deposit') {{ __('ui.paid_by_deposit') }} @endif
                                @if ($booking->payment_reference) ({{ $booking->payment_reference }}) @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-coins"></i></div>
                        <div class="min-w-0">
                            <div class="{{ $label }}">{{ __('ui.total_price') }}</div>
                            <div class="text-xl font-black text-slate-900">{{ $booking->displayTotal() }}</div>
                            {{-- Hanya tampil bila berbeda dengan total di atas --}}
                            @if ($booking->displayTotal() !== format_price($booking->total_price, 'IDR'))
                                <div class="text-xs text-slate-500">{{ __('ui.charged_in_idr', ['amount' => format_price($booking->total_price, 'IDR')]) }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFO RENTAL MOBIL --}}
            @if ($booking->isCar())
                <div class="p-6 sm:p-8 text-sm text-slate-600 space-y-2">
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

            {{-- STATUS MENGINAP (hotel) --}}
            @if ($stay = $booking->stayStage())
                <div class="p-6 sm:p-8 text-sm text-slate-600 space-y-2">
                    <div>
                        <i class="fa-solid fa-flag-checkered text-sky-500 w-5"></i>
                        <strong class="text-slate-900">{{ __('ui.stay_stage_' . $stay) }}</strong>
                        @if ($stay === 'in_house' && $booking->checked_in_at) &middot; {{ __('ui.stay_arrived_at', ['time' => $booking->checked_in_at->format('d/m H:i')]) }} @endif
                    </div>
                    @if ($stay === 'no_show')
                        <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold px-4 py-3">{{ __('ui.stay_no_show_customer_note') }}</div>
                    @endif
                </div>
            @endif

            {{-- DATA PEMESAN --}}
            @if ($booking->contact_phone || $booking->special_requests || $booking->trip_purpose)
                <div class="p-6 sm:p-8 space-y-3">
                    <h2 class="text-base font-extrabold text-slate-900">{{ __('ui.booking_details_title') }}</h2>
                    @include('partials.booking-contact-block', ['booking' => $booking, 'showCorporate' => true])
                </div>
            @endif

            {{-- DIBATALKAN --}}
            @if ($booking->status === 'cancelled')
                <div class="p-6 sm:p-8 text-sm space-y-1">
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
                    @if ($booking->cancel_fee > 0)
                        <div class="text-slate-600">{{ __('ui.cancel_fee_amount') }}: <strong class="text-red-600">{{ format_price($booking->cancel_fee, 'IDR') }}</strong></div>
                    @endif

                    {{-- Refund lewat Midtrans diproses manual oleh admin --}}
                    @if ($booking->refund_reference && str_starts_with($booking->refund_reference, 'MANUAL-'))
                        <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold px-4 py-3">
                            {{ __('ui.refund_manual_customer') }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- DITOLAK FINANCE --}}
            @if ($booking->status === 'rejected')
                <div class="p-6 sm:p-8 text-sm text-red-700">
                    <strong>{{ __('ui.rejected_by_finance') }}</strong>
                    @if ($booking->decision_note): {{ $booking->decision_note }} @endif
                </div>
            @endif

            {{-- AKSI SESUAI STATUS --}}
            @if ($booking->status === 'pending_approval')
                <div class="p-6 sm:p-8 space-y-4">
                    <div class="text-sm text-amber-700">
                        <i class="fa-regular fa-clock mr-1"></i>
                        {{ __('ui.awaiting_approval_notice', ['time' => $booking->expires_at->format('d/m/Y H:i')]) }}
                        <div class="mt-1 text-slate-600">{{ __('ui.your_limit') }}: <strong>{{ format_price($booking->policy_limit, 'IDR') }}</strong></div>
                    </div>

                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                          data-confirm-form
                          data-title="{{ __('ui.withdraw_request') }}"
                          data-message="{{ __('ui.withdraw_modal_message') }}">
                        @csrf
                        <button class="{{ $danger }}">{{ __('ui.withdraw_request') }}</button>
                    </form>
                </div>
            @elseif ($booking->status === 'pending_payment')
                <div class="p-6 sm:p-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('bookings.pay', $booking) }}" class="{{ $primary }}">
                        <i class="fa-solid fa-credit-card"></i> {{ __('ui.pay_now') }}
                    </a>

                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                          data-confirm-form
                          data-title="{{ __('ui.cancel_modal_title') }}"
                          data-message="{{ __('ui.cancel_modal_message') }}">
                        @csrf
                        <button class="{{ $danger }}">{{ __('ui.cancel_booking') }}</button>
                    </form>
                </div>
            @elseif ($booking->status === 'confirmed')
                <div class="p-6 sm:p-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('bookings.ticket', $booking) }}" class="{{ $primary }}">
                        <i class="fa-solid fa-download"></i> {{ __('ui.download_ticket') }}
                    </a>

                    @if ($booking->canBeExtended())
                        <a href="{{ route('bookings.extend', $booking) }}" class="{{ $outline }}">
                            <i class="fa-solid fa-calendar-plus"></i> {{ __('ui.extend_rental') }}
                        </a>
                    @endif
                </div>
            @endif

            {{-- ULASAN DAN LAPORAN KENDALA --}}
            @if ($myReview || $reviewState === 'ok' || $issueCategories || $myReports->isNotEmpty())
                <div class="p-6 sm:p-8 space-y-4">
                    <h2 class="text-base font-extrabold text-slate-900">{{ __('ui.after_service_title') }}</h2>

                    @if ($myReview)
                        <div class="rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3 space-y-1">
                            <div class="text-amber-400">@foreach (range(1, 5) as $i)<i class="fa-solid fa-star {{ $i <= $myReview->rating ? '' : 'text-slate-200' }}"></i>@endforeach</div>
                            @if ($myReview->comment)<p class="text-sm text-slate-700 whitespace-pre-line">{{ $myReview->comment }}</p>@endif
                            @if ($myReview->partner_reply)<p class="text-xs text-slate-500 border-l-2 border-amber-200 pl-3"><strong>{{ __('ui.review_partner_reply') }}:</strong> {{ $myReview->partner_reply }}</p>@endif
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-3">
                        @if (! $myReview && $reviewState === 'ok')
                            <a href="{{ route('reviews.create', $booking) }}" class="{{ $outline }}">
                                <i class="fa-solid fa-star text-amber-400"></i> {{ __('ui.review_write') }}
                            </a>
                        @endif

                        @if ($issueCategories)
                            <a href="{{ route('issues.create', $booking) }}" class="{{ $outline }}">
                                <i class="fa-solid fa-life-ring"></i> {{ __('ui.issue_report') }}
                            </a>
                        @endif
                    </div>

                    @foreach ($myReports as $report)
                        <div class="text-xs text-slate-600">
                            <i class="fa-solid fa-life-ring text-sky-500 w-4"></i>
                            <a href="{{ route('issues.index') }}" class="font-mono font-bold hover:underline">{{ $report->code }}</a>
                            &middot; {{ __('ui.issue_cat_' . $report->category) }} &middot; <strong>{{ __('ui.issue_status_' . $report->status) }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- PEMBATALAN (pesanan terkonfirmasi) --}}
            @if ($booking->status === 'confirmed')
                @php $quote = $booking->cancellationQuote(); @endphp

                @if (in_array($quote['type'], ['free', 'fee']))
                    <details class="group" @if ($errors->any()) open @endif>
                        <summary class="flex items-center justify-between gap-3 px-6 sm:px-8 py-5 cursor-pointer list-none [&::-webkit-details-marker]:hidden text-sm font-bold text-slate-700">
                            {{ __('ui.cancel_booking') }}
                            <i class="fa-solid fa-chevron-down text-slate-400 transition group-open:rotate-180"></i>
                        </summary>

                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                              data-confirm-form
                              data-title="{{ __('ui.cancel_modal_title') }}"
                              data-message="{{ $quote['type'] === 'fee'
                                  ? __('ui.cancel_fee_confirm', ['fee' => format_price($quote['fee'], 'IDR'), 'amount' => format_price($quote['refund'], 'IDR')])
                                  : __('ui.cancel_modal_message') }}"
                              class="px-6 sm:px-8 pb-6 max-w-xl space-y-4">
                            @csrf

                            @if ($quote['type'] === 'fee')
                                <dl class="text-sm space-y-2">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">{{ __('ui.cancel_fee_amount') }} ({{ $quote['percent'] }}%)</dt>
                                        <dd class="font-bold text-red-600">{{ format_price($quote['fee'], 'IDR') }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">{{ __('ui.refund_amount') }}</dt>
                                        <dd class="font-bold text-slate-900">{{ format_price($quote['refund'], 'IDR') }}</dd>
                                    </div>
                                </dl>
                            @else
                                <div class="text-sm text-slate-600 space-y-1">
                                    <p>{{ __('ui.free_cancel_until', ['date' => $booking->freeCancelDeadline()->format('d/m/Y')]) }}</p>
                                    <p>{{ __('ui.refund_full_notice', ['amount' => format_price($booking->total_price, 'IDR')]) }}</p>
                                </div>
                            @endif

                            <textarea name="cancellation_reason" rows="2" maxlength="500" placeholder="{{ __('ui.cancel_reason') }}" class="{{ $input }}">{{ old('cancellation_reason') }}</textarea>

                            @if ($quote['type'] === 'fee')
                                <label class="flex items-start gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="accept_fee" value="1" required class="mt-0.5 rounded border-slate-300 text-sky-500">
                                    <span>{{ __('ui.cancel_fee_accept') }}</span>
                                </label>
                            @endif

                            <button class="{{ $danger }}">
                                {{ $quote['type'] === 'fee' ? __('ui.cancel_booking_with_fee') : __('ui.cancel_booking') }}
                            </button>
                        </form>
                    </details>
                @else
                    <p class="px-6 sm:px-8 py-5 text-sm text-slate-500">{{ __('ui.cancel_not_allowed') }}</p>
                @endif
            @endif
        </div>
    </section>

    @include('partials.confirm-modal')
@endsection