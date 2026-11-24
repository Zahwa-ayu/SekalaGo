<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class Reminders
{
    public function __construct(private Notifier $notifier)
    {
    }

    /** @return array<string, int> jumlah pengingat yang dikirim per jenis */
    public function run(): array
    {
        return [
            'payment_expiring' => $this->paymentExpiring(),
            'arrival_tomorrow' => $this->arrivalTomorrow(),
            'return_soon'      => $this->returnSoon(),
            'return_overdue'   => $this->returnOverdue(),
            'review_prompt'    => $this->reviewPrompt(),
        ];
    }

    /** Tandai sebagai terkirim; false jika pengingat ini sudah pernah dikirim untuk pesanan itu. */
    private function claim(Booking $booking, string $kind): bool
    {
        return DB::table('booking_reminders')->insertOrIgnore(['booking_id' => $booking->id, 'kind' => $kind]) === 1;
    }

    private function paymentExpiring(): int
    {
        $sent = 0;

        $bookings = Booking::with('user')
            ->where('status', 'pending_payment')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addMinutes(10))
            ->get();

        foreach ($bookings as $booking) {
            if (! $this->claim($booking, 'payment_expiring')) {
                continue;
            }

            $this->notifier->to($booking->user, 'payment_expiring', [
                'code'    => $booking->booking_code,
                'name'    => $booking->hotel_name,
                'minutes' => max(1, (int) ceil(now()->diffInSeconds($booking->expires_at, false) / 60)),
            ], route('bookings.pay', $booking), 'fa-hourglass-half');

            $sent++;
        }

        return $sent;
    }

    /** Check-in atau pengambilan besok; dikirim mulai pukul 08.00. */
    private function arrivalTomorrow(): int
    {
        if (now()->hour < 8) {
            return 0;
        }

        $sent = 0;

        $bookings = Booking::with('user')
            ->where('status', 'confirmed')
            ->whereDate('check_in', today()->addDay())
            ->whereNull('no_show_at')
            ->whereNull('picked_up_at')
            ->get();

        foreach ($bookings as $booking) {
            if (! $this->claim($booking, 'arrival_tomorrow')) {
                continue;
            }

            $this->notifier->to(
                $booking->user,
                $booking->isCar() ? 'arrival_tomorrow_car' : 'arrival_tomorrow_hotel',
                [
                    'code'  => $booking->booking_code,
                    'name'  => $booking->hotel_name,
                    'time'  => $booking->arrival_time ? substr($booking->arrival_time, 0, 5) : '-',
                    'place' => $booking->pickup_location ?: '-',
                ],
                route('bookings.show', $booking),
                'fa-calendar-check'
            );

            $sent++;
        }

        return $sent;
    }

    /** Mobil yang sedang disewa dan batas kembalinya tinggal 3 jam. */
    private function returnSoon(): int
    {
        $sent = 0;

        $bookings = Booking::with('user')
            ->where('product_type', 'car')
            ->where('status', 'confirmed')
            ->whereNotNull('picked_up_at')
            ->whereNull('returned_at')
            ->whereBetween('due_at', [now(), now()->addHours(3)])
            ->get();

        foreach ($bookings as $booking) {
            if (! $this->claim($booking, 'return_soon')) {
                continue;
            }

            $this->notifier->to($booking->user, 'return_soon', [
                'code' => $booking->booking_code,
                'name' => $booking->hotel_name,
                'time' => $booking->due_at->format('d/m H:i'),
            ], route('bookings.show', $booking), 'fa-clock');

            $sent++;
        }

        return $sent;
    }

    /** Mobil belum kembali setelah masa toleransi: pelanggan dan mitra diberi tahu. */
    private function returnOverdue(): int
    {
        $sent = 0;

        $bookings = Booking::with('user', 'partner.user')
            ->where('product_type', 'car')
            ->where('status', 'confirmed')
            ->whereNotNull('picked_up_at')
            ->whereNull('returned_at')
            ->whereRaw('DATE_ADD(due_at, INTERVAL late_grace_minutes MINUTE) < ?', [now()])
            ->get();

        foreach ($bookings as $booking) {
            if (! $this->claim($booking, 'return_overdue')) {
                continue;
            }

            $params = [
                'code'  => $booking->booking_code,
                'name'  => $booking->hotel_name,
                'guest' => $booking->guest_name,
                'time'  => $booking->due_at->format('d/m H:i'),
            ];

            $this->notifier->to($booking->user, 'return_overdue_customer', $params, route('bookings.show', $booking), 'fa-triangle-exclamation');
            $this->notifier->to($booking->partner->user, 'return_overdue_partner', $params, route('partner.rentals.index'), 'fa-triangle-exclamation');

            $sent++;
        }

        return $sent;
    }

    /** Ajak pelanggan mengulas setelah layanan selesai (maksimal 3 hari sesudahnya). */
    private function reviewPrompt(): int
    {
        $sent = 0;

        $hotels = Booking::with('user')
            ->where('product_type', 'hotel')
            ->where('status', 'confirmed')
            ->whereNull('no_show_at')
            ->whereDate('check_out', '<=', today())
            ->whereDate('check_out', '>=', today()->subDays(3))
            ->whereDoesntHave('review')
            ->get();

        $cars = Booking::with('user')
            ->where('product_type', 'car')
            ->where('status', 'confirmed')
            ->whereNotNull('returned_at')
            ->where('returned_at', '>=', now()->subDays(3))
            ->whereDoesntHave('review')
            ->get();

        foreach ($hotels->concat($cars) as $booking) {
            if (! $this->claim($booking, 'review_prompt')) {
                continue;
            }

            $this->notifier->to($booking->user, 'review_prompt', ['name' => $booking->hotel_name],
                route('reviews.create', $booking), 'fa-star');

            $sent++;
        }

        return $sent;
    }
}