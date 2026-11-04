<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'subject_type', 'subject_id', 'subject_label', 'scope_type', 'scope_id',
        'causer_id', 'causer_label', 'causer_role', 'event', 'properties', 'ip',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function subject() { return $this->morphTo(); }
    public function causer()  { return $this->belongsTo(User::class, 'causer_id'); }

    /** Kunci bahasa untuk jenis objek, contoh: ui.log_type_room_type */
    public function typeLabelKey(): string
    {
        return 'ui.log_type_' . Str::snake(class_basename((string) $this->subject_type));
    }
}