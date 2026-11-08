@php
    $results = $search['results'];
    $nights  = $search['nights'];
    $params  = $search['params'];
@endphp

<div class="space-y-5">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-md px-5 py-4 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ __('ui.search_results') }}</h2>
        <p class="text-xs sm:text-sm text-slate-500 flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-sagara-blue"></i>
            {{ __('ui.stay_summary', [
                'in' => \Illuminate\Support\Carbon::parse($params['check_in'])->format('d/m/Y'),
                'out' => \Illuminate\Support\Carbon::parse($params['check_out'])->format('d/m/Y'),
                'count' => $nights,
                'guests' => $params['guests'],
            ]) }}
        </p>
    </div>

    @forelse ($results as $row)
        @php $hotel = $row['hotel']; @endphp

        <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-md hover:shadow-xl transition duration-300 flex flex-col sm:flex-row">
            <div class="sm:w-56 h-40 sm:h-auto shrink-0 bg-slate-200">
                @if ($hotel->coverUrl())
                    <img src="{{ $hotel->coverUrl() }}" alt="{{ $hotel->name }}" class="w-full h-full object-cover" loading="lazy">
                @else
                    <div class="w-full h-full min-h-40 bg-gradient-to-br from-sagara-navy to-sagara-teal flex items-center justify-center text-white/70 text-5xl">
                        <i class="fa-solid fa-hotel"></i>
                    </div>
                @endif
            </div>

            <div class="p-6 flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="min-w-0">
                    <span class="text-[11px] font-extrabold text-sagara-blue uppercase tracking-wider">
                        <i class="fa-solid fa-location-dot"></i>
                        {{ collect([$hotel->area, $hotel->city])->filter()->join(', ') }}
                    </span>
                    <h3 class="text-xl font-extrabold text-slate-900 mt-0.5">
                        <a href="{{ route('hotels.show', ['hotel' => $hotel] + $params) }}" class="hover:text-sagara-blue transition">{{ $hotel->name }}</a>
                        @if ($hotel->star_rating)
                            <span class="text-sm font-bold text-amber-500 whitespace-nowrap"><i class="fa-solid fa-star"></i> {{ $hotel->star_rating }}</span>
                        @endif
                    </h3>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($hotel->facilities as $f)
                            <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-sky-100 text-sagara-blue">{{ $f->label }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="sm:text-right shrink-0">
                    <span class="text-[11px] text-slate-400 block">{{ __('ui.from_price') }}</span>
                    <span class="text-xl font-black text-slate-900">{{ format_price($row['min_price']) }}</span>
                    <span class="text-xs text-slate-500">{{ __('ui.per_night') }}</span>
                    <div class="text-xs text-slate-500 mt-1">
                        {{ __('ui.total_price') }}
                        <strong class="text-sagara-blue">{{ format_price($row['min_price'] * $nights) }}</strong>
                        ({{ __('ui.for_nights', ['count' => $nights]) }})
                    </div>
                    <a href="{{ route('hotels.show', ['hotel' => $hotel] + $params) }}"
                       class="mt-3 inline-flex items-center gap-1 px-4 py-2 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-xl transition">
                        {{ __('ui.view') }} <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
            <i class="fa-solid fa-magnifying-glass text-3xl mb-2 block"></i>
            {{ __('ui.no_results') }}
        </div>
    @endforelse
</div>