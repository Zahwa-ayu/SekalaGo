<?php

namespace App\Services\Translation;

interface TranslationProvider
{
    public function translate(string $text, string $from, string $to): string;
}