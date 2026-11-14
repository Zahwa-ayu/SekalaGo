@php
    $tone = [
        'overdue'  => 'bg-red-100 text-red-700',
        'ongoing'  => 'bg-sky-100 text-sagara-blue',
        'upcoming' => 'bg-amber-100 text-amber-700',
        'returned' => 'bg-emerald-100 text-emerald-700',
    ][$stage];

    $lateMinutes = $lateInfo[0] ?? 0;
    $lateFee     = $lateInfo[1] ?? 0;
@endphp

<div class="bg-white rounded-3xl border {{ $stage === 'overdue' ? 'border-red-200' : 'border-slate-100' }} shadow-md p-5 mb-3 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="min-w-0 space-y-1">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $tone }}">{{ __('ui.rental_stage_' . $stage) }}</span>
            <span class="font-mono text-xs text-slate-400">{{ $booking->booking_code }}</span>
        </div>

        <div class="font-extrabold text-slate-900">
            {{ $booking->hotel_name }}
            <span class="text-xs font-semibold text-slate-500">/ {{ $booking->room_name }}</span>
        </div>

        <div class="text-xs text-slate-500">
            {{ $booking->guest_name }} &middot;
            {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }} &middot;
            {{ __('ui.due_at_label') }} {{ $booking->due_at?->format('d/m H:i') }}
        </div>

        @if ($stage === 'overdue')
            <div class="text-xs font-bold text-red-600">
                {{ __('ui.late_by', ['hours' => intdiv($lateMinutes, 60), 'minutes' => $lateMinutes % 60]) }}
                @if ($lateFee > 0) &middot; {{ __('ui.late_fee_computed') }}: {{ format_price($lateFee, 'IDR') }} @endif
            </div>
        @elseif ($stage === 'returned' && $booking->late_minutes > 0)
            <div class="text-xs text-slate-500">
                @if ($booking->extensions->isNotEmpty())
                    <div class="text-[11px] font-bold text-sky-600">
                        <i class="fa-solid fa-calendar-plus"></i> {{ __('ui.extended_times', ['count' => $booking->extensions->count()]) }}
                    </div>
                @endif
                {{ __('ui.late_by', ['hours' => intdiv($booking->late_minutes, 60), 'minutes' => $booking->late_minutes % 60]) }}
                &middot; {{ __('ui.late_fee') }}: <strong>{{ format_price($booking->late_fee, 'IDR') }}</strong>
                @if ($booking->late_fee !== $booking->late_fee_computed)
                    ({{ __('ui.late_fee_computed') }}: {{ format_price($booking->late_fee_computed, 'IDR') }})
                @endif
            </div>
        @endif
    </div>

    <div>
        @if ($stage === 'upcoming')
            <form method="POST" action="{{ route('partner.rentals.pickup', $booking) }}">
                @csrf
                <button @disabled($booking->check_in->gt(today()))
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed">
                    {{ __('ui.rental_mark_picked_up') }}
                </button>
            </form>
        @elseif ($stage === 'ongoing' || $stage === 'overdue')
            <form method="POST" action="{{ route('partner.rentals.return', $booking) }}" class="flex flex-col sm:flex-row gap-2 sm:items-end">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.late_fee') }} (IDR)</label>
                    <input type="number" name="late_fee" min="0" placeholder="{{ $lateFee }}" title="{{ __('ui.late_fee_input_hint') }}"
                           class="w-36 rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
                </div>
                <button class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition whitespace-nowrap">
                    {{ __('ui.rental_mark_returned') }}
                </button>
            </form>
            <p class="text-[10px] text-slate-400 mt-1 max-w-56">{{ __('ui.late_fee_input_hint') }}</p>
        @endif
    </div>
</div>