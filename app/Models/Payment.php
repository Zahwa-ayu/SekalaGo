<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'provider', 'order_id', 'amount', 'status', 'payment_type', 'transaction_id',
        'snap_token', 'redirect_url', 'expires_at', 'paid_at',
        'refund_status', 'refund_amount', 'refund_reference', 'refunded_at', 'last_payload',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'   => 'datetime',
            'paid_at'      => 'datetime',
            'refunded_at'  => 'datetime',
            'last_payload' => 'array',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
}