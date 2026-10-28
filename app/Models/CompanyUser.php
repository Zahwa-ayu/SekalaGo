<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    protected $fillable = ['company_id', 'user_id', 'role_in_company', 'department'];
    public function user()    { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(Company::class); }
}
