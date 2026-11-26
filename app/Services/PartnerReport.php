<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\Payout;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PartnerReport
{
    /** Tanggal pembayaran; pesanan lama tanpa paid_at memakai tanggal dibuat. */
    private const PAID_AT = 'COALESCE(bookings.paid_at, bookings.created_at)';

    public function __construct(private RevenueAnalytics $analytics, private PayoutService $payouts)
    {
    }

    public function build(Partner $partner, Carbon $start, Carbon $end, ?string $product): array
    {
        [$prevStart, $prevEnd] = $this->analytics->previous($start, $end);

        $summary  = $this->summary($partner, $start, $end, $product);
        $previous = $this->summary($partner, $prevStart, $prevEnd, $product);

        return [
            'summary'   => $summary,
            'deltas'    => [
                'earnings' => $this->delta($summary['earnings'], $previous['earnings']),
                'gmv'      => $this->delta($summary['gmv'], $previous['gmv']),
                'orders'   => $this->delta($summary['orders'], $previous['orders']),
            ],
            'prevStart' => $prevStart,
            'prevEnd'   => $prevEnd,
            'series'    => $this->series($partner, $start, $end, $product),
            'items'     => $this->items($partner, $start, $end, $product),
            'payouts'   => $this->payoutBlock($partner, $start, $end),
            'rating'    => $this->ratingBlock($partner, $start, $end),
        ];
    }

    private function summary(Partner $partner, Carbon $start, Carbon $end, ?string $product): array
    {
        $paid = $this->confirmed($partner, $start, $end, $product)
            ->selectRaw('COUNT(*) AS orders, COALESCE(SUM(bookings.total_price), 0) AS gmv, COALESCE(SUM(bookings.platform_fee), 0) AS fee, COALESCE(SUM(bookings.vendor_revenue), 0) AS earnings, COALESCE(SUM(bookings.nights), 0) AS units')
            ->first();

        // Setelah pembatalan berdenda, kolom hak mitra memuat bagian mitra dari denda yang ditahan
        $penalty = $this->applyItem(Booking::query())
            ->where('bookings.partner_id', $partner->id)
            ->where('bookings.status', 'cancelled')
            ->where('bookings.cancel_fee', '>', 0)
            ->whereBetween('bookings.cancelled_at', [$start, $end])
            ->when($product, fn ($q, $type) => $q->where('bookings.product_type', $type))
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(bookings.vendor_revenue), 0) AS share')
            ->first();

        $cancelled = $this->applyItem(Booking::query())
            ->where('bookings.partner_id', $partner->id)
            ->where('bookings.status', 'cancelled')
            ->where('bookings.payment_status', 'refunded')
            ->whereBetween('bookings.cancelled_at', [$start, $end])
            ->when($product, fn ($q, $type) => $q->where('bookings.product_type', $type))
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(bookings.refund_amount), 0) AS refunded')
            ->first();

        // Denda keterlambatan dibayar langsung ke mitra: hanya informasi
        $late = $this->applyItem(Booking::query())
            ->where('bookings.partner_id', $partner->id)
            ->where('bookings.product_type', 'car')
            ->where('bookings.status', 'confirmed')
            ->where('bookings.late_fee', '>', 0)
            ->whereBetween('bookings.returned_at', [$start, $end])
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(bookings.late_fee), 0) AS fees')
            ->first();

        $orders   = (int) $paid->orders;
        $earnings = (int) $paid->earnings;

        return [
            'orders'           => $orders,
            'gmv'              => (int) $paid->gmv,
            'commission'       => (int) $paid->fee,
            'earnings'         => $earnings,
            'units'            => (int) $paid->units,
            'average'          => $orders > 0 ? (int) round($paid->gmv / $orders) : 0,
            'cancel_fee_count' => (int) $penalty->n,
            'cancel_fee_share' => (int) $penalty->share,
            'income'           => $earnings + (int) $penalty->share,
            'cancelled'        => (int) $cancelled->n,
            'refunded'         => (int) $cancelled->refunded,
            'late_fee_count'   => (int) $late->n,
            'late_fees'        => (int) $late->fees,
        ];
    }

    /** Deret waktu: hari yang kosong diisi nol; rentang panjang dikelompokkan per bulan. */
    private function series(Partner $partner, Carbon $start, Carbon $end, ?string $product): array
    {
        $monthly = $this->analytics->daysIn($start, $end) > 62;
        $bucket  = $monthly ? "DATE_FORMAT(" . self::PAID_AT . ", '%Y-%m')" : "DATE(" . self::PAID_AT . ")";

        $rows = $this->confirmed($partner, $start, $end, $product)
            ->selectRaw("{$bucket} AS bucket, COUNT(*) AS orders, SUM(bookings.vendor_revenue) AS earnings")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = ['labels' => [], 'earnings' => [], 'orders' => []];
        $cursor = $monthly ? $start->copy()->startOfMonth() : $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $row = $rows->get($monthly ? $cursor->format('Y-m') : $cursor->toDateString());

            $series['labels'][]   = $monthly ? $cursor->translatedFormat('M Y') : $cursor->format('d/m');
            $series['earnings'][] = (int) ($row->earnings ?? 0);
            $series['orders'][]   = (int) ($row->orders ?? 0);

            $cursor = $monthly ? $cursor->copy()->addMonthNoOverflow() : $cursor->copy()->addDay();
        }

        return $series;
    }

    /** Per hotel atau armada (nama yang tercatat pada pesanan). */
    private function items(Partner $partner, Carbon $start, Carbon $end, ?string $product): Collection
    {
        return $this->confirmed($partner, $start, $end, $product)
            ->selectRaw('bookings.product_type AS type, bookings.hotel_name AS name, COUNT(*) AS orders, SUM(bookings.nights) AS units, SUM(bookings.total_price) AS gmv, SUM(bookings.vendor_revenue) AS earnings')
            ->groupBy('bookings.product_type', 'bookings.hotel_name')
            ->orderByDesc('earnings')
            ->get();
    }

    private function payoutBlock(Partner $partner, Carbon $start, Carbon $end): array
    {
        $overview = $this->payouts->overview($partner->id)->first();

        return [
            'ready'       => (int) ($overview['eligible'] ?? 0),
            'waiting'     => (int) ($overview['waiting'] ?? 0),
            'pending'     => (int) Payout::where('partner_id', $partner->id)->where('status', 'pending')->sum('total_amount'),
            'transferred' => (int) Payout::where('partner_id', $partner->id)->where('status', 'paid')->whereBetween('paid_at', [$start, $end])->sum('total_amount'),
        ];
    }

    private function ratingBlock(Partner $partner, Carbon $start, Carbon $end): array
    {
        $all = Review::visible()->where('partner_id', $partner->id)
            ->when($this->itemId, fn ($q) => $q->where($this->itemType === 'car' ? 'vehicle_id' : 'hotel_id', $this->itemId));

        return [
            'average'   => round((float) (clone $all)->avg('rating'), 1),
            'count'     => (clone $all)->count(),
            'in_period' => (clone $all)->whereBetween('created_at', [$start, $end])->count(),
        ];
    }

    /** Rincian pesanan untuk CSV, dibaca bertahap agar hemat memori. */
    public function detailRows(Partner $partner, Carbon $start, Carbon $end, ?string $product): \Generator
    {
        $query = $this->confirmed($partner, $start, $end, $product)
            ->leftJoin('payouts', 'payouts.id', '=', 'bookings.payout_id')
            ->selectRaw(self::PAID_AT . ' AS paid_on, bookings.booking_code, bookings.product_type, bookings.hotel_name, bookings.room_name, bookings.guest_name, bookings.check_in, bookings.check_out, bookings.nights, bookings.total_price, bookings.platform_fee, bookings.vendor_revenue, payouts.code AS payout_code, payouts.status AS payout_status')
            ->orderByRaw(self::PAID_AT);

        foreach ($query->cursor() as $row) {
            yield $row;
        }
    }

    /** Pesanan dibatalkan dengan denda pada periode (bagian mitra dari denda). */
    public function penaltyRows(Partner $partner, Carbon $start, Carbon $end, ?string $product): Collection
    {
        return $this->applyItem(Booking::query())
            ->where('bookings.partner_id', $partner->id)
            ->where('bookings.status', 'cancelled')
            ->where('bookings.cancel_fee', '>', 0)
            ->whereBetween('bookings.cancelled_at', [$start, $end])
            ->when($product, fn ($q, $type) => $q->where('bookings.product_type', $type))
            ->orderBy('bookings.cancelled_at')
            ->get(['booking_code', 'hotel_name', 'cancelled_at', 'cancel_fee', 'vendor_revenue']);
    }

    private function confirmed(Partner $partner, Carbon $start, Carbon $end, ?string $product): Builder
    {
        return $this->applyItem(Booking::query())
            ->where('bookings.partner_id', $partner->id)
            ->where('bookings.status', 'confirmed')
            ->whereRaw(self::PAID_AT . ' BETWEEN ? AND ?', [$start, $end])
            ->when($product, fn ($q, $type) => $q->where('bookings.product_type', $type));
    }

    private function delta(int $now, int $before): ?float
    {
        return $before > 0 ? round(($now - $before) / $before * 100, 1) : null;
    }

    private ?string $itemType = null;
    private ?int $itemId = null;

    /** Batasi laporan ke satu hotel atau armada: 'hotel:12' atau 'car:5'. */
    public function onlyItem(?string $key): static
    {
        if ($key && preg_match('/^(hotel|car):(\d+)$/', $key, $m)) {
            $this->itemType = $m[1];
            $this->itemId   = (int) $m[2];
        }

        return $this;
    }

    private function applyItem(Builder $query): Builder
    {
        if (! $this->itemId) {
            return $query;
        }

        return $query
            ->where('bookings.product_type', $this->itemType)
            ->where($this->itemType === 'car' ? 'bookings.vehicle_id' : 'bookings.hotel_id', $this->itemId);
    }
}