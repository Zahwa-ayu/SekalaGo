@php
    $decision = $agreement->proposalDecision();
    $fmt = fn ($rate) => rtrim(rtrim(number_format($rate * 100, 2, '.', ''), '0'), '.');
    $adjusted = $decision === 'adjusted';
@endphp

@if ($decision || $agreement->admin_note)
    <div class="flex items-start gap-3 rounded-3xl border px-5 py-4 text-sm {{ $adjusted ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }}">
        <i class="fa-solid {{ $adjusted ? 'fa-circle-info text-amber-600' : 'fa-circle-check text-emerald-600' }} mt-0.5 shrink-0"></i>

        <div class="min-w-0 space-y-1">
            @if ($decision)
                <div class="font-extrabold text-slate-900">
                    {{ $decision === 'as_proposed'
                        ? __('ui.proposal_accepted', ['percent' => $fmt($agreement->commission_rate)])
                        : __('ui.proposal_adjusted', ['proposed' => $fmt($agreement->proposed_rate), 'final' => $fmt($agreement->commission_rate)]) }}
                </div>
            @endif
            @if ($agreement->admin_note)
                <div class="text-slate-700 break-words"><strong>{{ __('ui.admin_note_for_partner') }}:</strong> {{ $agreement->admin_note }}</div>
            @endif
        </div>
    </div>
@endif