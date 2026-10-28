<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasTranslations;

    protected $fillable = ['partner_id', 'city', 'address', 'star_rating', 'status', 'source_locale'];

    public function partner()      { return $this->belongsTo(Partner::class); }
    public function translations() { return $this->hasMany(HotelTranslation::class); }
    public function roomTypes()    { return $this->hasMany(RoomType::class); }
    public function facilities()   { return $this->belongsToMany(Facility::class); }

    public function getNameAttribute(): ?string        { return $this->translated('name'); }
    public function getDescriptionAttribute(): ?string { return $this->translated('description'); }
}