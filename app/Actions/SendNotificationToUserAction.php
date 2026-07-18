<?php

namespace App\Actions;

use Pusher\PushNotifications\PushNotifications;

class SendNotificationToUserAction
{

    public function handle(int $user_id,string $title,string $body, string $deep_link): void
    {

        $beamsClient = new PushNotifications([
            'instanceId' => config('services.beams.instance_id'),
            'secretKey' => config('services.beams.secret_key'),
        ]);

        $beamsClient->publishToUsers([(string) $user_id], [
            'web' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'deep_link' => $deep_link,
                ],
            ],
        ]);

    }
}