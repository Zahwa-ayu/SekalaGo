@php
    $stage = $booking->stayStage();
    $tone  = ['upcoming' => 'bg-amber-100 text-amber-700', 'in_house' => 'bg-sky-100 text-sagara-blue', 'departed' => 'bg-emerald-100 text-emerald-700', 'no_show' => 'bg-red-100 text-red-700'][$stage];
    $late  = $section === 'arrivals' && $booking->check_in->lt(today());
@endphp

<div class="bg-white rounded-2xl border {{ $border }} shadow-sm p-5 mb-3 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="min-w-0 space-y-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $tone }}">{{ __('ui.stay_stage_' . $stage) }}</span>
            @if ($late)<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-700">{{ __('ui.stay_not_arrived_yet') }}</span>@endif
            <span class="font-mono text-xs text-slate-400">{{ $booking->booking_code }}</span>
        </div>

        <div class="font-extrabold text-slate-900">
            {{ $booking->guest_name }}
            <span class="text-xs font-semibold text-slate-500">&middot; {{ $booking->hotel_name }} / {{ $booking->room_name }}</span>
        </div>

        <div class="text-xs text-slate-500">
            {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
            &middot; {{ $booking->guests }} {{ __('ui.guests') }}
            @if ($booking->checked_in_at) &middot; {{ __('ui.stay_arrived_at', ['time' => $booking->checked_in_at->format('d/m H:i')]) }} @endif
        </div>

        @include('partials.booking-contact-block', ['booking' => $booking])
    </div>

    <div class="flex flex-wrap gap-2">
        @if ($section === 'arrivals')
            <form method="POST" action="{{ route('partner.stays.arrive', $booking) }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">{{ __('ui.stay_confirm_arrival') }}</button>
            </form>
            <form method="POST" action="{{ route('partner.stays.noshow', $booking) }}" onsubmit="return confirm(@js(__('ui.stay_no_show_confirm')))">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition">{{ __('ui.stay_mark_no_show') }}</button>
            </form>
        @elseif ($section === 'in_house')
            <form method="POST" action="{{ route('partner.stays.depart', $booking) }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-sagara-navy hover:bg-sagara-blue text-white text-xs font-bold transition">{{ __('ui.stay_confirm_departure') }}</button>
            </form>
        @elseif ($section === 'recent' && $stage === 'no_show' && ! $booking->payout_id && $booking->check_out->gt(today()))
            <form method="POST" action="{{ route('partner.stays.arrive', $booking) }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition">{{ __('ui.stay_undo_no_show') }}</button>
            </form>
        @endif
    </div>
</div>