<?php
namespace Triyatna\Broadcasty\Push;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Triyatna\Broadcasty\Contracts\PushBridge;

class WebPushBridge implements PushBridge
{
    protected WebPush $client;

    public function __construct()
    {
        $this->client = new WebPush([
            'VAPID' => [
                'subject' => config('broadcasty.push.webpush.vapid.subject'),
                'publicKey' => config('broadcasty.push.webpush.vapid.public_key'),
                'privateKey' => config('broadcasty.push.webpush.vapid.private_key'),
            ]
        ]);
    }

    public function send(array $subscription, array $payload): array
    {
        $sub = Subscription::create($subscription);
        $report = $this->client->sendOneNotification($sub, json_encode($payload));
        return ['success' => $report->isSuccess(), 'reason' => $report->getReason()];
    }
}