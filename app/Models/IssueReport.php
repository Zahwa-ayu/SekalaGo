<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Searchable;


class IssueReport extends Model
{
    use Searchable;
    protected $fillable = [
        'code', 'booking_id', 'user_id', 'partner_id', 'category', 'description', 'evidence_path',
        'status', 'resolution', 'partner_response', 'partner_responded_at', 'handled_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['partner_responded_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function partner() { return $this->belongsTo(Partner::class); }
    public function handler() { return $this->belongsTo(User::class, 'handled_by'); }
}