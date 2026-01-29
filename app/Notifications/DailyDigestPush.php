<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DailyDigestPush extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $url = '/lancamentos-do-dia'
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)                 // "Lançamentos do dia"
            ->body($this->body)                   // "X entradas, X saídas, X investimentos"
            ->icon('/laravelpwa/icons/icon-192x192.png')
            ->action('Abrir', $this->url)         // opcional: botão de ação (onde suportado)
            ->data(['url' => $this->url]);        // usado pelo sw.js para abrir a tela
    }
}
