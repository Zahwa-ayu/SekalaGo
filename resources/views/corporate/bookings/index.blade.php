@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.company_bookings') }}</h1>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FILTER STATUS --}}
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (['pending_approval', 'confirmed', 'rejected', 'cancelled', 'expired'] as $s)
                <a href="{{ route('corporate.bookings.index', ['status' => $s]) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                          {{ $status === $s ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                    {{ __('ui.booking_status_' . $s) }}
                    <span class="ml-1 opacity-70">({{ $counts->get($s, 0) }})</span>
                </a>
            @endforeach
            <a href="{{ route('corporate.bookings.index', ['status' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                      {{ $status === 'all' ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                {{ __('ui.all') }}
            </a>
        </div>

        {{-- TABEL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.booking_code') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.employees') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.hotel') }} / {{ __('ui.room_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.total_price') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.limit_label') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            @php
                                $badge = match ($booking->status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'pending_approval', 'pending_payment' => 'bg-amber-100 text-amber-700',
                                    'cancelled', 'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition align-top">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-sagara-blue whitespace-nowrap">{{ $booking->booking_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $booking->user->companyUser?->displayName() ?? $booking->user->email }}</div>
                                    @if ($booking->department)
                                        <div class="text-[11px] text-slate-500">{{ $booking->department }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $booking->hotel_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $booking->room_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($booking->total_price, 'IDR') }}</td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $booking->policy_limit !== null ? format_price($booking->policy_limit, 'IDR') : '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
                                    @if ($booking->decision_note)
                                        <div class="text-[11px] text-slate-500 mt-1">{{ $booking->decision_note }}</div>
                                    @endif
                                    @if ($booking->status === 'pending_approval' && $booking->approval_reason)
                                        <div class="text-[11px] text-amber-600 mt-1">{{ __('ui.approval_reason_' . $booking->approval_reason) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($booking->status === 'pending_approval')
                                        <form method="POST" action="{{ route('corporate.bookings.approve', $booking) }}" class="mb-2">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                                                <i class="fa-solid fa-check"></i> {{ __('ui.approve') }}
                                            </button>
                                        </form>

                                        <details>
                                            <summary class="cursor-pointer list-none inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-bold hover:bg-red-100 transition">
                                                <i class="fa-solid fa-xmark"></i> {{ __('ui.reject') }}
                                            </summary>
                                            <form method="POST" action="{{ route('corporate.bookings.reject', $booking) }}" class="mt-2 w-56 space-y-2">
                                                @csrf
                                                <textarea name="decision_note" rows="2" maxlength="500" required
                                                          placeholder="{{ __('ui.rejection_reason_label') }}"
                                                          class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sagara-blue"></textarea>
                                                <button class="w-full px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">{{ __('ui.reject') }}</button>
                                            </form>
                                        </details>

                                        <div class="text-[11px] text-slate-400 mt-2">{{ __('ui.hold_until', ['time' => $booking->expires_at->format('d/m H:i')]) }}</div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                    {{ __('ui.no_bookings') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection