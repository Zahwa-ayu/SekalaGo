@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.confirm_booking') }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color:red">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>{{ $roomType->hotel->name }}</h2>
    <p>{{ $roomType->name }} ({{ __('ui.capacity') }}: {{ $roomType->capacity }})</p>
    <p>
        {{ __('ui.stay_summary', [
            'in' => $checkIn->format('d/m/Y'),
            'out' => $checkOut->format('d/m/Y'),
            'count' => $nights,
            'guests' => $guests,
        ]) }}
    </p>

    <table border="1" cellpadding="6">
        <tr>
            <td>{{ __('ui.price_per_night') }}</td>
            <td>{{ format_price($roomType->base_price) }}</td>
        </tr>
        <tr>
            <td>{{ __('ui.nights') }}</td>
            <td>{{ $nights }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('ui.total_price') }}</strong></td>
            <td><strong>{{ format_price($total) }}</strong></td>
        </tr>
    </table>

    {{-- BARU: keterangan pembayaran dan limit untuk karyawan corporate --}}
    @if ($isCorporate)
        <p><small>{{ __('ui.paid_by_deposit') }}</small></p>
        <p>
            {{ __('ui.your_limit') }}: <strong>{{ format_price($limit, 'IDR') }}</strong><br>
            @if ($needsApproval)
                <span style="color:#b45309">{{ __('ui.over_limit_notice', ['limit' => format_price($limit, 'IDR')]) }}</span>
            @elseif ($isFinance && $total > $limit)
                {{ __('ui.finance_exempt_notice') }}
            @else
                {{ __('ui.within_limit_notice') }}
            @endif
        </p>
    @else
        <p><small>{{ __('ui.charged_in_idr', ['amount' => format_price($total, 'IDR')]) }}</small></p>
    @endif

    @if ($isOpen)
        <form method="POST" action="{{ route('bookings.store') }}">
            @csrf
            <input type="hidden" name="room_type" value="{{ $roomType->id }}">
            <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
            <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
            <input type="hidden" name="guests" value="{{ $guests }}">

            <p>
                <label>{{ __('ui.guest_name') }}<br>
                    {{-- BARU: nama bawaan juga dari data karyawan --}}
                    <input name="guest_name"
                           value="{{ old('guest_name', auth()->user()->customer?->full_name ?? auth()->user()->companyUser?->displayName()) }}" required>
                </label>
            </p>

            {{-- BARU: tombol menyesuaikan alur --}}
            <button>
                @if ($isCorporate)
                    {{ $needsApproval ? __('ui.submit_for_approval') : __('ui.submit_booking') }}
                @else
                    {{ __('ui.continue_to_payment') }}
                @endif
            </button>
        </form>
    @else
        <p style="color:red">{{ __('ui.room_unavailable') }}</p>
    @endif
@endsection