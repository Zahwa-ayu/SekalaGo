<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RoomType extends Model
{
    use HasTranslations;

    public const BED_TYPES  = ['single', 'double', 'queen', 'king', 'bunk'];
    public const MEAL_PLANS = ['room_only', 'breakfast', 'half_board', 'full_board', 'all_inclusive'];

    protected $fillable = [
        'hotel_id', 'base_price', 'capacity', 'bed_type', 'bed_count', 'smoking_allowed',
        'meal_plan', 'total_rooms', 'source_locale',
    ];

    protected function casts(): array
    {
        return ['smoking_allowed' => 'boolean'];
    }

    public function hotel()        { return $this->belongsTo(Hotel::class); }
    public function translations() { return $this->hasMany(RoomTypeTranslation::class); }
    public function stocks()       { return $this->hasMany(RoomStock::class); }
    public function facilities()   { return $this->belongsToMany(Facility::class, 'facility_room_type'); }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable')
            ->orderByDesc('is_cover')->orderBy('sort_order')->orderBy('id');
    }

    public function getNameAttribute(): ?string        { return $this->translated('name'); }
    public function getDescriptionAttribute(): ?string { return $this->translated('description'); }

    public function coverUrl(): ?string
    {
        return $this->photos->first()?->url();
    }

    protected static function booted(): void
    {
        static::deleting(fn (RoomType $roomType) => $roomType->photos->each->delete());
    }

    /**
     * Menambah kolom `open_nights`: jumlah malam (check-in s/d sehari sebelum check-out)
     * yang stoknya masih tersisa. Tersedia penuh jika open_nights == jumlah malam.
     */
    public function scopeWithOpenNights(Builder $query, Carbon $checkIn, Carbon $checkOut): Builder
    {
        $lastNight = $checkOut->copy()->subDay()->toDateString();

        return $query->withCount(['stocks as open_nights' => function ($q) use ($checkIn, $lastNight) {
            $q->whereBetween('date', [$checkIn->toDateString(), $lastNight])
              ->whereColumn('available', '>', 'booked');
        }]);
    }
}