<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTypeTranslation extends Model
{
    protected $fillable = ['room_type_id', 'locale', 'name', 'description', 'is_auto_translated'];

    protected function casts(): array { return ['is_auto_translated' => 'boolean']; }

    public function roomType() { return $this->belongsTo(RoomType::class); }
}
