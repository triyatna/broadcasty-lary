<?php
namespace Triyatna\Broadcasty\Drivers;

use GuzzleHttp\Client;
use Triyatna\Broadcasty\Contracts\TransportDriver;
use Triyatna\Broadcasty\Support\Envelope;

class AblyDriver implements TransportDriver
{
    protected Client $http;
    protected ?string $key;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 1.0]);
        $this->key = config('broadcasty.drivers.ably.key');
    }

    public function publish(Envelope $envelope): void
    {
        if (!$this->key) return;
        // Minimal publish to Ably REST channel (example endpoint). Real envs may vary.
        $channel = "{$envelope->tenant}:{$envelope->channel}";
        $this->http->post("https://rest.ably.io/channels/".rawurlencode($channel)."/messages", [
            'auth' => explode(':', $this->key, 2),
            'json' => ['name' => 'message', 'data' => $envelope->payload]
        ]);
    }

    public function subscribe(string $tenant, string $channel, callable $onMessage): void {}
    public function presenceJoin(string $tenant, string $channel, array $member): void {}
    public function presenceLeave(string $tenant, string $channel, string $memberId): void {}

    public function health(int $timeoutMs = 800): bool
    {
        return (bool)$this->key;
    }
}