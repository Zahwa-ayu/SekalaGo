@props(['placeholder' => null, 'except' => []])

@php
    $term  = trim((string) request('q', ''));
    $keep  = collect(request()->query())
        ->except(array_merge(['q', 'page'], $except))
        ->filter(fn ($value) => ! is_array($value) && filled($value));
    $dirty = $term !== '' || collect($except)->contains(fn ($name) => filled(request($name)));
@endphp

<form method="GET" action="{{ url()->current() }}" role="search" class="flex flex-wrap items-center gap-2">
    @foreach ($keep as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    <div class="relative flex-1 min-w-48">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input name="q" value="{{ $term }}" placeholder="{{ $placeholder ?? __('ui.search') }}"
               class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
    </div>

    {{ $slot }}

    <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">{{ __('ui.search') }}</button>

    @if ($dirty)
        <a href="{{ url()->current() . ($keep->isNotEmpty() ? '?' . http_build_query($keep->all()) : '') }}"
           class="text-sm font-semibold text-slate-500 hover:underline">{{ __('ui.reset') }}</a>
    @endif
</form>