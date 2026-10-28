<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasTranslations;

    protected $fillable = ['hotel_id', 'base_price', 'capacity', 'total_rooms'];

    public function hotel()        { return $this->belongsTo(Hotel::class); }
    public function translations() { return $this->hasMany(RoomTypeTranslation::class); }
    public function stocks()       { return $this->hasMany(RoomStock::class); }

    public function getNameAttribute(): ?string        { return $this->translated('name'); }
    public function getDescriptionAttribute(): ?string { return $this->translated('description'); }
}