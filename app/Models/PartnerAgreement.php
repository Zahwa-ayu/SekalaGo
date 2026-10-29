<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAgreement extends Model
{
    protected $fillable = [
        'partner_id', 'version', 'commission_rate', 'payout_schedule', 'free_cancel_days',
        'content', 'status', 'sent_at', 'signed_at', 'signed_name', 'signed_ip',
        'signature_hash', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'float',
            'sent_at'         => 'datetime',
            'signed_at'       => 'datetime',
        ];
    }

    public function partner() { return $this->belongsTo(Partner::class); }

    /** Komisi sebagai persen tanpa nol di belakang, contoh: "15" atau "12.5". */
    public function commissionPercent(): string
    {
        return rtrim(rtrim(number_format($this->commission_rate * 100, 2, '.', ''), '0'), '.');
    }

    /** Contoh: MOU-0003-v1 (sama dengan nomor di isi dokumen). */
    public function documentNumber(): string
    {
        return 'MOU-' . str_pad((string) $this->partner_id, 4, '0', STR_PAD_LEFT) . '-v' . $this->version;
    }

    /** Sidik jari yang seharusnya, dihitung dari isi dokumen dan data penandatangan. */
    public function expectedHash(): ?string
    {
        if ($this->status !== 'signed' || ! $this->signed_at) {
            return null;
        }

        return hash('sha256', implode('|', [
            $this->content,
            $this->signed_name,
            $this->signed_at->toIso8601String(),
            $this->signed_ip,
        ]));
    }

    /** true = utuh, false = isi berubah setelah ditandatangani, null = belum ada sidik jari. */
    public function hashIsValid(): ?bool
    {
        if (! $this->signature_hash) {
            return null;
        }

        return hash_equals($this->signature_hash, (string) $this->expectedHash());
    }
}