<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the default notification channel that gets used
    | to deliver any notifications that do not have a more specific channel.
    |
    */

    'default' => env('NOTIFICATION_DRIVER', 'mail'),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the available notification channel drivers.
    |
    */

    'channels' => [
        'mail' => Illuminate\Notifications\Channels\MailChannel::class,
        'database' => Illuminate\Notifications\Channels\DatabaseChannel::class,
        'webpush' => NotificationChannels\WebPush\WebPushChannel::class,
    ],
];
