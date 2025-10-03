<?php
namespace Triyatna\Broadcasty\Push;

use GuzzleHttp\Client;
use Triyatna\Broadcasty\Contracts\PushBridge;

class FcmBridge implements PushBridge
{
    protected Client $http;
    protected ?string $key;

    public function __construct()
    {
        $this->http = new Client(['timeout'=>2.0, 'http_errors'=>false]);
        $this->key = config('broadcasty.push.fcm.server_key');
    }

    public function send(array $subscription, array $payload): array
    {
        if (!$this->key) return ['success'=>false, 'reason'=>'missing_key'];
        $token = $subscription['token'] ?? null;
        if (!$token) return ['success'=>false, 'reason'=>'missing_token'];

        $resp = $this->http->post('https://fcm.googleapis.com/fcm/send', [
            'headers' => ['Authorization' => 'key '.$this->key, 'Content-Type' => 'application/json'],
            'json' => ['to'=>$token, 'data'=>$payload, 'notification'=>[ 'title'=>$payload['title'] ?? 'Notification', 'body'=>$payload['body'] ?? '' ]]
        ]);
        return ['success' => $resp->getStatusCode()<300, 'reason' => (string)$resp->getStatusCode()];
    }
}