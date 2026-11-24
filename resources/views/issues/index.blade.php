@extends('layouts.app')

@section('content')
    @php
        $tone = ['open' => 'bg-amber-100 text-amber-700', 'in_review' => 'bg-sky-100 text-sky-700', 'resolved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-slate-100 text-slate-500'];
    @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-14 space-y-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.issue_my_reports') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.issue_my_reports_hint') }}</p>
        </div>

        @forelse ($reports as $report)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $tone[$report->status] }}">{{ __('ui.issue_status_' . $report->status) }}</span>
                        <span class="font-mono text-xs text-slate-500">{{ $report->code }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div class="text-sm font-bold text-slate-900">{{ __('ui.issue_cat_' . $report->category) }}
                    <span class="font-semibold text-slate-400">&middot; <a href="{{ route('bookings.show', $report->booking) }}" class="hover:underline">{{ $report->booking->booking_code }}</a></span></div>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $report->description }}</p>

                @if ($report->evidence_path)
                    <a href="{{ route('issues.evidence', $report) }}" target="_blank" rel="noopener" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-paperclip"></i> {{ __('ui.issue_evidence') }}</a>
                @endif

                @if ($report->partner_response)
                    <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('ui.issue_partner_response') }}</div>
                        <p class="text-slate-600 whitespace-pre-line">{{ $report->partner_response }}</p>
                    </div>
                @endif

                @if ($report->resolution)
                    <div class="rounded-xl bg-sky-50 border border-sky-100 px-4 py-3 text-sm">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-0.5">{{ __('ui.issue_resolution') }}</div>
                        <p class="text-slate-700 whitespace-pre-line">{{ $report->resolution }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center text-slate-400 text-sm">{{ __('ui.issue_empty') }}</div>
        @endforelse

        {{ $reports->links() }}
    </section>
@endsection