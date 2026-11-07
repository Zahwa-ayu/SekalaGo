@php
    $options = ['' => __('ui.all'), 'hotel' => __('ui.tab_hotels'), 'car' => __('ui.tab_cars')];
    $current = (string) request('product', '');
@endphp

<div class="flex flex-wrap items-center gap-2">
    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.product') }}</span>

    @foreach ($options as $value => $label)
        <a href="{{ route($route, array_filter(array_merge(request()->except('product', 'page'), ['product' => $value]), fn ($v) => $v !== '' && $v !== null)) }}"
           class="px-3 py-1.5 rounded-xl text-xs transition
                  {{ $current === (string) $value ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
            {{ $label }}
        </a>
    @endforeach
</div>