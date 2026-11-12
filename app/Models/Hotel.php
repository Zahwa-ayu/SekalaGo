<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsActivity;

class Hotel extends Model
{
    use HasTranslations, LogsActivity;
    
    protected $fillable = [
        'partner_id', 'city', 'area', 'address', 'latitude', 'longitude', 'star_rating',
        'check_in_time', 'check_out_time', 'status', 'rejection_reason', 'source_locale', 'city_id'
    ];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float'];
    }

    public function partner()      { return $this->belongsTo(Partner::class); }
    public function translations() { return $this->hasMany(HotelTranslation::class); }
    public function roomTypes()    { return $this->hasMany(RoomType::class); }
    public function facilities()   { return $this->belongsToMany(Facility::class); }

    /** Foto sampul selalu di urutan pertama. */
    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable')
            ->orderByDesc('is_cover')->orderBy('sort_order')->orderBy('id');
    }

    public function nearbyPlaces()
    {
        return $this->hasMany(NearbyPlace::class)->orderByRaw('distance_km is null, distance_km');
    }

    public function getNameAttribute(): ?string        { return $this->translated('name'); }
    public function getDescriptionAttribute(): ?string { return $this->translated('description'); }

    public function checkInLabel(): string  { return substr($this->check_in_time ?? '14:00', 0, 5); }
    public function checkOutLabel(): string { return substr($this->check_out_time ?? '12:00', 0, 5); }

    public function coverUrl(): ?string
    {
        return $this->photos->first()?->url();
    }

    protected static function booted(): void
    {
        // Hapus berkas foto hotel dan foto semua tipe kamarnya (relasi polimorfik tidak ikut terhapus oleh basis data)
        static::deleting(function (Hotel $hotel) {
            $hotel->photos->each->delete();
            $hotel->roomTypes->each(fn ($roomType) => $roomType->photos->each->delete());
        });
    }

    // log
        protected function logAttributes(): array
    {
        return ['city', 'area', 'address', 'latitude', 'longitude', 'star_rating',
                'check_in_time', 'check_out_time', 'status', 'rejection_reason'];
    }

    // "created" dicatat manual di controller (nama hotel baru ada setelah terjemahan tersimpan)
    protected function logEvents(): array { return ['updated', 'deleted']; }

    public function logLabel(): string { return $this->name ?: 'Hotel #' . $this->getKey(); }

    public function logScope(): ?Model { return $this; }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'scope')->latest('created_at')->latest('id');
    }
} 