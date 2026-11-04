@php
    $old = $log->properties['old'] ?? [];
    $new = $log->properties['new'] ?? [];
    $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

    $fmt = fn ($v) => is_null($v) ? '-' : (is_bool($v) ? ($v ? 'ya' : 'tidak') : (is_array($v) ? json_encode($v) : $v));
@endphp

@if ($keys)
    <ul class="mt-1 space-y-0.5 text-[11px] text-slate-500">
        @foreach ($keys as $key)
            <li>
                <span class="font-semibold text-slate-600">
                    {{ \Illuminate\Support\Facades\Lang::has('ui.log_attr_' . $key) ? __('ui.log_attr_' . $key) : $key }}
                </span>:
                @if (array_key_exists($key, $old))
                    <span class="line-through text-red-400">{{ $fmt($old[$key]) }}</span> &rarr;
                @endif
                <span class="text-emerald-600 font-semibold">{{ $fmt($new[$key] ?? null) }}</span>
            </li>
        @endforeach
    </ul>
@endif