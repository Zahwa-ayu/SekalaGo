@extends('layouts.app')

@section('content')
    <section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-14 space-y-6">
        <div>
            <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-chevron-left mr-1"></i> {{ $booking->booking_code }}</a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ __('ui.review_write') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $booking->hotel_name }} &middot; {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('reviews.store', $booking) }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-5">
            @csrf
            <input type="hidden" name="rating" id="rating" value="{{ old('rating') }}">

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('ui.review_rating') }}</div>
                <div class="flex gap-1 text-3xl" id="stars" role="radiogroup">
                    @foreach (range(1, 5) as $i)
                        <button type="button" data-value="{{ $i }}" class="star text-slate-300 hover:scale-110 transition" aria-label="{{ $i }}"><i class="fa-solid fa-star"></i></button>
                    @endforeach
                </div>
                <div id="rating-label" class="text-xs font-semibold text-slate-500 mt-1 h-4"></div>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('ui.review_comment') }}</label>
                <textarea name="comment" rows="5" maxlength="1000" placeholder="{{ __('ui.review_comment_hint') }}"
                          class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">{{ old('comment') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.review_public_note') }}</p>
            </div>

            <button id="submit" class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                {{ __('ui.review_submit') }}
            </button>
        </form>
    </section>

    @php
        $ratingNames = [
            1 => __('ui.rating_1'),
            2 => __('ui.rating_2'),
            3 => __('ui.rating_3'),
            4 => __('ui.rating_4'),
            5 => __('ui.rating_5'),
        ];
    @endphp

    <script>
        (function () {
            const input = document.getElementById('rating');
            const stars = [...document.querySelectorAll('.star')];
            const label = document.getElementById('rating-label');
            const submit = document.getElementById('submit');
            const names = @json($ratingNames);

            function paint(value) {
                stars.forEach((s) => s.classList.toggle('text-amber-400', parseInt(s.dataset.value, 10) <= value));
                stars.forEach((s) => s.classList.toggle('text-slate-300', parseInt(s.dataset.value, 10) > value));
                label.textContent = value ? names[value] : '';
                submit.disabled = !value;
            }

            stars.forEach((s) => {
                s.addEventListener('click', () => { input.value = s.dataset.value; paint(parseInt(s.dataset.value, 10)); });
                s.addEventListener('mouseenter', () => paint(parseInt(s.dataset.value, 10)));
            });
            document.getElementById('stars').addEventListener('mouseleave', () => paint(parseInt(input.value || 0, 10)));

            paint(parseInt(input.value || 0, 10));
        })();
    </script>
@endsection