<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification
{
    /** @param array<string, scalar> $params nilai yang sudah siap tampil, disimpan apa adanya */
    public function __construct(
        public string $event,
        public array $params,
        public string $url,
        public string $icon = 'fa-bell',
        public bool $mail = true,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->mail ? ['database', 'mail'] : ['database'];
    }

    /** Disimpan: kejadian dan parameternya, bukan teks, supaya tampil sesuai bahasa pembacanya. */
    public function toArray(object $notifiable): array
    {
        return ['event' => $this->event, 'params' => $this->params, 'url' => $this->url, 'icon' => $this->icon];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__("notif.{$this->event}.title", $this->params))
            ->greeting(__('notif.greeting'))
            ->line(__("notif.{$this->event}.body", $this->params))
            ->action(__('notif.open'), $this->url)
            ->line(__('notif.footer'));
    }
}