@if ($paginator->hasPages())
    @php
        $page = 'inline-flex items-center justify-center h-9 px-4 rounded-xl text-sm font-bold transition';
        $idle = 'bg-white border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-600 hover:border-sky-200';
    @endphp

    <nav role="navigation" aria-label="{{ __('ui.pagination_label') }}" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="{{ $page }} bg-slate-100 text-slate-300 cursor-not-allowed">{{ __('ui.pagination_previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $page }} {{ $idle }}">{{ __('ui.pagination_previous') }}</a>
        @endif

        <span class="text-xs font-semibold text-slate-500">{{ __('ui.pagination_page', ['page' => $paginator->currentPage()]) }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $page }} {{ $idle }}">{{ __('ui.pagination_next') }}</a>
        @else
            <span class="{{ $page }} bg-slate-100 text-slate-300 cursor-not-allowed">{{ __('ui.pagination_next') }}</span>
        @endif
    </nav>
@endif