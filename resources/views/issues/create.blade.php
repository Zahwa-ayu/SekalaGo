@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-14 space-y-6">
        <div>
            <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-chevron-left mr-1"></i> {{ $booking->booking_code }}</a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ __('ui.issue_report') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $booking->hotel_name }} &middot; {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('issues.store', $booking) }}" enctype="multipart/form-data"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-5">
            @csrf

            <div>
                <label class="{{ $label }}">{{ __('ui.issue_category') }}</label>
                <select name="category" required class="{{ $input }}">
                    <option value="">-</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ __('ui.issue_cat_' . $category) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.issue_description') }}</label>
                <textarea name="description" rows="6" required minlength="15" maxlength="2000" placeholder="{{ __('ui.issue_description_hint') }}" class="{{ $input }}">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.issue_evidence') }} <span class="normal-case font-semibold">({{ __('ui.optional') }})</span></label>
                <input type="file" name="evidence" accept="image/jpeg,image/png,image/webp,application/pdf"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100">
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.payout_proof_hint') }}</p>
            </div>

            <p class="text-xs text-slate-500">{{ __('ui.issue_flow_note') }}</p>
            <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">{{ __('ui.issue_submit') }}</button>
        </form>
    </section>
@endsection