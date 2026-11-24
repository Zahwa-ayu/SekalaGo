<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Partner;
use App\Models\Review;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public const WINDOW_DAYS = 30;

    public function __construct(private Notifier $notifier)
    {
    }

    /** "ok", atau alasannya: not_owner, not_completed, no_show, already, expired. */
    public function eligibility(Booking $booking, ?User $user): string
    {
        if (! $user || $booking->user_id !== $user->id) {
            return 'not_owner';
        }

        if ($booking->status !== 'confirmed') {
            return 'not_completed';
        }

        if ($booking->review()->exists()) {
            return 'already';
        }

        if ($booking->isCar()) {
            if (! $booking->returned_at) {
                return 'not_completed';
            }

            $done = $booking->returned_at;
        } else {
            if ($booking->no_show_at) {
                return 'no_show';
            }

            if ($booking->check_out->gt(today())) {
                return 'not_completed';
            }

            $done = $booking->check_out->copy()->setTime(12, 0);
        }

        return now()->gt($done->copy()->addDays(self::WINDOW_DAYS)) ? 'expired' : 'ok';
    }

    public function create(Booking $booking, User $user, int $rating, ?string $comment): Review
    {
        return DB::transaction(function () use ($booking, $user, $rating, $comment) {
            $locked = Booking::lockForUpdate()->findOrFail($booking->id);
            $state  = $this->eligibility($locked, $user);

            if ($state !== 'ok') {
                throw ValidationException::withMessages(['review' => __('ui.review_not_allowed_' . $state)]);
            }

            $review = Review::create([
                'booking_id'    => $locked->id,
                'user_id'       => $user->id,
                'partner_id'    => $locked->partner_id,
                'hotel_id'      => $locked->isCar() ? null : $locked->hotel_id,
                'vehicle_id'    => $locked->isCar() ? $locked->vehicle_id : null,
                'rating'        => $rating,
                'comment'       => $comment,
                'reviewer_name' => $this->maskName($locked->guest_name),
            ]);

            $this->refresh($review);

            $this->notifier->to($locked->partner->user, 'review_received', [
                'name'   => $locked->hotel_name,
                'rating' => $rating,
            ], route('partner.reviews.index'), 'fa-star');

            return $review;
        });
    }

    /** Mitra membalas ulasan tentang usahanya sendiri. */
    public function reply(Review $review, Partner $partner, string $text): Review
    {
        abort_unless($review->partner_id === $partner->id, 404);

        $review->update(['partner_reply' => $text, 'partner_replied_at' => now()]);

        $this->notifier->to($review->user, 'review_replied', [
            'name' => $review->booking->hotel_name,
        ], route('bookings.show', $review->booking_id), 'fa-comment-dots');

        return $review;
    }

    /** Admin menyembunyikan atau menampilkan kembali ulasan; rata-rata bintang dihitung ulang. */
    public function setVisibility(Review $review, bool $visible, User $admin, ?string $reason = null): Review
    {
        $review->update([
            'is_visible'    => $visible,
            'hidden_reason' => $visible ? null : $reason,
            'hidden_by'     => $visible ? null : $admin->id,
            'hidden_at'     => $visible ? null : now(),
        ]);

        $this->refresh($review);

        return $review;
    }

    /** Hitung ulang rata-rata dan jumlah ulasan yang tampil untuk hotel atau armada itu. */
    public function refresh(Review $review): void
    {
        [$model, $column, $id] = $review->hotel_id
            ? [Hotel::class, 'hotel_id', $review->hotel_id]
            : [Vehicle::class, 'vehicle_id', $review->vehicle_id];

        $stats = Review::visible()->where($column, $id)
            ->selectRaw('COUNT(*) AS n, COALESCE(AVG(rating), 0) AS average')
            ->first();

        $model::whereKey($id)->update(['rating_avg' => round((float) $stats->average, 2), 'rating_count' => (int) $stats->n]);
    }

    /** "Budi Santoso" menjadi "Budi S." */
    private function maskName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) < 2) {
            return mb_substr($parts[0] ?? 'Tamu', 0, 30);
        }

        return mb_substr($parts[0], 0, 30) . ' ' . mb_strtoupper(mb_substr(end($parts), 0, 1)) . '.';
    }
}