<?php

namespace App\Services;

use App\Models\CompanyUser;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Support\Collection;

class Notifier
{
    /**
     * Kirim ke satu atau banyak pengguna. Kegagalan kirim tidak pernah mengganggu proses utama.
     * $forceEmail: untuk akun yang belum bisa masuk (mitra atau perusahaan yang belum terverifikasi).
     */
    public function to(User|iterable|null $users, string $event, array $params, string $url, string $icon = 'fa-bell', bool $forceEmail = false): void
    {
        $recipients = $users instanceof User ? collect([$users]) : collect($users);

        foreach ($recipients->filter(fn ($user) => $user instanceof User && $user->is_active) as $user) {
            try {
                $notification = new EventNotification($event, $params, $url, $icon, $forceEmail || $user->notify_email);

                $user->notify($notification->locale($user->preferred_locale ?: null));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function admins(): Collection
    {
        return User::where('user_type', 'admin')->where('is_active', true)->get();
    }

    /** Pengguna Finance sebuah perusahaan. */
    public function financeOf(int $companyId): Collection
    {
        return CompanyUser::where('company_id', $companyId)
            ->where('role_in_company', 'admin_finance')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();
    }
}