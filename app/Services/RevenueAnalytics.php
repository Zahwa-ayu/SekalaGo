<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RevenueAnalytics
{
    public const PRESETS = ['this_month', 'last_month', 'last_7', 'last_30', 'this_year', 'custom'];

    /** Tanggal pembayaran; pesanan lama tanpa paid_at memakai tanggal dibuat. */
    private const PAID_AT = 'COALESCE(bookings.paid_at, bookings.created_at)';

    /** @return array{0: Carbon, 1: Carbon} awal dan akhir periode */
    public function period(string $preset, ?string $from = null, ?string $to = null): array
    {
        $today = today();

        return match ($preset) {
            'last_month' => [
                $today->copy()->subMonthNoOverflow()->startOfMonth(),
                $today->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'last_7'    => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            'last_30'   => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay()],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()->endOfDay()],
            'custom'    => [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()],
            default     => [$today->copy()->startOfMonth(), $today->copy()->endOfDay()],
        };
    }

    public function daysIn(Carbon $start, Carbon $end): int
    {
        return (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
    }

    /** Pembanding: rentang yang sama panjang, tepat sebelum periode ini. */
    public function previous(Carbon $start, Carbon $end): array
    {
        $days = $this->daysIn($start, $end);

        return [
            $start->copy()->subDays($days)->startOfDay(),
            $start->copy()->subDay()->endOfDay(),
        ];
    }

    public function summary(Carbon $start, Carbon $end, ?string $product): array
    {
        $paid = $this->confirmed($start, $end, $product)
            ->selectRaw('COUNT(*) AS orders, COALESCE(SUM(bookings.total_price), 0) AS gmv, COALESCE(SUM(bookings.platform_fee), 0) AS fee, COALESCE(SUM(bookings.vendor_revenue), 0) AS vendor')
            ->first();

        $refund = Booking::query()
            ->where('bookings.payment_status', 'refunded')
            ->whereBetween('bookings.refunded_at', [$start, $end])
            ->when($product, fn ($q, $type) => $q->where('bookings.product_type', $type))
            ->selectRaw('COUNT(*) AS refunds, COALESCE(SUM(bookings.refund_amount), 0) AS refunded')
            ->first();

        $orders  = (int) $paid->orders;
        $gmv     = (int) $paid->gmv;
        $fee     = (int) $paid->fee;
        $refunds = (int) $refund->refunds;

        return [
            'orders'      => $orders,
            'gmv'         => $gmv,
            'fee'         => $fee,
            'vendor'      => (int) $paid->vendor,
            'average'     => $orders > 0 ? (int) round($gmv / $orders) : 0,
            'take_rate'   => $gmv > 0 ? round($fee / $gmv * 100, 1) : 0.0,
            'refunds'     => $refunds,
            'refunded'    => (int) $refund->refunded,
            'cancel_rate' => ($orders + $refunds) > 0 ? round($refunds / ($orders + $refunds) * 100, 1) : 0.0,
        ];
    }

    /** Deret waktu untuk grafik; hari yang kosong diisi nol. */
    public function series(Carbon $start, Carbon $end, ?string $product): array
    {
        $monthly = $this->daysIn($start, $end) > 62;
        $bucket  = $monthly
            ? "DATE_FORMAT(" . self::PAID_AT . ", '%Y-%m')"
            : "DATE(" . self::PAID_AT . ")";

        $rows = $this->confirmed($start, $end, $product)
            ->selectRaw("{$bucket} AS bucket, COUNT(*) AS orders, SUM(bookings.total_price) AS gmv, SUM(bookings.platform_fee) AS fee")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = ['labels' => [], 'gmv' => [], 'fee' => [], 'orders' => [], 'monthly' => $monthly];

        $cursor = $monthly ? $start->copy()->startOfMonth() : $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $row = $rows->get($monthly ? $cursor->format('Y-m') : $cursor->toDateString());

            $series['labels'][] = $monthly ? $cursor->translatedFormat('M Y') : $cursor->format('d/m');
            $series['gmv'][]    = (int) ($row->gmv ?? 0);
            $series['fee'][]    = (int) ($row->fee ?? 0);
            $series['orders'][] = (int) ($row->orders ?? 0);

            $cursor = $monthly ? $cursor->copy()->addMonthNoOverflow() : $cursor->copy()->addDay();
        }

        return $series;
    }

    public function byProduct(Carbon $start, Carbon $end, ?string $product): Collection
    {
        return $this->confirmed($start, $end, $product)
            ->selectRaw('bookings.product_type AS type, COUNT(*) AS orders, SUM(bookings.total_price) AS gmv, SUM(bookings.platform_fee) AS fee')
            ->groupBy('bookings.product_type')
            ->get()
            ->map(fn ($row) => $this->row((string) $row->type, $row))
            ->sortByDesc('gmv')
            ->values();
    }

    /** Pelanggan umum (b2c) dan corporate (b2b). */
    public function bySegment(Carbon $start, Carbon $end, ?string $product): Collection
    {
        return $this->confirmed($start, $end, $product)
            ->selectRaw("CASE WHEN bookings.company_id IS NULL THEN 'b2c' ELSE 'b2b' END AS segment, COUNT(*) AS orders, SUM(bookings.total_price) AS gmv, SUM(bookings.platform_fee) AS fee")
            ->groupBy('segment')
            ->get()
            ->map(fn ($row) => $this->row((string) $row->segment, $row))
            ->keyBy('key');
    }

    public function topPartners(Carbon $start, Carbon $end, ?string $product, int $limit = 10): Collection
    {
        return $this->confirmed($start, $end, $product)
            ->join('partners', 'partners.id', '=', 'bookings.partner_id')
            ->selectRaw('partners.company_name AS name, COUNT(*) AS orders, SUM(bookings.total_price) AS gmv, SUM(bookings.platform_fee) AS fee')
            ->groupBy('partners.id', 'partners.company_name')
            ->orderByDesc('gmv')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->row((string) $row->name, $row));
    }

    /** Kota dari hotel atau armada yang dipesan. */
    public function topCities(Carbon $start, Carbon $end, ?string $product, int $limit = 8): Collection
    {
        return $this->confirmed($start, $end, $product)
            ->leftJoin('hotels', 'hotels.id', '=', 'bookings.hotel_id')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'bookings.vehicle_id')
            ->whereRaw('COALESCE(hotels.city, vehicles.city) IS NOT NULL')
            ->selectRaw('COALESCE(hotels.city, vehicles.city) AS city, COUNT(*) AS orders, SUM(bookings.total_price) AS gmv, SUM(bookings.platform_fee) AS fee')
            ->groupBy('city')
            ->orderByDesc('gmv')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->row((string) $row->city, $row));
    }

    /** Denda keterlambatan yang tercatat: dibayar langsung ke vendor, bukan pendapatan platform. */
    public function lateFees(Carbon $start, Carbon $end): array
    {
        $row = Booking::query()
            ->where('product_type', 'car')
            ->where('status', 'confirmed')
            ->where('late_fee', '>', 0)
            ->whereBetween('returned_at', [$start, $end])
            ->selectRaw('COUNT(*) AS rentals, COALESCE(SUM(late_fee), 0) AS fees')
            ->first();

        return ['rentals' => (int) $row->rentals, 'fees' => (int) $row->fees];
    }

    /** Rincian pesanan untuk CSV, dibaca bertahap agar hemat memori. */
    public function detailRows(Carbon $start, Carbon $end, ?string $product): \Generator
    {
        $query = $this->confirmed($start, $end, $product)
            ->join('partners', 'partners.id', '=', 'bookings.partner_id')
            ->leftJoin('hotels', 'hotels.id', '=', 'bookings.hotel_id')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'bookings.vehicle_id')
            ->selectRaw(self::PAID_AT . ' AS paid_on, bookings.booking_code, bookings.product_type, partners.company_name AS partner, COALESCE(hotels.city, vehicles.city) AS city, bookings.company_id, bookings.total_price, bookings.platform_fee, bookings.vendor_revenue')
            ->orderByRaw(self::PAID_AT);

        foreach ($query->cursor() as $row) {
            yield $row;
        }
    }

    // ---------------------------------------------------------------- helper

    /** Pesanan terkonfirmasi yang dibayar dalam periode. */
    private function confirmed(Carbon $start, Carbon $end, ?string $product): Builder
    {
        return Booking::query()
            ->where('bookings.status', 'confirmed')
            ->whereRaw(self::PAID_AT . ' BETWEEN ? AND ?', [$start, $end])
            ->when($product, fn ($q, $type) => $q->where('bookings.product_type', $type));
    }

    private function row(string $key, $row): array
    {
        return [
            'key'    => $key,
            'orders' => (int) $row->orders,
            'gmv'    => (int) $row->gmv,
            'fee'    => (int) $row->fee,
        ];
    }
}