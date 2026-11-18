<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = ['type', 'label', 'value', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getIconAttribute(): string
    {
        return config("contact_types.{$this->type}.icon", 'fa-solid fa-circle-info');
    }

    public function getTypeNameAttribute(): string
    {
        return config("contact_types.{$this->type}.name", $this->type);
    }

    /** Tautan yang bisa diklik di footer; null kalau tipenya hanya teks (mis. jam operasional). */
    public function getHrefAttribute(): ?string
    {
        $value  = trim((string) $this->value);
        $digits = preg_replace('/\D+/', '', $value);

        return match ($this->type) {
            'email'    => 'mailto:' . $value,
            'phone'    => 'tel:' . (str_starts_with($value, '+') ? '+' : '') . $digits,
            // Nomor berawalan 0 dianggap nomor Indonesia (62)
            'whatsapp' => 'https://wa.me/' . (str_starts_with($digits, '0') ? '62' . substr($digits, 1) : $digits),
            'address'  => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($value),
            default    => null,
        };
    }
}