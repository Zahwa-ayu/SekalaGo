<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>E-Ticket {{ $booking->booking_code }}</title>
    <style>
        @page { margin: 40px 45px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111; line-height: 1.5; }
        .head { border-bottom: 2px solid #0a64a4; padding-bottom: 8px; margin-bottom: 14px; }
        .brand { font-size: 20px; font-weight: bold; color: #0a64a4; }
        .code { font-size: 22px; font-weight: bold; letter-spacing: 2px; border: 2px dashed #0a64a4; padding: 10px; text-align: center; margin: 12px 0; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.info td { border: 1px solid #ccc; padding: 6px 8px; vertical-align: top; }
        table.info td.label { width: 34%; background: #f2f6fa; font-weight: bold; }
        h2 { font-size: 12px; margin: 14px 0 4px; color: #0a64a4; }
        .ok { color: #0a7a2f; font-weight: bold; }
        .note { font-size: 9px; color: #555; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="head">
        <span class="brand">{{ $platform }}</span><br>
        E-TICKET / VOUCHER HOTEL
    </div>

    <div class="code">{{ $booking->booking_code }}</div>
    <p>Status: <span class="ok">TERKONFIRMASI / CONFIRMED</span></p>

    <h2>Hotel / Hotel</h2>
    <table class="info">
        <tr><td class="label">Nama hotel / Hotel name</td><td>{{ $booking->hotel_name }}</td></tr>
        <tr><td class="label">Tipe kamar / Room type</td><td>{{ $booking->room_name }}</td></tr>
        <tr>
            <td class="label">Lokasi / Location</td>
            <td>
                {{ $booking->hotel?->city ?? '-' }}
                @if ($booking->hotel?->address), {{ $booking->hotel->address }}@endif
            </td>
        </tr>
    </table>

    <h2>Menginap / Stay</h2>
    <table class="info">
        <tr><td class="label">Check-in</td><td>{{ $booking->check_in->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Check-out</td><td>{{ $booking->check_out->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Malam / Nights</td><td>{{ $booking->nights }}</td></tr>
        <tr><td class="label">Tamu / Guests</td><td>{{ $booking->guests }}</td></tr>
        <tr><td class="label">Nama tamu / Guest name</td><td>{{ $booking->guest_name }}</td></tr>
    </table>

    <h2>Pembayaran / Payment</h2>
    <table class="info">
        <tr><td class="label">Total dibayar / Total paid</td><td>{{ format_price($booking->total_price, 'IDR') }}</td></tr>
        <tr><td class="label">Setara / Equivalent</td><td>{{ $booking->displayTotal() }}</td></tr>
        <tr><td class="label">Referensi / Reference</td><td>{{ $booking->payment_reference }}</td></tr>
        <tr><td class="label">Dibayar pada / Paid at</td><td>{{ $booking->paid_at?->format('d/m/Y H:i') }}</td></tr>
    </table>

    <h2>Pembatalan / Cancellation</h2>
    <table class="info">
        <tr>
            <td class="label">Gratis sampai / Free until</td>
            <td>{{ $booking->freeCancelDeadline()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p class="note">
        Tunjukkan e-ticket ini kepada resepsionis saat check-in. / Please show this e-ticket at the front desk on check-in.<br>
        Dokumen simulasi untuk pengembangan aplikasi. / Simulated document for application development.
    </p>
</body>
</html>