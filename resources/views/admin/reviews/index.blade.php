@extends('layouts.app')

@section('content')
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.reviews') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.admin_reviews_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            @foreach ([null => 'all', 'low' => 'reviews_tab_low', 'hidden' => 'reviews_tab_hidden'] as $value => $label)
                <a href="{{ route('admin.reviews.index', $value ? ['view' => $value] : []) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ (string) $view === (string) $value ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ __('ui.' . $label) }}</a>
            @endforeach
            <x-search-bar :placeholder="__('ui.reviews_search')" :except="[]" />
        </div>

        <div class="space-y-3">
            @forelse ($reviews as $review)
                <div class="bg-white rounded-2xl border {{ $review->is_visible ? 'border-slate-100' : 'border-red-200' }} shadow-sm p-5 space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <div>
                            <span class="font-bold text-slate-900">{{ $review->reviewer_name }}</span>
                            <span class="text-xs text-slate-400">&middot; {{ $review->booking->hotel_name }} &middot; {{ $review->partner->company_name }} &middot; {{ $review->booking->booking_code }}</span>
                        </div>
                        <span class="text-amber-400">@foreach (range(1, 5) as $i)<i class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-slate-200' }}"></i>@endforeach</span>
                    </div>
                    @if ($review->comment)<p class="text-sm text-slate-600 whitespace-pre-line">{{ $review->comment }}</p>@endif
                    @if ($review->partner_reply)<p class="text-xs text-slate-500 border-l-2 border-slate-200 pl-3">{{ $review->partner_reply }}</p>@endif

                    @if ($review->is_visible)
                        <form method="POST" action="{{ route('admin.reviews.hide', $review) }}" class="flex flex-wrap gap-2 pt-1">
                            @csrf
                            <input name="reason" required maxlength="300" placeholder="{{ __('ui.review_hide_reason') }}" class="flex-1 min-w-48 rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <button class="px-3 py-2 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition">{{ __('ui.review_hide') }}</button>
                        </form>
                    @else
                        <div class="flex flex-wrap items-center gap-3 text-xs text-red-700">
                            <span><strong>{{ __('ui.review_hidden_by_admin') }}:</strong> {{ $review->hidden_reason }}</span>
                            <form method="POST" action="{{ route('admin.reviews.unhide', $review) }}">
                                @csrf
                                <button class="font-bold text-sky-600 hover:underline">{{ __('ui.review_show_again') }}</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center text-slate-400 text-sm">{{ __('ui.no_results') }}</div>
            @endforelse
        </div>

        {{ $reviews->links() }}
    </section>
@endsection