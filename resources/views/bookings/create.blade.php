@extends('layouts.app')

@section('content')
    @php $total = $roomType->base_price * $nights; @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.confirm_booking') }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- RINGKASAN MENGINAP --}}
        <div class="bg-gradient-to-r from-sagara-navy via-sagara-blue to-sagara-navy rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <div class="w-12 h-12 rounded-xl bg-sagara-teal/20 flex items-center justify-center text-sagara-teal text-xl mb-4">
                <i class="fa-solid fa-hotel"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold">{{ $roomType->hotel->name }}</h2>
            <p class="text-sky-200 text-sm mt-1">{{ $roomType->name }} ({{ __('ui.capacity') }}: {{ $roomType->capacity }})</p>
            <p class="text-sm mt-4 pt-4 border-t border-white/15 flex items-start gap-2">
                <i class="fa-solid fa-calendar-days text-sagara-teal mt-0.5"></i>
                {{ __('ui.stay_summary', [
                    'in' => $checkIn->format('d/m/Y'),
                    'out' => $checkOut->format('d/m/Y'),
                    'count' => $nights,
                    'guests' => $guests,
                ]) }}
            </p>
        </div>

        {{-- RINCIAN HARGA --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex justify-between px-6 py-4">
                    <dt class="text-slate-500">{{ __('ui.price_per_night') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ format_price($roomType->base_price) }}</dd>
                </div>
                <div class="flex justify-between px-6 py-4">
                    <dt class="text-slate-500">{{ __('ui.nights') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $nights }}</dd>
                </div>
                <div class="flex justify-between items-center px-6 py-4 bg-sky-50/60">
                    <dt class="font-bold text-slate-900">{{ __('ui.total_price') }}</dt>
                    <dd class="text-xl font-black text-sagara-blue">{{ format_price($total) }}</dd>
                </div>
            </dl>
            <p class="px-6 py-3 text-[11px] text-slate-500 bg-slate-50 border-t border-slate-100">
                <i class="fa-solid fa-circle-info"></i> {{ __('ui.charged_in_idr', ['amount' => format_price($total, 'IDR')]) }}
            </p>
        </div>

        {{-- FORM --}}
        @if ($isOpen)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
                <form method="POST" action="{{ route('bookings.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="room_type" value="{{ $roomType->id }}">
                    <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
                    <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
                    <input type="hidden" name="guests" value="{{ $guests }}">

                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-user text-sagara-blue"></i> {{ __('ui.guest_name') }}
                        </label>
                        <input name="guest_name" required
                               value="{{ old('guest_name', auth()->user()->customer?->full_name) }}"
                               class="w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none">
                    </div>

                    <button class="w-full py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-credit-card"></i> {{ __('ui.continue_to_payment') }}
                    </button>
                </form>
            </div>
        @else
            <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.room_unavailable') }}
            </div>
        @endif
    </section>
@endsection