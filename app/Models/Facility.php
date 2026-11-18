<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class Facility extends Model
{
    /** Ikon bawaan untuk fasilitas dari seeder. */
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

    /** Ikon yang ditawarkan di pemilih ikon admin. */
    public const ICON_CHOICES = [
        'fa-wifi', 'fa-person-swimming', 'fa-square-parking', 'fa-utensils', 'fa-dumbbell', 'fa-spa',
        'fa-elevator', 'fa-bell-concierge', 'fa-van-shuttle', 'fa-tree', 'fa-mug-hot', 'fa-snowflake',
        'fa-tv', 'fa-shower', 'fa-bath', 'fa-bed', 'fa-couch', 'fa-lock', 'fa-vault', 'fa-baby',
        'fa-paw', 'fa-smoking-ban', 'fa-martini-glass', 'fa-umbrella-beach', 'fa-plug', 'fa-fan',
        'fa-fire-extinguisher', 'fa-wheelchair', 'fa-shirt', 'fa-water', 'fa-door-open', 'fa-wind',
        'fa-cart-shopping', 'fa-gamepad', 'fa-bus', 'fa-key',
    ];

    protected $fillable = ['code', 'scope', 'names', 'icon_class', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'names'      => 'array',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function hotels()    { return $this->belongsToMany(Hotel::class); }
    public function roomTypes() { return $this->belongsToMany(RoomType::class, 'facility_room_type'); }

    public function scopeForHotel($query) { return $query->whereIn('scope', ['hotel', 'both']); }
    public function scopeForRoom($query)  { return $query->whereIn('scope', ['room', 'both']); }

    /**
     * Pilihan untuk form mitra: fasilitas aktif sesuai jenisnya, ditambah yang sedang dipakai
     * walau sudah dinonaktifkan (supaya tidak hilang saat disunting).
     */
    public static function choices(string $for, array $keepIds = []): Collection
    {
        return static::query()
            ->when($for === 'hotel', fn ($q) => $q->forHotel(), fn ($q) => $q->forRoom())
            ->where(fn ($q) => $q
                ->where('is_active', true)
                ->when($keepIds !== [], fn ($w) => $w->orWhereIn('id', $keepIds)))
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
    }

    public function icon(): string
    {
        return $this->icon_class ?: (self::ICONS[$this->code] ?? 'fa-circle-check');
    }

    /** Nama di satu bahasa: dari admin, atau dari berkas bahasa bawaan; null jika tidak ada. */
    public function nameIn(string $locale): ?string
    {
        $stored = $this->names[$locale] ?? null;

        if (filled($stored)) {
            return $stored;
        }

        $key  = 'facilities.' . $this->code;
        $lang = Lang::get($key, [], $locale, false);

        return is_string($lang) && $lang !== $key ? $lang : null;
    }

    /** Nama untuk bahasa pengunjung, dengan cadangan ke Inggris lalu Indonesia. */
    public function getLabelAttribute(): string
    {
        foreach ([app()->getLocale(), config('app.fallback_locale', 'en'), 'id'] as $locale) {
            if ($name = $this->nameIn($locale)) {
                return $name;
            }
        }

        return Str::headline($this->code);
    }
}