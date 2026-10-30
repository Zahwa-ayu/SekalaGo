<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['company_name', 'corporate_code', 'deposit_balance', 'travel_policy_limit'];

    public function companyUsers() { return $this->hasMany(CompanyUser::class); }
    public function positions()    { return $this->hasMany(CompanyPosition::class); }
    public function transactions() { return $this->hasMany(DepositTransaction::class); }
}