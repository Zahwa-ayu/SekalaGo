<?php

namespace App\Support;

use Illuminate\Notifications\DatabaseNotification;

class NotificationPresenter
{
    /** Teks dibuat saat ditampilkan, jadi selalu mengikuti bahasa pembaca. */
    public static function present(DatabaseNotification $notification): array
    {
        $data  = $notification->data;
        $event = $data['event'] ?? '';
        $params = $data['params'] ?? [];

        return [
            'id'    => $notification->id,
            'title' => __("notif.{$event}.title", $params),
            'body'  => __("notif.{$event}.body", $params),
            'icon'  => $data['icon'] ?? 'fa-bell',
            'read'  => $notification->read_at !== null,
            'time'  => $notification->created_at->diffForHumans(),
            'open'  => route('notifications.open', $notification->id),
        ];
    }
}