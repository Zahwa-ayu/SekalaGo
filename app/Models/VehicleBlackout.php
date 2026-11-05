<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleBlackout extends Model
{
    protected $fillable = ['vehicle_id', 'start_date', 'end_date', 'note'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}