<?php
// app/Notifications/PushNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PushNotification extends Notification
{
    use Queueable;

    public $title;
    public $body;

    public function __construct(string $title, string $body, string $url = '/lancamentos-do-dia')
    {
        $this->title = $title;
        $this->body  = $body;
        $this->url   = $url;
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon('/laravelpwa/icons/icon-192x192.png')
            ->action('Abrir app', $this->url)
            ->data(['url' => $this->url]);
    }
}
