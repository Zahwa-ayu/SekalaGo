<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['code', 'symbol', 'name', 'decimal_places', 'rate_to_base', 'is_active'];

    protected function casts(): array
    {
        return [
            'rate_to_base' => 'float',
            'is_active'    => 'boolean',
        ];
    }

    /** Mata uang dasar (Rupiah): kurs selalu 1, tidak bisa dinonaktifkan atau dihapus. */
    public function isBase(): bool
    {
        return $this->code === config('sekalago.default_currency');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function rateLogs()
    {
        return $this->hasMany(CurrencyRateLog::class, 'currency_code', 'code')->latest('id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'currency_code', 'code');
    }
}