@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    {{-- BANNER BIRU --}}
    <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center gap-2 text-sm font-bold text-white hover:underline">
                <i class="fa-solid fa-arrow-left"></i> {{ $booking->booking_code }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white drop-shadow-sm">{{ __('ui.issue_report') }}</h1>
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

        <form method="POST" action="{{ route('issues.store', $booking) }}" enctype="multipart/form-data"
              class="bg-white rounded-3xl border border-slate-100 shadow-xl p-6 sm:p-8 space-y-6">
            @csrf

            {{-- RINGKASAN PESANAN --}}
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 break-words">{{ $booking->hotel_name }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    <i class="fa-solid fa-calendar-days text-sky-500 mr-1"></i>
                    {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
                </p>
            </div>

            {{-- ISI LAPORAN --}}
            <div class="space-y-5">
                <div>
                    <label for="category" class="{{ $label }}">{{ __('ui.issue_category') }}</label>
                    <select id="category" name="category" required class="{{ $input }}">
                        <option value="">-</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ __('ui.issue_cat_' . $category) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="{{ $label }}">{{ __('ui.issue_description') }}</label>
                    <textarea id="description" name="description" rows="6" required minlength="15" maxlength="2000"
                              placeholder="{{ __('ui.issue_description_hint') }}" class="{{ $input }}">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="evidence" class="{{ $label }}">{{ __('ui.issue_evidence') }} <span class="normal-case font-semibold">({{ __('ui.optional') }})</span></label>
                    <input id="evidence" type="file" name="evidence" accept="image/jpeg,image/png,image/webp,application/pdf"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100">
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.payout_proof_hint') }}</p>
                </div>
            </div>

            {{-- KIRIM --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-xs text-slate-500">{{ __('ui.issue_flow_note') }}</p>
                <button class="shrink-0 px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold shadow-lg shadow-sky-500/20 transition">
                    {{ __('ui.issue_submit') }}
                </button>
            </div>
        </form>
    </section>
@endsection