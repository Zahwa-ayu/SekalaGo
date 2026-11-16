@php
    $showCorporate = $showCorporate ?? false;
    $digits   = preg_replace('/\D/', '', (string) $booking->contact_phone);
    $whatsapp = str_starts_with($digits, '0') ? '62' . substr($digits, 1) : $digits;
@endphp

<div class="text-[11px] text-slate-500 space-y-0.5">
    @if ($booking->contact_name && $booking->contact_name !== $booking->guest_name)
        <div><i class="fa-solid fa-user w-4 text-sky-500"></i> {{ __('ui.booker_data') }}: {{ $booking->contact_name }}</div>
    @endif

    @if ($booking->contact_phone)
        <div>
            <i class="fa-solid fa-phone w-4 text-sky-500"></i>
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $booking->contact_phone) }}" class="hover:underline">{{ $booking->contact_phone }}</a>
            @if ($whatsapp !== '')
                &middot; <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="text-emerald-600 font-bold hover:underline">WhatsApp</a>
            @endif
        </div>
    @endif

    @if ($booking->contact_email)
        <div><i class="fa-solid fa-envelope w-4 text-sky-500"></i> <a href="mailto:{{ $booking->contact_email }}" class="hover:underline break-all">{{ $booking->contact_email }}</a></div>
    @endif

    @if ($booking->arrival_time)
        <div><i class="fa-solid fa-clock w-4 text-sky-500"></i> {{ $booking->isCar() ? __('ui.pickup_time') : __('ui.arrival_time') }}: {{ substr($booking->arrival_time, 0, 5) }}</div>
    @endif

    @if ($booking->destination)
        <div><i class="fa-solid fa-route w-4 text-sky-500"></i> {{ $booking->destination }}</div>
    @endif

    @if ($booking->special_requests)
        <div class="text-slate-700"><i class="fa-solid fa-comment-dots w-4 text-sky-500"></i> {{ $booking->special_requests }}</div>
    @endif

    @if ($showCorporate && ($booking->trip_purpose || $booking->cost_center))
        <div>
            <i class="fa-solid fa-briefcase w-4 text-violet-500"></i>
            {{ $booking->trip_purpose }}@if ($booking->cost_center) &middot; {{ __('ui.cost_center') }}: {{ $booking->cost_center }}@endif
        </div>
    @endif
</div>