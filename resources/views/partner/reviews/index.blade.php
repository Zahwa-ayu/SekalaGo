@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.reviews') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('ui.partner_reviews_hint') }}</p>
            </div>
            @if ($average > 0)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md px-5 py-3 text-xl font-black text-slate-900">
                    <i class="fa-solid fa-star text-amber-400"></i> {{ number_format($average, 1, ',', '.') }}
                </div>
            @endif
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="space-y-3">
            <x-filter-tabs param="view" :current="$view" :counts="$counts" :options="[
                '' => __('ui.all'),
                'unreplied' => __('ui.reviews_tab_unreplied'),
                'replied' => __('ui.reviews_tab_replied'),
                'hidden' => __('ui.reviews_tab_hidden'),
            ]" />

            <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                <x-filter-tabs param="rating" :current="$rating ?: ''" :options="[
                    '' => __('ui.all_ratings'), '5' => '5 ★', '4' => '4 ★', '3' => '3 ★', '2' => '2 ★', '1' => '1 ★',
                ]" />
                <x-filter-tabs param="type" :current="$type" :options="[
                    '' => __('ui.all'), 'hotel' => __('ui.tab_hotels'), 'car' => __('ui.tab_cars'),
                ]" />
            </div>

            <x-search-bar :placeholder="__('ui.review_search_partner')" />
        </div>

        @forelse ($reviews as $review)
            <div class="bg-white rounded-2xl border {{ $review->is_visible ? 'border-slate-100' : 'border-red-200' }} shadow-sm p-5 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="font-bold text-slate-900 text-sm">{{ $review->reviewer_name }}</span>
                        <span class="text-xs text-slate-400">&middot; {{ $review->booking->hotel_name }} &middot; {{ $review->booking->booking_code }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400">{{ $review->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="text-amber-400 text-sm">
                    @foreach (range(1, 5) as $i)<i class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-slate-200' }}"></i>@endforeach
                    @unless ($review->is_visible)<span class="ml-2 text-[11px] font-bold text-red-600">{{ __('ui.review_hidden_by_admin') }}</span>@endunless
                </div>
                @if ($review->comment)<p class="text-sm text-slate-600 whitespace-pre-line">{{ $review->comment }}</p>@endif

                <form method="POST" action="{{ route('partner.reviews.reply', $review) }}" class="space-y-2">
                    @csrf
                    <textarea name="partner_reply" rows="2" maxlength="1000" required placeholder="{{ __('ui.review_reply_placeholder') }}"
                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">{{ old('partner_reply', $review->partner_reply) }}</textarea>
                    <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-700 text-xs font-bold transition">
                        {{ $review->partner_reply ? __('ui.review_reply_update') : __('ui.review_reply') }}
                    </button>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center text-slate-400 text-sm">{{ __('ui.reviews_empty') }}</div>
        @endforelse

        {{ $reviews->links() }}
    </div>
@endsection