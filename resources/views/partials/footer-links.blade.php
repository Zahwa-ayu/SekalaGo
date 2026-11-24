@php
    $footerPages = \Illuminate\Support\Facades\Cache::rememberForever('footer_pages', fn () => \App\Models\StaticPage::query()
        ->where('is_published', true)->where('show_in_footer', true)->orderBy('sort_order')->get());
@endphp

<ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
    @foreach ($footerPages as $page)
        <li><a href="{{ route('pages.show', $page->slug) }}" class="hover:text-sky-300 transition">{{ $page->localized('title') }}</a></li>
    @endforeach
</ul>