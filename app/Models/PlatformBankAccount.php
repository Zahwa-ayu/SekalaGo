<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlatformBankAccount extends Model
{
    protected $fillable = ['bank_name', 'account_number', 'account_name', 'note', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function topupRequests() { return $this->hasMany(TopupRequest::class); }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}