<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\NotificationResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function sendFCMNotification($deviceToken, $data, $notificationTitle, $notificationBody, $type)
    {
        $factory = $this->notificationFactory();
        $messaging = $factory->createMessaging();
        $title = $notificationTitle;
        $body = $notificationBody;

        $notificationData = [
            'id' => $data ?? null,
            'type' => $type ?? null,
        ];

        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
        ]);

        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'normal',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => 'stock_ticker_update',
                'sound' => 'default',
            ],
        ]);

        $apnsConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'sound' => 'default',
                ],
            ],
        ]);

        $webPushConfig = WebPushConfig::fromArray([
            'headers' => [
                'Urgency' => 'high',
            ],
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ]);

        $message = CloudMessage::fromArray([
            'token' => $deviceToken,
            'notification' => $notification,
            'data' => $notificationData,
            'android' => $androidConfig,
            'apns' => $apnsConfig,
            'webpush' => $webPushConfig,
        ]);

        $messaging->send($message);

        return  [
            'title' => $title,
            'message' => $body,
        ];
    }

    public function makeNotification(array $notification, $user, $type,$round = null)
    {
        return \App\Models\Notification::create([
            'user_id' => $user,
            'round_id' => $round ?? null,
            'type' => $type ?? null,
            'title' => $notification['title'],
            'message' => $notification['message'],
            'is_read' => 0,
        ]);
    }

    public function notificationFactory(){
        return (new Factory)->withServiceAccount(__DIR__ . '/firebase_credentials.json');
    }

}
