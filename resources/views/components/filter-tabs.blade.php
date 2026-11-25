@props(['param', 'options', 'current' => null, 'counts' => []])

@php $keep = request()->except([$param, 'page']); @endphp

<div class="flex flex-wrap gap-2">
    @foreach ($options as $value => $label)
        @php
            $query = http_build_query(array_merge($keep, (string) $value === '' ? [] : [$param => $value]));
            $on    = (string) $current === (string) $value;
        @endphp
        <a href="{{ url()->current() . ($query ? '?' . $query : '') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $on ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            {{ $label }}
            @if (array_key_exists($value, $counts))<span class="opacity-70">({{ $counts[$value] }})</span>@endif
        </a>
    @endforeach
</div>