<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPosition extends Model
{
    protected $fillable = ['company_id', 'name', 'booking_limit'];

    public function company() { return $this->belongsTo(Company::class); }
    public function members() { return $this->hasMany(CompanyUser::class, 'position_id'); }
}