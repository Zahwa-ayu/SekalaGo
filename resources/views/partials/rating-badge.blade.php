@if (($count ?? 0) > 0)
    <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-700">
        <i class="fa-solid fa-star text-amber-400"></i> {{ number_format((float) $avg, 1, ',', '.') }}
        <span class="font-medium text-slate-400">({{ $count }})</span>
    </span>
@endif