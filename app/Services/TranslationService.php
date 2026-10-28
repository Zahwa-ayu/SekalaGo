<?php

namespace App\Services;

use App\Services\Translation\TranslationProvider;
use Illuminate\Database\Eloquent\Model;

class TranslationService
{
    public function __construct(private TranslationProvider $provider)
    {
    }

    /**
     * Simpan teks sumber, lalu isi bahasa lain.
     *
     * @param array $source    ['name' => ..., 'description' => ...] dalam $sourceLocale
     * @param array $overrides [locale => ['name' => ..., 'description' => ...]] isian manual (opsional)
     */
    public function sync(Model $model, string $sourceLocale, array $source, array $overrides = []): void
    {
        $existing = $model->translations()->get()->keyBy('locale');

        $model->translations()->updateOrCreate(
            ['locale' => $sourceLocale],
            [
                'name'               => $source['name'],
                'description'        => $source['description'] ?? null,
                'is_auto_translated' => false,
            ]
        );

        foreach (array_keys(config('sekalago.locales')) as $locale) {
            if ($locale === $sourceLocale) {
                continue;
            }

            $row  = $existing->get($locale);
            $name = trim($overrides[$locale]['name'] ?? '');
            $desc = $overrides[$locale]['description'] ?? null;

            // Manual jika diisi, dan (belum ada / sebelumnya manual / isinya diubah mitra).
            $isManual = $name !== '' && (
                ! $row
                || ! $row->is_auto_translated
                || $name !== $row->name
                || ($desc ?? '') !== ($row->description ?? '')
            );

            if ($isManual) {
                $values = ['name' => $name, 'description' => $desc, 'is_auto_translated' => false];
            } else {
                $values = [
                    'name'               => $this->translate($source['name'], $sourceLocale, $locale),
                    'description'        => $this->translate($source['description'] ?? null, $sourceLocale, $locale),
                    'is_auto_translated' => true,
                ];
            }

            $model->translations()->updateOrCreate(['locale' => $locale], $values);
        }
    }

    /** Lengkapi bahasa yang belum ada (dipakai saat menambah bahasa baru). */
    public function fillMissing(Model $model): int
    {
        $translations = $model->translations()->get()->keyBy('locale');
        $source = $translations->get($model->source_locale) ?? $translations->first();

        if (! $source) {
            return 0;
        }

        $count = 0;

        foreach (array_keys(config('sekalago.locales')) as $locale) {
            if ($translations->has($locale)) {
                continue;
            }

            $model->translations()->create([
                'locale'             => $locale,
                'name'               => $this->translate($source->name, $source->locale, $locale),
                'description'        => $this->translate($source->description, $source->locale, $locale),
                'is_auto_translated' => true,
            ]);
            $count++;
        }

        return $count;
    }

    private function translate(?string $text, string $from, string $to): ?string
    {
        return filled($text) ? $this->provider->translate($text, $from, $to) : null;
    }
}