<?php
namespace Triyatna\Broadcasty\Drivers;

use GuzzleHttp\Client;
use Triyatna\Broadcasty\Contracts\TransportDriver;
use Triyatna\Broadcasty\Support\Envelope;

class PusherLikeDriver implements TransportDriver
{
    protected Client $http;
    protected array $cfg;

    public function __construct()
    {
        $this->http = new Client(['timeout'=>1.0, 'http_errors'=>false]);
        $this->cfg = [
            'app_id' => config('broadcasty.drivers.pusher.app_id'),
            'key' => config('broadcasty.drivers.pusher.key'),
            'secret' => config('broadcasty.drivers.pusher.secret'),
            'host' => config('broadcasty.drivers.pusher.host'),
            'port' => config('broadcasty.drivers.pusher.port', 443),
            'scheme' => config('broadcasty.drivers.pusher.scheme', 'https'),
        ];
    }

    public function publish(Envelope $envelope): void
    {
        if (!$this->cfg['key'] || !$this->cfg['secret'] || !$this->cfg['app_id']) return;
        $channel = "{$envelope->tenant}:{$envelope->channel}";
        $url = sprintf('%s://%s:%s/apps/%s/events', $this->cfg['scheme'], $this->cfg['host'], $this->cfg['port'], $this->cfg['app_id']);
        $payload = ['name'=>'message','channels'=>[$channel],'data'=>$envelope->payload];
        $this->http->post($url, ['json'=>$payload, 'headers'=>['Authorization'=>'Bearer '.$this->cfg['key']]]);
    }

    public function subscribe(string $tenant, string $channel, callable $onMessage): void {}
    public function presenceJoin(string $tenant, string $channel, array $member): void {}
    public function presenceLeave(string $tenant, string $channel, string $memberId): void {}

    public function health(int $timeoutMs = 800): bool
    {
        return (bool) ($this->cfg['key'] && $this->cfg['secret'] && $this->cfg['app_id'] && $this->cfg['host']);
    }
}