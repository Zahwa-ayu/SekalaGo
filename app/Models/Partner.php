<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['user_id', 'company_name', 'business_license_no', 'bank_account_no', 'verification_status'];
    public function user() { return $this->belongsTo(User::class); }

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }
}
