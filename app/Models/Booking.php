<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'user_id', 'product_type', 'hotel_id', 'room_type_id',
        'hotel_name', 'room_name', 'check_in', 'check_out', 'nights', 'guests', 'guest_name',
        'price_per_night', 'total_price', 'platform_fee', 'vendor_revenue',
        'currency_code', 'exchange_rate', 'total_price_display',
        'status', 'payment_status', 'payment_reference', 'expires_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in'            => 'date',
            'check_out'           => 'date',
            'expires_at'          => 'datetime',
            'paid_at'             => 'datetime',
            'exchange_rate'       => 'float',
            'total_price_display' => 'float',
        ];
    }

    public function user()     { return $this->belongsTo(User::class); }
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
}