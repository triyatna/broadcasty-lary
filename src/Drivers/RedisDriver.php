<?php
namespace Triyatna\Broadcasty\Drivers;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Triyatna\Broadcasty\Contracts\TransportDriver;
use Triyatna\Broadcasty\Support\Envelope;

class RedisDriver implements TransportDriver
{
    protected Connection $redis;
    protected string $prefix;

    public function __construct()
    {
        $this->redis = Redis::connection(config('broadcasty.drivers.redis.connection'));
        $this->prefix = (string) config('broadcasty.drivers.redis.prefix', 'bcy:');
    }

    public function publish(Envelope $envelope): void
    {
        $chan = $this->prefix."chan:{$envelope->tenant}:{$envelope->channel}";
        $this->redis->publish($chan, $envelope->payload);
    }

    public function subscribe(string $tenant, string $channel, callable $onMessage): void
    {
        $chan = $this->prefix."chan:{$tenant}:{$channel}";
        $this->redis->psubscribe([$chan], function ($message) use ($onMessage) {
            $onMessage($message);
        });
    }

    public function presenceJoin(string $tenant, string $channel, array $member): void
    {
        $key = $this->prefix."prs:{$tenant}:{$channel}";
        $this->redis->hset($key, $member['id'], json_encode($member));
        $this->redis->expire($key, config('broadcasty.presence.ttl',120));
    }

    public function presenceLeave(string $tenant, string $channel, string $memberId): void
    {
        $key = $this->prefix."prs:{$tenant}:{$channel}";
        $this->redis->hdel($key, [$memberId]);
    }

    public function health(int $timeoutMs = 800): bool
    {
        $start = microtime(true);
        $pong = $this->redis->client()->executeRaw(['PING']);
        return strtoupper((string)$pong) === 'PONG' && (microtime(true) - $start) * 1000 <= $timeoutMs;
    }
}