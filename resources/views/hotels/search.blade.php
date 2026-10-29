@extends('layouts.app')

@section('content')
    @include('partials.search-form')

    <h1>{{ __('ui.search_results') }}</h1>
    <p>
        {{ __('ui.stay_summary', [
            'in' => \Illuminate\Support\Carbon::parse($params['check_in'])->format('d/m/Y'),
            'out' => \Illuminate\Support\Carbon::parse($params['check_out'])->format('d/m/Y'),
            'count' => $nights,
            'guests' => $params['guests'],
        ]) }}
    </p>

    @forelse ($results as $row)
        @php $hotel = $row['hotel']; @endphp
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px">
            <h2>
                <a href="{{ route('hotels.show', ['hotel' => $hotel] + $params) }}">{{ $hotel->name }}</a>
                @if ($hotel->star_rating) ({{ $hotel->star_rating }}★) @endif
            </h2>
            <p>{{ $hotel->city }}</p>
            <p>{{ $hotel->facilities->map(fn ($f) => $f->label)->join(', ') }}</p>
            <p>
                {{ __('ui.from_price') }}
                <strong>{{ format_price($row['min_price']) }}</strong>{{ __('ui.per_night') }}<br>
                {{ __('ui.total_price') }}
                <strong>{{ format_price($row['min_price'] * $nights) }}</strong>
                ({{ __('ui.for_nights', ['count' => $nights]) }})
            </p>
        </div>
    @empty
        <p>{{ __('ui.no_results') }}</p>
    @endforelse
@endsection