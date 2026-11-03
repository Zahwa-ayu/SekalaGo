<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    protected $fillable = ['photoable_type', 'photoable_id', 'path', 'caption', 'is_cover', 'sort_order'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function photoable() { return $this->morphTo(); }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    protected static function booted(): void
    {
        // Berkas ikut terhapus saat baris foto dihapus
        static::deleting(fn (Photo $photo) => Storage::disk('public')->delete($photo->path));
    }
}