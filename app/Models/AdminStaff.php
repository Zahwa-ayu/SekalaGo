<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminStaff extends Model
{
    protected $table = 'admin_staffs';

    protected $fillable = ['user_id', 'full_name', 'position'];

    public function user() { return $this->belongsTo(User::class); }
}
