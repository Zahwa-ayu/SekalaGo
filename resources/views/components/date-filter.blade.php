@props(['by' => []])

@php
    $keep   = collect(request()->query())->except(['from', 'to', 'by', 'page'])->filter(fn ($v) => ! is_array($v) && filled($v));
    $byNow  = request('by');
    $link   = fn (array $range) => url()->current() . '?' . http_build_query($keep->all() + $range + ($byNow ? ['by' => $byNow] : []));
    $today  = today();
    $active = filled(request('from')) || filled(request('to'));
    $input  = 'rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';

    $presets = [
        'range_today'      => [$today, $today],
        'range_last_7'     => [$today->copy()->subDays(6), $today],
        'range_last_30'    => [$today->copy()->subDays(29), $today],
        'range_this_month' => [$today->copy()->startOfMonth(), $today],
        'range_last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
    ];
@endphp

<div class="space-y-2">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">
        @foreach ($keep as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @if ($by)
            <select name="by" class="{{ $input }}" aria-label="{{ __('ui.date_by') }}">
                @foreach ($by as $value => $label)
                    <option value="{{ $value }}" @selected(($byNow ?: array_key_first($by)) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        @endif

        <i class="fa-regular fa-calendar text-slate-400"></i>
        <input type="date" name="from" value="{{ request('from') }}" class="{{ $input }}" aria-label="{{ __('ui.analytics_from') }}">
        <span class="text-slate-400 text-xs">-</span>
        <input type="date" name="to" value="{{ request('to') }}" class="{{ $input }}" aria-label="{{ __('ui.analytics_to') }}">

        <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">{{ __('ui.date_apply') }}</button>

        @if ($active)
            <a href="{{ url()->current() . ($keep->isNotEmpty() || $byNow ? '?' . http_build_query($keep->all() + ($byNow ? ['by' => $byNow] : [])) : '') }}"
               class="text-sm font-semibold text-slate-500 hover:underline">{{ __('ui.reset') }}</a>
        @endif
    </form>

    <div class="flex flex-wrap gap-1.5">
        @foreach ($presets as $label => [$start, $end])
            @php $on = request('from') === $start->toDateString() && request('to') === $end->toDateString(); @endphp
            <a href="{{ $link(['from' => $start->toDateString(), 'to' => $end->toDateString()]) }}"
               class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition {{ $on ? 'bg-sagara-navy text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ __('ui.' . $label) }}</a>
        @endforeach
    </div>
</div>