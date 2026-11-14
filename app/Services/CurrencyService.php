<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Collection;

class CurrencyService
{
    protected ?Collection $currencies = null;
    protected string $code;

    public function __construct()
    {
        $this->code = config('sekalago.default_currency');
    }

    public function all(): Collection
    {
        return $this->currencies ??= Currency::orderBy('code')->get()->keyBy('code');
    }

        /** Mata uang yang boleh dipilih pengunjung (yang aktif). */
    public function selectable(): Collection
    {
        return $this->all()->filter(fn (Currency $currency) => $currency->is_active);
    }

    public function use(string $code): void
    {
        if ($this->selectable()->has($code)) {
            $this->code = $code;
        }
    }

    public function current(): Currency
    {
        $selected = $this->all()->get($this->code);

        // Pilihan yang sudah dinonaktifkan admin kembali ke mata uang dasar
        return ($selected && $selected->is_active)
            ? $selected
            : $this->all()->get(config('sekalago.default_currency'));
    }

    /** Harga dasar (IDR) -> format tampilan dalam mata uang aktif */
    public function format(int|float $idrAmount, ?string $code = null): string
    {
        $currency = $code ? $this->all()->get($code) : $this->current();

        $value = round($idrAmount / $currency->rate_to_base, $currency->decimal_places);

        [$decimal, $thousand] = app()->getLocale() === 'id' ? [',', '.'] : ['.', ','];

        return $currency->symbol . ' ' . number_format($value, $currency->decimal_places, $decimal, $thousand);
    }
}