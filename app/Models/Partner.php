<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'business_license_no', 'bank_account_no',
        'verification_status', 'verification_note',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function hotels()     { return $this->hasMany(Hotel::class); }
    public function agreements() { return $this->hasMany(PartnerAgreement::class); }

    /** MOU terbaru (versi lama yang diganti selalu lebih tua). */
    public function latestAgreement()
    {
        return $this->hasOne(PartnerAgreement::class)->latestOfMany();
    }

    public function hasSignedAgreement(): bool
    {
        return $this->agreements()->where('status', 'signed')->exists();
    }

    /** Komisi dari MOU yang ditandatangani; cadangan: nilai default di config. */
    public function commissionRate(): float
    {
        $agreement = $this->agreements()->where('status', 'signed')->latest('signed_at')->first();

        return $agreement ? (float) $agreement->commission_rate : (float) config('sekalago.commission_rate');
    }

    /** Pesan alasan login ditolak, atau null jika data mitra sudah diverifikasi. */
    public function loginBlockedMessage(): ?string
    {
        return match ($this->verification_status) {
            'verified' => null,
            'rejected' => __('ui.partner_login_rejected', ['note' => $this->verification_note ?? '-']),
            default    => __('ui.partner_login_pending'),
        };
    }

        /** Batas hari pembatalan gratis dari MOU yang ditandatangani; cadangan: 3 hari. */
    public function freeCancelDays(): int
    {
        $agreement = $this->agreements()->where('status', 'signed')->latest('signed_at')->first();

        return $agreement ? (int) $agreement->free_cancel_days : 3;
    }
}