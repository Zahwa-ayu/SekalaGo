@php
    $stage  = $booking->stayStage();
    $tone   = ['upcoming' => 'bg-amber-100 text-amber-700', 'in_house' => 'bg-sky-100 text-sagara-blue', 'departed' => 'bg-emerald-100 text-emerald-700', 'no_show' => 'bg-red-100 text-red-700'][$stage];
    $accent = ['upcoming' => 'border-l-amber-400', 'in_house' => 'border-l-sky-500', 'departed' => 'border-l-emerald-500', 'no_show' => 'border-l-red-400'][$stage];
    $late   = $section === 'arrivals' && $booking->check_in->lt(today());
@endphp

<div class="bg-white rounded-2xl border {{ $border }} border-l-4 {{ $accent }} shadow-sm hover:shadow-md transition p-5 mb-3 flex flex-col lg:flex-row lg:items-center justify-between gap-4">

    {{-- INFO TAMU --}}
    <div class="flex items-start gap-4 min-w-0">
        <div class="w-11 h-11 rounded-full bg-sagara-blue/10 text-sagara-blue flex items-center justify-center font-extrabold text-base shrink-0">
            {{ strtoupper(mb_substr($booking->guest_name, 0, 1)) }}
        </div>

        <div class="min-w-0 space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap {{ $tone }}">{{ __('ui.stay_stage_' . $stage) }}</span>
                @if ($late)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-red-100 text-red-700">{{ __('ui.stay_not_arrived_yet') }}</span>
                @endif
                <span class="font-mono text-[11px] text-slate-400">{{ $booking->booking_code }}</span>
            </div>

            <div class="font-extrabold text-slate-900 break-words">{{ $booking->guest_name }}</div>

            <div class="text-xs text-slate-500 break-words">
                <i class="fa-solid fa-hotel text-slate-400 mr-1"></i>{{ $booking->hotel_name }} <span class="text-slate-300">/</span> {{ $booking->room_name }}
            </div>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                <span><i class="fa-solid fa-calendar-days text-slate-400 mr-1"></i>{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</span>
                <span><i class="fa-solid fa-users text-slate-400 mr-1"></i>{{ $booking->guests }} {{ __('ui.guests') }}</span>
                @if ($booking->checked_in_at)
                    <span class="text-sky-600 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>{{ __('ui.stay_arrived_at', ['time' => $booking->checked_in_at->format('d/m H:i')]) }}</span>
                @endif
            </div>

            <div class="pt-1">
                @include('partials.booking-contact-block', ['booking' => $booking])
            </div>
        </div>
    </div>

    {{-- AKSI --}}
    <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100 lg:border-0 lg:pt-0 lg:justify-end shrink-0">
        <a href="{{ route('partner.bookings.show', $booking) }}"
           class="px-4 py-2 rounded-xl border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white text-xs font-bold transition whitespace-nowrap">
            {{ __('ui.view') }} <i class="fa-solid fa-chevron-right ml-1"></i>
        </a>

        @if ($section === 'arrivals')
            <form method="POST" action="{{ route('partner.stays.arrive', $booking) }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition whitespace-nowrap">{{ __('ui.stay_confirm_arrival') }}</button>
            </form>
            <form method="POST" action="{{ route('partner.stays.noshow', $booking) }}" onsubmit="return confirm(@js(__('ui.stay_no_show_confirm')))">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition whitespace-nowrap">{{ __('ui.stay_mark_no_show') }}</button>
            </form>
        @elseif ($section === 'in_house')
            <form method="POST" action="{{ route('partner.stays.depart', $booking) }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-sagara-navy hover:bg-sagara-blue text-white text-xs font-bold transition whitespace-nowrap">{{ __('ui.stay_confirm_departure') }}</button>
            </form>
        @elseif ($section === 'recent' && $stage === 'no_show' && ! $booking->payout_id && $booking->check_out->gt(today()))
            <form method="POST" action="{{ route('partner.stays.arrive', $booking) }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition whitespace-nowrap">{{ __('ui.stay_undo_no_show') }}</button>
            </form>
        @endif
    </div>
</div>