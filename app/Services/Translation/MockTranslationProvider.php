<?php

namespace App\Services\Translation;

/**
 * Penerjemah palsu untuk pengembangan: hanya menambahkan penanda bahasa tujuan.
 * Ganti dengan implementasi API/AI sungguhan nanti.
 */
class MockTranslationProvider implements TranslationProvider
{
    public function translate(string $text, string $from, string $to): string
    {
        return "[{$to}] {$text}";
    }
}