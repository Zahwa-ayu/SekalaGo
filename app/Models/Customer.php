<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['user_id', 'full_name', 'identity_number', 'birth_date', 'gender'];
    public function user() { return $this->belongsTo(User::class); }

}
