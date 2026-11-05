<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTranslation extends Model
{
    protected $fillable = ['vehicle_id', 'locale', 'name', 'description', 'is_auto_translated'];

    protected function casts(): array
    {
        return ['is_auto_translated' => 'boolean'];
    }

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}