<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelTranslation extends Model
{
    protected $fillable = ['hotel_id', 'locale', 'name', 'description', 'is_auto_translated'];

    protected function casts(): array { return ['is_auto_translated' => 'boolean']; }

    public function hotel() { return $this->belongsTo(Hotel::class); }
}
