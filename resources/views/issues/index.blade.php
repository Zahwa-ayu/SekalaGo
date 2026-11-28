@extends('layouts.app')

@section('content')
    @php
        $tone = [
            'open'      => 'bg-amber-100 text-amber-700',
            'in_review' => 'bg-sky-100 text-sky-700',
            'resolved'  => 'bg-emerald-100 text-emerald-700',
            'rejected'  => 'bg-slate-100 text-slate-500',
        ];
    @endphp

    {{-- BANNER BIRU --}}
    <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white hover:underline">
                <i class="fa-solid fa-arrow-left"></i> {{ __('ui.back') }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white drop-shadow-sm">{{ __('ui.issue_my_reports') }}</h1>
            <p class="text-sm text-sky-100">{{ __('ui.issue_my_reports_hint') }}</p>
        </div>
    </div>

    <section class="relative z-10 w-full px-4 sm:px-6 lg:px-8 -mt-12 pb-12 space-y-5">

        @forelse ($reports as $report)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xl p-6 sm:p-8 space-y-4">

                {{-- STATUS, KODE LAPORAN, TANGGAL --}}
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $tone[$report->status] }}">{{ __('ui.issue_status_' . $report->status) }}</span>
                        <span class="font-mono text-xs text-slate-500">{{ $report->code }}</span>
                    </div>
                    <div class="text-xs text-slate-400">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                </div>

                {{-- KATEGORI, KODE PESANAN, DESKRIPSI --}}
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">{{ __('ui.issue_cat_' . $report->category) }}</h2>
                    <a href="{{ route('bookings.show', $report->booking) }}" class="inline-block font-mono text-xs font-bold text-sky-600 hover:underline">
                        {{ $report->booking->booking_code }}
                    </a>
                    <p class="text-sm text-slate-600 whitespace-pre-line break-words mt-2">{{ $report->description }}</p>
                </div>

                {{-- BUKTI --}}
                @if ($report->evidence_path)
                    <a href="{{ route('issues.evidence', $report) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 text-sm font-bold text-sky-600 hover:underline">
                        <i class="fa-solid fa-paperclip"></i> {{ __('ui.issue_evidence') }}
                    </a>
                @endif

                {{-- TANGGAPAN MITRA --}}
                @if ($report->partner_response)
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('ui.issue_partner_response') }}</div>
                        <p class="text-slate-600 whitespace-pre-line break-words">{{ $report->partner_response }}</p>
                    </div>
                @endif

                {{-- PENYELESAIAN --}}
                @if ($report->resolution)
                    <div class="rounded-2xl bg-sky-50 px-4 py-3 text-sm">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">{{ __('ui.issue_resolution') }}</div>
                        <p class="text-slate-700 whitespace-pre-line break-words">{{ $report->resolution }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xl px-6 py-12 text-center text-slate-400 text-sm">
                <i class="fa-solid fa-life-ring text-3xl text-sky-300 block mb-3"></i>
                {{ __('ui.issue_empty') }}
            </div>
        @endforelse

        {{ $reports->links() }}
    </section>
@endsection