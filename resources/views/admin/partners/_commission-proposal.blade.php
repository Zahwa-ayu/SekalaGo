@php $percent = $partner->proposedPercent(); @endphp

<div class="mt-6 rounded-2xl border {{ $percent !== null ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-slate-50' }} px-5 py-4 text-sm">
    @if ($percent !== null)
        <div class="font-extrabold text-slate-900">
            <i class="fa-solid fa-hand-holding-dollar text-sky-500"></i>
            {{ __('ui.partner_proposes', ['percent' => rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.')]) }}
        </div>
        @if ($partner->commission_proposal_note)
            <p class="text-slate-600 mt-1">"{{ $partner->commission_proposal_note }}"</p>
        @endif
        <p class="text-[11px] text-slate-500 mt-1">
            {{ __('ui.proposal_on', ['date' => $partner->commission_proposed_at?->format('d/m/Y H:i') ?? '-']) }}
            &middot; {{ __('ui.proposal_admin_hint') }}
        </p>
    @else
        <span class="text-slate-500">{{ __('ui.partner_no_proposal') }}</span>
    @endif
</div>