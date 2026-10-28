<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomStock extends Model
{
    protected $fillable = ['room_type_id', 'date', 'available'];

    protected function casts(): array { return ['date' => 'date']; }

    public function roomType() { return $this->belongsTo(RoomType::class); }
}
