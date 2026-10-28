<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    /**
     * Urutan: bahasa aktif -> bahasa fallback (en) -> terjemahan apa pun yang ada
     * (biasanya bahasa asli yang diisi mitra).
     */
    public function translated(string $field): ?string
    {
        $translations = $this->translations;

        $row = $translations->firstWhere('locale', app()->getLocale())
            ?? $translations->firstWhere('locale', config('app.fallback_locale'))
            ?? $translations->first();

        return $row?->{$field};
    }
}
