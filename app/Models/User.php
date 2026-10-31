<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    protected $fillable = [
        'email', 'password', 'phone_number', 'user_type',
        'is_active', 'preferred_locale', 'preferred_currency',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): HasOne     { return $this->hasOne(Customer::class); }
    public function partner(): HasOne      { return $this->hasOne(Partner::class); }
    public function companyUser(): HasOne  { return $this->hasOne(CompanyUser::class); }
    public function adminStaff(): HasOne   { return $this->hasOne(AdminStaff::class); }

    public function dashboardRoute(): string
    {
        return match ($this->user_type) {
            'admin'     => 'admin.dashboard',
            'partner'   => 'partner.dashboard',
            'corporate' => 'corporate.dashboard',
            default     => 'home',
        };
    }

        protected function displayName(): Attribute
    {
        return Attribute::get(fn () => match ($this->user_type) {
            'admin'     => $this->adminStaff?->full_name,
            'customer'  => $this->customer?->full_name,
            'partner'   => $this->partner?->company_name,
            'corporate' => $this->companyUser?->full_name,
            default     => null,
        } ?? $this->email);
    }
}