<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NearbyPlace extends Model
{
    public const TYPES = ['airport', 'station', 'port', 'beach', 'mall', 'attraction', 'restaurant', 'hospital', 'other'];

    public const ICONS = [
        'airport'    => 'fa-plane',
        'station'    => 'fa-train',
        'port'       => 'fa-ship',
        'beach'      => 'fa-umbrella-beach',
        'mall'       => 'fa-bag-shopping',
        'attraction' => 'fa-landmark',
        'restaurant' => 'fa-utensils',
        'hospital'   => 'fa-hospital',
        'other'      => 'fa-location-dot',
    ];

    protected $table = 'hotel_nearby_places';

    protected $fillable = ['hotel_id', 'name', 'type', 'distance_km'];

    protected function casts(): array
    {
        return ['distance_km' => 'float'];
    }

    public function hotel() { return $this->belongsTo(Hotel::class); }

    public function icon(): string
    {
        return self::ICONS[$this->type] ?? self::ICONS['other'];
    }

    /** Contoh: "350 m" atau "1,2 km". */
    public function distanceLabel(): ?string
    {
        if ($this->distance_km === null) {
            return null;
        }

        if ($this->distance_km < 1) {
            return round($this->distance_km * 1000) . ' m';
        }

        return rtrim(rtrim(number_format($this->distance_km, 1, '.', ''), '0'), '.') . ' km';
    }
}