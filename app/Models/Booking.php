<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'user_id', 'partner_id', 'product_type', 'hotel_id', 'room_type_id',
        'hotel_name', 'room_name', 'check_in', 'check_out', 'nights', 'guests', 'guest_name',
        'price_per_night', 'total_price', 'platform_fee', 'vendor_revenue',
        'currency_code', 'exchange_rate', 'total_price_display',
        'status', 'payment_status', 'payment_reference', 'expires_at', 'paid_at',
        'free_cancel_days', 'cancelled_at', 'cancelled_by', 'cancellation_reason',
        'refund_amount', 'refunded_at', 'refund_reference',
    ];

    protected function casts(): array
    {
        return [
            'check_in'            => 'date',
            'check_out'           => 'date',
            'expires_at'          => 'datetime',
            'paid_at'             => 'datetime',
            'cancelled_at'        => 'datetime',
            'refunded_at'         => 'datetime',
            'exchange_rate'       => 'float',
            'total_price_display' => 'float',
            'free_cancel_days'    => 'integer',
        ];
    }

    public function user()     { return $this->belongsTo(User::class); }
    public function partner()  { return $this->belongsTo(Partner::class); }
    public function hotel()    { return $this->belongsTo(Hotel::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }

    /** Total dalam mata uang yang dipilih pelanggan saat memesan (kurs terkunci). */
    public function displayTotal(): string
    {
        $currency = Currency::find($this->currency_code);
        $decimals = $currency?->decimal_places ?? 0;
        [$decimal, $thousand] = app()->getLocale() === 'id' ? [',', '.'] : ['.', ','];

        return ($currency?->symbol ?? $this->currency_code) . ' '
            . number_format($this->total_price_display, $decimals, $decimal, $thousand);
    }

    /** Pembatalan gratis berlaku sampai tanggal ini (check-in dikurangi batas hari dari MOU). */
    public function freeCancelDeadline(): Carbon
    {
        return $this->check_in->copy()->subDays($this->free_cancel_days)->startOfDay();
    }

    public function canBeCancelledByCustomer(): bool
    {
        if ($this->status === 'pending_payment') {
            return true;
        }

        return $this->status === 'confirmed' && today()->lte($this->freeCancelDeadline());
    }

    public function canBeCancelledByAdmin(): bool
    {
        return $this->status === 'confirmed' && today()->lte($this->check_in);
    }
}