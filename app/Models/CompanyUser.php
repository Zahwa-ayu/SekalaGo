<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'full_name', 'role_in_company', 'department', 'position_id',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function company()  { return $this->belongsTo(Company::class); }
    public function position() { return $this->belongsTo(CompanyPosition::class, 'position_id'); }

    public function isFinance(): bool
    {
        return $this->role_in_company === 'admin_finance';
    }

    public function displayName(): string
    {
        return $this->full_name ?: $this->user->email;
    }

    /** Limit per pemesanan: dari jabatan, atau limit default perusahaan jika belum punya jabatan. */
    public function bookingLimit(): int
    {
        return (int) ($this->position?->booking_limit ?? $this->company->travel_policy_limit);
    }
}