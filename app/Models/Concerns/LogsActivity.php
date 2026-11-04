<?php

namespace App\Models\Concerns;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Mencatat perubahan model secara otomatis. Model wajib menentukan atribut yang dicatat
 * lewat logAttributes() (jangan masukkan password atau nomor rekening).
 */
trait LogsActivity
{
    abstract protected function logAttributes(): array;

    /** Kejadian yang dicatat otomatis: created, updated, deleted. */
    protected function logEvents(): array
    {
        return ['created', 'updated', 'deleted'];
    }

    public function logLabel(): string
    {
        return class_basename($this) . ' #' . $this->getKey();
    }

    /** Induk untuk linimasa (misalnya hotel untuk tipe kamar). null = dirinya sendiri. */
    public function logScope(): ?Model
    {
        return null;
    }

    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if (in_array('created', $model->logEvents(), true)) {
                ActivityLogger::record($model, 'created', [], Arr::only($model->getAttributes(), $model->logAttributes()));
            }
        });

        static::updated(function ($model) {
            if (! in_array('updated', $model->logEvents(), true)) {
                return;
            }

            $changes = Arr::only($model->getChanges(), $model->logAttributes());

            if ($changes === []) {
                return;
            }

            ActivityLogger::record($model, 'updated', Arr::only($model->getOriginal(), array_keys($changes)), $changes);
        });

        // "deleting" (bukan "deleted") supaya nama dan relasinya masih bisa dibaca
        static::deleting(function ($model) {
            if (in_array('deleted', $model->logEvents(), true)) {
                ActivityLogger::record($model, 'deleted', Arr::only($model->getAttributes(), $model->logAttributes()));
            }
        });
    }
}