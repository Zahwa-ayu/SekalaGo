<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    public const ICONS = [
        'wifi' => 'fa-wifi', 'pool' => 'fa-person-swimming', 'parking' => 'fa-square-parking',
        'restaurant' => 'fa-utensils', 'gym' => 'fa-dumbbell', 'spa' => 'fa-spa',
        'elevator' => 'fa-elevator', 'reception_24h' => 'fa-bell-concierge',
        'airport_shuttle' => 'fa-van-shuttle', 'meeting_room' => 'fa-people-roof',
        'laundry' => 'fa-shirt', 'accessible' => 'fa-wheelchair', 'garden' => 'fa-tree',
        'breakfast' => 'fa-mug-hot', 'ac' => 'fa-snowflake', 'tv' => 'fa-tv',
        'hot_water' => 'fa-shower', 'minibar' => 'fa-wine-bottle', 'kettle' => 'fa-mug-saucer',
        'desk' => 'fa-table', 'wardrobe' => 'fa-door-closed', 'balcony' => 'fa-door-open',
        'sea_view' => 'fa-water', 'bathtub' => 'fa-bath', 'hairdryer' => 'fa-wind',
        'safe' => 'fa-vault', 'refrigerator' => 'fa-temperature-low',
    ];

    protected $fillable = ['code', 'scope'];

    public function hotels()    { return $this->belongsToMany(Hotel::class); }
    public function roomTypes() { return $this->belongsToMany(RoomType::class, 'facility_room_type'); }

    public function scopeForHotel($query) { return $query->whereIn('scope', ['hotel', 'both']); }
    public function scopeForRoom($query)  { return $query->whereIn('scope', ['room', 'both']); }

    public function getLabelAttribute(): string
    {
        return __('facilities.' . $this->code);
    }

    public function icon(): string
    {
        return self::ICONS[$this->code] ?? 'fa-circle-check';
    }
}