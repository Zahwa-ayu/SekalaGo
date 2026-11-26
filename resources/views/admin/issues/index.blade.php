@extends('layouts.app')

@section('content')
    @php
        $tone = ['open' => 'bg-amber-100 text-amber-700', 'in_review' => 'bg-sky-100 text-sky-700', 'resolved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-slate-100 text-slate-500'];
        $tabs = ['active' => 'issue_tab_active', 'resolved' => 'issue_status_resolved', 'rejected' => 'issue_status_rejected', 'all' => 'all'];
        $input = 'rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/30';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.issue_reports') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.admin_issues_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <x-search-bar :placeholder="__('ui.issue_search')" :except="['status']">
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                @foreach ([
                    'active' => __('ui.issue_tab_active') . ' (' . ($counts->get('open', 0) + $counts->get('in_review', 0)) . ')',
                    'open' => __('ui.issue_status_open') . ' (' . $counts->get('open', 0) . ')',
                    'in_review' => __('ui.issue_status_in_review') . ' (' . $counts->get('in_review', 0) . ')',
                    'resolved' => __('ui.issue_status_resolved') . ' (' . $counts->get('resolved', 0) . ')',
                    'rejected' => __('ui.issue_status_rejected') . ' (' . $counts->get('rejected', 0) . ')',
                    'all' => __('ui.all'),
                ] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-search-bar>

        <div class="space-y-4">
            @forelse ($reports as $report)
                <div class="bg-white rounded-3xl border {{ $report->status === 'open' ? 'border-amber-200' : 'border-slate-100' }} shadow-md p-6 space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $tone[$report->status] }}">{{ __('ui.issue_status_' . $report->status) }}</span>
                                <span class="font-mono text-xs text-slate-500">{{ $report->code }}</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ __('ui.issue_cat_' . $report->category) }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $report->user->email }} &middot; {{ $report->partner->company_name }} &middot;
                                <a href="{{ route('admin.bookings.index', ['q' => $report->booking->booking_code]) }}" class="font-mono text-sky-600 hover:underline">{{ $report->booking->booking_code }}</a>
                                &middot; {{ $report->booking->hotel_name }}
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-400">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $report->description }}</p>

                    @if ($report->evidence_path)
                        <a href="{{ route('issues.evidence', $report) }}" target="_blank" rel="noopener" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-paperclip"></i> {{ __('ui.issue_evidence') }}</a>
                    @endif

                    @if ($report->partner_response)
                        <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-2.5 text-xs text-slate-700"><strong>{{ __('ui.issue_partner_response') }}:</strong> {{ $report->partner_response }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.issues.update', $report) }}" class="grid md:grid-cols-[1fr_auto] gap-3 pt-2">
                        @csrf
                        <textarea name="resolution" rows="2" maxlength="2000" placeholder="{{ __('ui.issue_resolution_placeholder') }}" class="{{ $input }} w-full">{{ old('resolution', $report->resolution) }}</textarea>
                        <div class="flex md:flex-col gap-2">
                            <select name="status" class="{{ $input }}">
                                @foreach (['in_review', 'resolved', 'rejected'] as $option)
                                    <option value="{{ $option }}" @selected($report->status === $option)>{{ __('ui.issue_status_' . $option) }}</option>
                                @endforeach
                            </select>
                            <button class="px-3 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold transition">{{ __('ui.save_changes') }}</button>
                        </div>
                    </form>

                    @if ($report->handler)<div class="text-[11px] text-slate-400">{{ __('ui.topup_decided', ['name' => $report->handler->email, 'date' => $report->updated_at->format('d/m/Y H:i')]) }}</div>@endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400"><i class="fa-solid fa-life-ring text-3xl mb-2 block"></i> {{ __('ui.no_results') }}</div>
            @endforelse
        </div>

        {{ $reports->links() }}
    </section>
@endsection