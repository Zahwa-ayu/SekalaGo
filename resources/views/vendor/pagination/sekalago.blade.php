@if ($paginator->hasPages())
    @php
        $page = 'inline-flex items-center justify-center min-w-9 h-9 px-3 rounded-xl text-sm font-bold transition';
        $idle = 'bg-white border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-600 hover:border-sky-200';
    @endphp

    <nav role="navigation" aria-label="{{ __('ui.pagination_label') }}"
         class="flex flex-col sm:flex-row items-center justify-between gap-3">

        <p class="text-xs text-slate-500">
            {{ __('ui.pagination_showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        </p>

        {{-- HP: sebelumnya, berikutnya, dan posisi halaman --}}
        <div class="flex sm:hidden items-center gap-3 w-full justify-between">
            @if ($paginator->onFirstPage())
                <span class="{{ $page }} bg-slate-100 text-slate-300 cursor-not-allowed">{{ __('ui.pagination_previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $page }} {{ $idle }}">{{ __('ui.pagination_previous') }}</a>
            @endif

            <span class="text-xs font-semibold text-slate-500">{{ __('ui.pagination_page_of', ['page' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $page }} {{ $idle }}">{{ __('ui.pagination_next') }}</a>
            @else
                <span class="{{ $page }} bg-slate-100 text-slate-300 cursor-not-allowed">{{ __('ui.pagination_next') }}</span>
            @endif
        </div>

        {{-- Desktop: nomor halaman --}}
        <ul class="hidden sm:flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <li><span class="{{ $page }} bg-slate-100 text-slate-300 cursor-not-allowed" aria-disabled="true"><i class="fa-solid fa-chevron-left text-xs"></i></span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('ui.pagination_previous') }}" class="{{ $page }} {{ $idle }}"><i class="fa-solid fa-chevron-left text-xs"></i></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="inline-flex items-center justify-center min-w-9 h-9 text-slate-400">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $number => $url)
                        <li>
                            @if ($number == $paginator->currentPage())
                                <span aria-current="page" class="{{ $page }} bg-sagara-navy text-white shadow-md">{{ $number }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('ui.pagination_go_to', ['page' => $number]) }}" class="{{ $page }} {{ $idle }}">{{ $number }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('ui.pagination_next') }}" class="{{ $page }} {{ $idle }}"><i class="fa-solid fa-chevron-right text-xs"></i></a></li>
            @else
                <li><span class="{{ $page }} bg-slate-100 text-slate-300 cursor-not-allowed" aria-disabled="true"><i class="fa-solid fa-chevron-right text-xs"></i></span></li>
            @endif
        </ul>
    </nav>
@endif