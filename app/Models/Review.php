<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'booking_id', 'user_id', 'partner_id', 'hotel_id', 'vehicle_id', 'rating', 'comment', 'reviewer_name',
        'is_visible', 'hidden_reason', 'hidden_by', 'hidden_at', 'partner_reply', 'partner_replied_at',
    ];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'hidden_at' => 'datetime', 'partner_replied_at' => 'datetime'];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function partner() { return $this->belongsTo(Partner::class); }
    public function hotel()   { return $this->belongsTo(Hotel::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}