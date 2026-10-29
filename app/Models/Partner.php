<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'business_license_no', 'bank_account_no',
        'verification_status', 'verification_note',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function hotels() { return $this->hasMany(Hotel::class); }

    /** Pesan alasan login ditolak, atau null jika mitra sudah terverifikasi. */
    public function loginBlockedMessage(): ?string
    {
        return match ($this->verification_status) {
            'verified' => null,
            'rejected' => __('ui.partner_login_rejected', ['note' => $this->verification_note ?? '-']),
            default    => __('ui.partner_login_pending'),
        };
    }
}