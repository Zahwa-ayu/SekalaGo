<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteBanner extends Model
{
    /** Banner yang tersedia dan foto bawaannya (ID foto Unsplash). */
    public const DEFAULTS = [
        'home'   => 'photo-1507525428034-b723cf961d3e',
        'hotels' => 'photo-1540555700478-4be289fbecef',
        'cars'   => 'photo-1469854523086-cc02fe5d8800',
    ];

    protected $fillable = ['key', 'image_path', 'updated_by'];

    public static function defaultUrl(string $key, int $width = 1920): string
    {
        return 'https://images.unsplash.com/' . self::DEFAULTS[$key] . "?auto=format&fit=crop&w={$width}&q=80";
    }

    /** Alamat gambar yang dipakai halaman: gambar kustom admin, atau gambar bawaan. */
    public static function urlFor(string $key, int $width = 1920): string
    {
        $path = self::customPaths()[$key] ?? null;

        return $path ? asset('storage/' . $path) : self::defaultUrl($key, $width);
    }

    /** @return array<string, string> key => path gambar kustom */
    private static function customPaths(): array
    {
        try {
            return Cache::rememberForever('site_banners', fn () => self::query()->pluck('image_path', 'key')->all());
        } catch (\Throwable) {
            return [];   // tabel belum ada atau database bermasalah: pakai gambar bawaan
        }
    }

    /** Dipanggil setiap kali admin mengubah banner. */
    public static function flush(): void
    {
        Cache::forget('site_banners');
    }
}