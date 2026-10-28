<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['code'];

    public function hotels() { return $this->belongsToMany(Hotel::class); }

    public function getLabelAttribute(): string
{
    return __('facilities.' . $this->code);
}
}
