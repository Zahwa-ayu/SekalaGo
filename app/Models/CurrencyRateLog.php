<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRateLog extends Model
{
    protected $fillable = ['currency_code', 'old_rate', 'new_rate', 'changed_by'];

    protected function casts(): array
    {
        return ['old_rate' => 'float', 'new_rate' => 'float'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}