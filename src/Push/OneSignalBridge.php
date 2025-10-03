<?php
namespace Triyatna\Broadcasty\Push;

use GuzzleHttp\Client;
use Triyatna\Broadcasty\Contracts\PushBridge;

class OneSignalBridge implements PushBridge
{
    protected Client $http;
    protected array $cfg;

    public function __construct()
    {
        $this->http = new Client(['timeout'=>2.0, 'http_errors'=>false]);
        $this->cfg = [
            'app_id' => config('broadcasty.push.onesignal.app_id'),
            'api_key'=> config('broadcasty.push.onesignal.api_key'),
        ];
    }

    public function send(array $subscription, array $payload): array
    {
        if (!$this->cfg['app_id'] || !$this->cfg['api_key']) return ['success'=>false, 'reason'=>'missing_cfg'];
        $playerId = $subscription['player_id'] ?? null;
        if (!$playerId) return ['success'=>false, 'reason'=>'missing_player_id'];

        $resp = $this->http->post('https://api.onesignal.com/notifications', [
            'headers' => ['Authorization' => 'Basic '.$this->cfg['api_key'], 'Content-Type' => 'application/json'],
            'json' => [
                'app_id' => $this->cfg['app_id'],
                'include_player_ids' => [$playerId],
                'headings' => ['en' => $payload['title'] ?? 'Notification'],
                'contents' => ['en' => $payload['body'] ?? ''],
                'data' => $payload
            ]
        ]);
        return ['success' => $resp->getStatusCode()<300, 'reason' => (string)$resp->getStatusCode()];
    }
}