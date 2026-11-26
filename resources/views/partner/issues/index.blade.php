@extends('layouts.app')

@section('content')
    @php
        $tone = ['open' => 'bg-amber-100 text-amber-700', 'in_review' => 'bg-sky-100 text-sky-700', 'resolved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-slate-100 text-slate-500'];
    @endphp

    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.issue_reports') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.partner_issues_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        @php
            $select = 'rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30';
            $views  = [
                'active'         => __('ui.issue_tab_active'),
                'needs_response' => __('ui.issue_tab_needs_response'),
                'resolved'       => __('ui.issue_status_resolved'),
                'rejected'       => __('ui.issue_status_rejected'),
                'all'            => __('ui.all'),
            ];
            $categories = [
                ''                 => __('ui.all_categories'),
                'not_as_described' => __('ui.issue_cat_not_as_described'),
                'not_available'    => __('ui.issue_cat_not_available'),
                'service_issue'    => __('ui.issue_cat_service_issue'),
                'refund_problem'   => __('ui.issue_cat_refund_problem'),
                'other'            => __('ui.issue_cat_other'),
            ];
        @endphp

        <x-search-bar :placeholder="__('ui.issue_search')" :except="['view', 'category']">
            <select name="view" onchange="this.form.submit()" class="{{ $select }}" aria-label="{{ __('ui.account_status') }}">
                @foreach ($views as $value => $label)
                    <option value="{{ $value }}" @selected($view === $value)>{{ $label }}@if (isset($counts[$value])) ({{ $counts[$value] }})@endif</option>
                @endforeach
            </select>

            <select name="category" onchange="this.form.submit()" class="{{ $select }}" aria-label="{{ __('ui.issue_category') }}">
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-search-bar>

        @forelse ($reports as $report)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $tone[$report->status] }}">{{ __('ui.issue_status_' . $report->status) }}</span>
                        <span class="font-mono text-xs text-slate-500">{{ $report->code }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div class="text-sm font-bold text-slate-900">{{ __('ui.issue_cat_' . $report->category) }}
                    <span class="font-semibold text-slate-400">&middot; {{ $report->booking->booking_code }} &middot; {{ $report->booking->hotel_name }}</span></div>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $report->description }}</p>

                @if ($report->evidence_path)
                    <a href="{{ route('issues.evidence', $report) }}" target="_blank" rel="noopener" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-paperclip"></i> {{ __('ui.issue_evidence') }}</a>
                @endif

                @if ($report->resolution)
                    <div class="rounded-xl bg-sky-50 border border-sky-100 px-4 py-2.5 text-xs text-slate-700"><strong>{{ __('ui.issue_resolution') }}:</strong> {{ $report->resolution }}</div>
                @endif

                @if (in_array($report->status, ['open', 'in_review'], true))
                    <form method="POST" action="{{ route('partner.issues.respond', $report) }}" class="space-y-2">
                        @csrf
                        <textarea name="partner_response" rows="2" maxlength="1000" required placeholder="{{ __('ui.issue_response_placeholder') }}"
                                  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">{{ old('partner_response', $report->partner_response) }}</textarea>
                        <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-700 text-xs font-bold transition">{{ __('ui.issue_respond') }}</button>
                    </form>
                @elseif ($report->partner_response)
                    <p class="text-xs text-slate-500"><strong>{{ __('ui.issue_partner_response') }}:</strong> {{ $report->partner_response }}</p>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center text-slate-400 text-sm">{{ __('ui.issue_empty') }}</div>
        @endforelse

        {{ $reports->links() }}
    </div>
@endsection