<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = ['slug', 'template', 'sort_order', 'is_active', 'show_in_footer'];

    protected $casts = [
        'is_active'      => 'boolean',
        'show_in_footer' => 'boolean',
        'sort_order'     => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    /** Halaman aktif yang ditampilkan di footer, urut sesuai pengaturan admin. */
    public function scopeFooter(Builder $query): Builder
    {
        return $query->with('translations')
            ->where('is_active', true)
            ->where('show_in_footer', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Terjemahan untuk bahasa aktif; kalau tidak ada, jatuh ke English, lalu Indonesia. */
    public function translation(?string $locale = null): ?PageTranslation
    {
        $locale ??= app()->getLocale();
        $all = $this->translations;

        return $all->firstWhere('locale', $locale)
            ?? $all->firstWhere('locale', 'en')
            ?? $all->firstWhere('locale', 'id')
            ?? $all->first();
    }

    public function getTitleAttribute(): string
    {
        return $this->translation()?->title ?? $this->slug;
    }

    /** Bagian tambahan (kartu angka, poin keunggulan) dari terjemahan yang sama dengan judul. */
    public function getExtrasAttribute(): array
    {
        return $this->translation()?->extras ?? [];
    }

    public function getBodyAttribute(): string
    {
        return $this->translation()?->body ?? '';
    }
}