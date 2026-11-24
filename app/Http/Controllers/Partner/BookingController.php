<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use PartnerHelpers;

    private const STATUSES = ['confirmed', 'pending_payment', 'cancelled', 'expired', 'all'];

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        $status = $request->query('status', 'confirmed');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $product = $request->query('product');
        abort_unless(in_array($product, [null, '', 'hotel', 'car'], true), 404);

        $base = Booking::where('partner_id', $partner->id)
            ->when($product, fn ($q, $type) => $q->where('product_type', $type));

        // Ringkasan hanya menghitung pesanan terkonfirmasi, mengikuti filter jenis produk
        $summary = (clone $base)
            ->where('status', 'confirmed')
            ->selectRaw('count(*) as orders, coalesce(sum(total_price), 0) as gross, coalesce(sum(platform_fee), 0) as fee, coalesce(sum(vendor_revenue), 0) as net')
            ->first();

        $bookings = (clone $base)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->search($request->query('q'), ['booking_code', 'guest_name', 'contact_name', 'contact_phone', 'hotel_name', 'user.email'])
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('partner.bookings.index', compact('summary', 'bookings', 'status'));
    }

    public function show(Request $request, Booking $booking)
    {
        $partner = $this->partner($request);

        // 404 (bukan 403) supaya mitra tidak bisa menebak kode booking milik mitra lain
        abort_unless($booking->partner_id === $partner->id, 404);

        return view('partner.bookings.show', compact('booking'));
    }
}