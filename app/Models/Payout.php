<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'code', 'partner_id', 'status', 'total_amount', 'platform_fee_total', 'booking_count',
        'bank_account', 'schedule', 'transfer_reference', 'paid_at', 'paid_by', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function partner()  { return $this->belongsTo(Partner::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function payer()    { return $this->belongsTo(User::class, 'paid_by'); }
}