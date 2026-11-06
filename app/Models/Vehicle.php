<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasTranslations, LogsActivity;

    public const TYPES         = ['city_car', 'sedan', 'mpv', 'suv', 'van', 'minibus', 'luxury'];
    public const TRANSMISSIONS = ['manual', 'automatic'];
    public const FUEL_TYPES    = ['petrol', 'diesel', 'hybrid', 'electric'];
    public const DRIVER_MODES  = ['both', 'with_driver', 'self_drive'];

    public const FEATURE_ICONS = [
        'ac'        => 'fa-snowflake',
        'audio'     => 'fa-music',
        'usb'       => 'fa-plug',
        'gps'       => 'fa-location-crosshairs',
        'baby_seat' => 'fa-baby',
        'roof_rack' => 'fa-suitcase',
        'sunroof'   => 'fa-sun',
        'airbag'    => 'fa-shield-halved',
    ];

    protected $fillable = [
        'partner_id', 'city', 'area', 'address',
        'type', 'transmission', 'fuel_type', 'seats', 'luggage_capacity', 'model_year', 'features',
        'units', 'price_per_day', 'min_days', 'driver_mode', 'driver_fee_per_day', 'extras_note',
        'status', 'rejection_reason', 'source_locale',
    ];

    protected function casts(): array
    {
        return ['features' => 'array'];
    }

    public function partner()      { return $this->belongsTo(Partner::class); }
    public function translations() { return $this->hasMany(VehicleTranslation::class); }
    public function blackouts()    { return $this->hasMany(VehicleBlackout::class); }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable')
            ->orderByDesc('is_cover')->orderBy('sort_order')->orderBy('id');
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'scope')->latest('created_at')->latest('id');
    }

    public function getNameAttribute(): ?string        { return $this->translated('name'); }
    public function getDescriptionAttribute(): ?string { return $this->translated('description'); }

    public function coverUrl(): ?string
    {
        return $this->photos->first()?->url();
    }

    public function offersDriver(): bool    { return in_array($this->driver_mode, ['both', 'with_driver'], true); }
    public function offersSelfDrive(): bool { return in_array($this->driver_mode, ['both', 'self_drive'], true); }

    // --- Log aktivitas ---

    protected function logAttributes(): array
    {
        return ['city', 'area', 'address', 'type', 'transmission', 'fuel_type', 'seats', 'units',
                'price_per_day', 'min_days', 'driver_mode', 'driver_fee_per_day', 'status', 'rejection_reason'];
    }

    // "created" dicatat manual di controller (nama armada baru ada setelah terjemahan tersimpan)
    protected function logEvents(): array { return ['updated', 'deleted']; }

    public function logLabel(): string { return $this->name ?: 'Vehicle #' . $this->getKey(); }

    public function logScope(): ?Model { return $this; }

    protected static function booted(): void
    {
        // Berkas foto ikut dihapus (relasi polimorfik tidak terhapus oleh basis data)
        static::deleting(fn (Vehicle $vehicle) => $vehicle->photos->each->delete());
    }

        /**
     * Armada yang bisa disewa pada rentang tanggal: sudah disetujui dan tidak terkena tanggal blokir.
     * Hari yang ditempati: tanggal ambil sampai sehari sebelum tanggal kembali.
     */
    public function scopeAvailableFor(Builder $query, Carbon $pickup, Carbon $return): Builder
    {
        $lastDay = $return->copy()->subDay()->toDateString();

        return $query
            ->where('status', 'approved')
            ->where('units', '>', 0)
            ->whereDoesntHave('blackouts', fn ($q) => $q
                ->where('start_date', '<=', $lastDay)
                ->where('end_date', '>=', $pickup->toDateString()));
    }
}