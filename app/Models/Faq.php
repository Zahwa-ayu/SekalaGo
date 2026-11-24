<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Searchable;


class Faq extends Model
{
    use Searchable;
    protected $fillable = ['category', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function translations()
    {
        return $this->hasMany(FaqTranslation::class);
    }

    // Ambil teks sesuai bahasa aktif, fallback ke Indonesia
    public function translation(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'id');
    }
}