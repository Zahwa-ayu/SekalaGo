<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Searchable;

class Company extends Model
{
    use Searchable;
    protected $fillable = [
        'company_name', 'corporate_code', 'deposit_balance',
        'travel_policy_limit', 'default_monthly_budget',
        'legal_form', 'tax_id', 'business_license_no', 'address', 'city_id', 'phone', 'industry',
        'tax_document_path', 'license_document_path', 'source',
        'verification_status', 'verification_note', 'verified_at', 'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime'
        ];
    }

    public function city()     { return $this->belongsTo(City::class); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /** Pesan di halaman login untuk perusahaan yang belum bisa dipakai. */
    public function loginBlockedMessage(): string
    {
        return $this->verification_status === 'rejected'
            ? __('ui.company_login_rejected', ['note' => $this->verification_note ?: '-'])
            : __('ui.company_login_pending');
    }


    public function companyUsers() { return $this->hasMany(CompanyUser::class); }
    public function positions()    { return $this->hasMany(CompanyPosition::class); }
    public function transactions() { return $this->hasMany(DepositTransaction::class); }
}