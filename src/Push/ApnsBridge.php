<?php
namespace Triyatna\Broadcasty\Push;

use GuzzleHttp\Client;
use Triyatna\Broadcasty\Contracts\PushBridge;

class ApnsBridge implements PushBridge
{
    protected Client $http;
    protected array $cfg;

    public function __construct()
    {
        $this->http = new Client(['timeout'=>2.0, 'http_errors'=>false]);
        $this->cfg = [
            'key_id' => config('broadcasty.push.apns.key_id'),
            'team_id'=> config('broadcasty.push.apns.team_id'),
            'p8_path'=> config('broadcasty.push.apns.p8_path'),
            'bundle' => config('broadcasty.push.apns.bundle_id'),
            'sandbox'=> (bool) config('broadcasty.push.apns.sandbox', false)
        ];
    }

    public function send(array $subscription, array $payload): array
    {
        $device = $subscription['deviceToken'] ?? null;
        if (!$device) return ['success'=>false, 'reason'=>'missing_device_token'];
        $host = $this->cfg['sandbox'] ? 'https://api.sandbox.push.apple.com' : 'https://api.push.apple.com';
        // Note: For brevity, token generation for APNs is omitted; integrate your JWT token for APNs here.
        $resp = $this->http->post($host.'/3/device/'.$device, [
            'headers' => [
                'apns-topic' => $this->cfg['bundle'],
                // 'authorization' => 'bearer '.$apnsJwt, // implement JWT for APNs provider token
                'content-type' => 'application/json'
            ],
            'json' => ['aps'=>['alert'=>['title'=>$payload['title'] ?? 'Notification','body'=>$payload['body'] ?? '']],'data'=>$payload]
        ]);
        return ['success' => $resp->getStatusCode()<300, 'reason' => (string)$resp->getStatusCode()];
    }
}