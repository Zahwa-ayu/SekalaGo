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
        E-TICKET / VOUCHER RENTAL MOBIL
    </div>

    <div class="code">{{ $booking->booking_code }}</div>
    <p>Status: <span class="ok">TERKONFIRMASI / CONFIRMED</span></p>

    <h2>Kendaraan / Vehicle</h2>
    <table class="info">
        <tr><td class="label">Mobil / Car</td><td>{{ $booking->hotel_name }}</td></tr>
        <tr><td class="label">Opsi / Option</td><td>{{ $booking->with_driver ? 'Dengan supir / With driver' : 'Lepas kunci / Self-drive' }}</td></tr>
        <tr><td class="label">Lokasi ambil &amp; kembali / Pick-up &amp; return</td><td>{{ $booking->pickup_location ?: '-' }}</td></tr>
    </table>

    <h2>Sewa / Rental</h2>
    <table class="info">
        <tr><td class="label">Tanggal ambil / Pick-up date</td><td>{{ $booking->check_in->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Tanggal kembali / Return date</td><td>{{ $booking->check_out->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Hari / Days</td><td>{{ $booking->nights }}</td></tr>
        <tr><td class="label">Penumpang / Passengers</td><td>{{ $booking->guests }}</td></tr>
        <tr><td class="label">Nama penyewa / Renter</td><td>{{ $booking->guest_name }}</td></tr>
    </table>

    <h2>Pembayaran / Payment</h2>
    <table class="info">
        <tr><td class="label">Total sewa dibayar / Rental paid</td><td>{{ format_price($booking->total_price, 'IDR') }}</td></tr>
        <tr><td class="label">Setara / Equivalent</td><td>{{ $booking->displayTotal() }}</td></tr>
        <tr><td class="label">Referensi / Reference</td><td>{{ $booking->payment_reference }}</td></tr>
        <tr><td class="label">Dibayar pada / Paid at</td><td>{{ $booking->paid_at?->format('d/m/Y H:i') }}</td></tr>
    </table>

    <h2>Biaya tambahan / Extra costs</h2>
    <table class="info">
        <tr>
            <td class="label">Dibayar langsung ke vendor / Paid directly to vendor</td>
            <td>
                @if ($booking->with_driver && $booking->driver_fee_per_day !== null)
                    Biaya supir sekitar {{ format_price($booking->driver_fee_per_day, 'IDR') }} per hari / Driver fee about {{ format_price($booking->driver_fee_per_day, 'IDR') }} per day.<br>
                @endif
                BBM, tol, dan parkir tidak termasuk total sewa. / Fuel, tolls and parking are not included in the rental total.
            </td>
        </tr>
    </table>

    <h2>Pembatalan / Cancellation</h2>
    <table class="info">
        <tr>
            <td class="label">Gratis sampai / Free until</td>
            <td>{{ $booking->freeCancelDeadline()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p class="note">
        Tunjukkan e-ticket ini dan identitas diri saat pengambilan. Untuk lepas kunci, bawa juga SIM yang masih berlaku.<br>
        Please show this e-ticket and your ID at pick-up. For self-drive, also bring a valid driving licence.<br>
        Dokumen simulasi untuk pengembangan aplikasi. / Simulated document for application development.
    </p>
</body>
</html>