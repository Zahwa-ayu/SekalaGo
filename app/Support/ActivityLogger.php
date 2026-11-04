<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ActivityLogger
{
    /**
     * Catat satu kejadian. Kegagalan mencatat tidak boleh menggagalkan aksi utama.
     */
    public static function record(Model $subject, string $event, array $old = [], array $new = []): void
    {
        try {
            $user  = auth()->user();
            $scope = method_exists($subject, 'logScope') ? ($subject->logScope() ?? $subject) : $subject;

            ActivityLog::create([
                'subject_type'  => $subject->getMorphClass(),
                'subject_id'    => $subject->getKey(),
                'subject_label' => method_exists($subject, 'logLabel')
                    ? $subject->logLabel()
                    : class_basename($subject) . ' #' . $subject->getKey(),
                'scope_type'    => $scope->getMorphClass(),
                'scope_id'      => $scope->getKey(),
                'causer_id'     => $user?->id,
                'causer_label'  => $user?->email,
                'causer_role'   => $user?->user_type,
                'event'         => $event,
                'properties'    => ($old || $new) ? ['old' => $old, 'new' => $new] : null,
                'ip'            => request()->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}