<?php

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PushNotification extends Notification{
    public function __construct(public string $title, public string $message, public string $type, public array $data = [])
    {
        throw new \Exception('Not implemented');
    }

    public function via(object $notifiable): array
    {
        return [
            FcmChannel::class,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $data = [];

        foreach ($this->data as $key => $value) {
            $data[(string) $key] = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $data['type'] = $this->type;

        return (new FcmMessage(
            notification: new FcmNotification(
                title: $this->title,
                body: $this->message,
            )
        ))
            ->data($data)
            ->custom([
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]);
    }
}