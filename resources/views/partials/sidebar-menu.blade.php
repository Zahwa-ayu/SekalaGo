@php
    $menu     = \App\Support\DashboardMenu::for(auth()->user());
    $isActive = fn (array $patterns) => request()->routeIs(...$patterns);
@endphp

<nav class="space-y-1 px-3" aria-label="Menu">
    @foreach ($menu as $entry)
        @if (($entry['type'] ?? 'item') === 'group')
            @php $open = $isActive($entry['active']); @endphp

            <details class="group" {{ $open ? 'open' : '' }}>
                <summary class="list-none [&::-webkit-details-marker]:hidden flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer select-none text-sm font-semibold transition
                                {{ $open ? 'text-white bg-white/10' : 'text-white/90 hover:bg-white/10' }}">
                    <i class="fa-solid {{ $entry['icon'] }} w-5 text-center text-white/80"></i>
                    <span class="flex-1">{{ __($entry['label']) }}</span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-white/60 transition-transform duration-200 group-open:rotate-180"></i>
                </summary>

                <ul class="mt-1 mb-2 space-y-1">
                    @foreach ($entry['children'] as $child)
                        @php $on = $isActive($child['active']); @endphp
                        <li>
                            <a href="{{ route($child['route']) }}"
                               class="flex items-center gap-3 pl-6 pr-4 py-2.5 rounded-xl text-[13px] transition
                                      {{ $on ? 'bg-white text-sagara-blue font-bold shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10 font-medium' }}">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $on ? 'bg-sagara-blue' : 'bg-white/60' }}"></span>
                                {{ __($child['label']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </details>
        @else
            @php $on = $isActive($entry['active']); @endphp

            <a href="{{ route($entry['route']) }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition
                      {{ $on ? 'bg-white text-sagara-blue font-bold shadow-lg' : 'text-white/90 font-semibold hover:bg-white/10' }}">
                <i class="fa-solid {{ $entry['icon'] }} w-5 text-center {{ $on ? 'text-sagara-blue' : 'text-white/80' }}"></i>
                {{ __($entry['label']) }}
            </a>
        @endif
    @endforeach
</nav>