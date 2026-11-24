<section id="reviews" class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <h2 class="text-xl font-extrabold text-slate-900">{{ __('ui.reviews') }}</h2>
        @if ($count > 0)
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-sm font-extrabold">
                <i class="fa-solid fa-star text-amber-400"></i> {{ number_format((float) $avg, 1, ',', '.') }}
                <span class="font-semibold text-amber-600/70">&middot; {{ __('ui.review_count', ['count' => $count]) }}</span>
            </span>
        @endif
    </div>

    @forelse ($reviews as $review)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="font-bold text-slate-900 text-sm">{{ $review->reviewer_name }}</div>
                <div class="text-[11px] text-slate-400">{{ $review->created_at->format('d/m/Y') }}</div>
            </div>
            <div class="text-amber-400 text-sm">
                @foreach (range(1, 5) as $i)<i class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-slate-200' }}"></i>@endforeach
            </div>
            @if ($review->comment)<p class="text-sm text-slate-600 whitespace-pre-line">{{ $review->comment }}</p>@endif

            @if ($review->partner_reply)
                <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('ui.review_partner_reply') }}</div>
                    <p class="text-slate-600 whitespace-pre-line">{{ $review->partner_reply }}</p>
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-400">{{ __('ui.reviews_empty') }}</p>
    @endforelse

    {{ $reviews->links() }}
</section>