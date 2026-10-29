<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RoomType extends Model
{
    use HasTranslations;

    protected $fillable = ['hotel_id', 'base_price', 'capacity', 'total_rooms', 'source_locale'];

    public function hotel()        { return $this->belongsTo(Hotel::class); }
    public function translations() { return $this->hasMany(RoomTypeTranslation::class); }
    public function stocks()       { return $this->hasMany(RoomStock::class); }

    public function getNameAttribute(): ?string        { return $this->translated('name'); }
    public function getDescriptionAttribute(): ?string { return $this->translated('description'); }

    /**
     * Menambah kolom `open_nights`: jumlah malam (check-in s/d sehari sebelum check-out)
     * yang stoknya masih lebih dari 0. Tersedia penuh jika open_nights == jumlah malam.
     */
    public function scopeWithOpenNights(Builder $query, Carbon $checkIn, Carbon $checkOut): Builder
    {
        $lastNight = $checkOut->copy()->subDay()->toDateString();

        return $query->withCount(['stocks as open_nights' => function ($q) use ($checkIn, $lastNight) {
            $q->whereBetween('date', [$checkIn->toDateString(), $lastNight])
              ->where('available', '>', 0);
        }]);
    }
}