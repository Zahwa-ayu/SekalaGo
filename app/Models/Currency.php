<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['code', 'symbol', 'name', 'decimal_places', 'rate_to_base'];

    protected function casts(): array
    {
        return ['rate_to_base' => 'float'];
    }
}