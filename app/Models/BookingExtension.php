<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingExtension extends Model
{
    protected $fillable = [
        'booking_id', 'days', 'old_check_out', 'new_check_out', 'amount',
        'platform_fee', 'vendor_revenue', 'payment_method', 'payment_reference', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'old_check_out' => 'date',
            'new_check_out' => 'date',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}