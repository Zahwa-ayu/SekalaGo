<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\Searchable;

class Partner extends Model
{
    use LogsActivity;
    use Searchable;
    
    protected $fillable = [
        'user_id', 'company_name', 'business_license_no', 'bank_account_no',
        'verification_status', 'verification_note', 'bank_name', 'bank_account_name',
        'proposed_commission_rate', 'commission_proposal_note', 'commission_proposed_at',
        'legal_form', 'business_type', 'tax_id', 'address', 'city_id', 'phone', 'website', 'contact_name',
        'tax_document_path', 'license_document_path', 'supporting_document_path',
        'source', 'created_by',
    ];

     protected function casts(): array
    {
        return [
            'proposed_commission_rate' => 'float',
            'commission_proposed_at'   => 'datetime',
        ];
    }

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

    // log
    protected function logAttributes(): array
    {
        return ['company_name', 'business_license_no', 'verification_status', 'verification_note'];
    }

    public function logLabel(): string { return $this->company_name; }

    public function vehicles() { return $this->hasMany(Vehicle::class); }

    /** Rekening pencairan lengkap: bank, nomor, dan nama pemilik. */
    public function hasBankDetails(): bool
    {
        return filled($this->bank_name) && filled($this->bank_account_no) && filled($this->bank_account_name);
    }

    public function bankLabel(): string
    {
        return trim($this->bank_name . ' ' . $this->bank_account_no
            . ($this->bank_account_name ? ' a.n. ' . $this->bank_account_name : ''));
    }

    /** Usulan komisi mitra dalam persen (misalnya 12.5), atau null jika tidak mengusulkan. */
    public function proposedPercent(): ?float
    {
        return $this->proposed_commission_rate === null
            ? null
            : round($this->proposed_commission_rate * 100, 2);
    }

    /**
     * Kolom usulan dari isian form profil. Terkunci setelah MOU ditandatangani;
     * waktu usulan hanya diperbarui jika angkanya benar-benar berubah.
     */
    public function proposalAttributes(array $data): array
    {
        if ($this->hasSignedAgreement() || ! array_key_exists('proposed_commission', $data)) {
            return [];
        }

        $rate = filled($data['proposed_commission']) ? round($data['proposed_commission'] / 100, 4) : null;
        $old  = $this->proposed_commission_rate === null ? null : round((float) $this->proposed_commission_rate, 4);

        return [
            'proposed_commission_rate' => $rate,
            'commission_proposal_note' => $rate === null ? null : ($data['commission_proposal_note'] ?? null),
            'commission_proposed_at'   => $rate === $old ? $this->commission_proposed_at : ($rate === null ? null : now()),
        ];
    }

    public function city() { return $this->belongsTo(City::class); }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

        /** Denda pembatalan setelah batas gratis (persen) dari MOU yang ditandatangani; cadangan: angka bawaan di config. */
    public function lateCancelFeePercent(): int
    {
        $agreement = $this->agreements()->where('status', 'signed')->latest('signed_at')->first();

        return (int) ($agreement->late_cancel_fee_percent ?? config('sekalago.late_cancel_fee_percent', 50));
    }

        /** Mitra lama tanpa jenis usaha dianggap mencakup keduanya. */
    public function offersHotels(): bool
    {
        return in_array($this->business_type, [null, '', 'hotel', 'both'], true);
    }

    public function offersCars(): bool
    {
        return in_array($this->business_type, [null, '', 'car', 'both'], true);
    }
}