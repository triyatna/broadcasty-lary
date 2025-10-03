<?php
namespace Triyatna\Broadcasty\Replay;

use Illuminate\Support\Facades\Redis;
use Triyatna\Broadcasty\Contracts\ReplayStore;
use Triyatna\Broadcasty\Support\Envelope;

class RedisReplayStore implements ReplayStore
{
    protected string $p;

    public function __construct()
    {
        $this->p = config('broadcasty.drivers.redis.prefix','bcy:').'rpl:';
    }

    public function append(string $tenant, string $channel, int $partition, int $sequence, Envelope $event): void
    {
        $key = "{$this->p}{$tenant}:{$channel}:{$partition}";
        Redis::hset($key, (string)$sequence, $event->payload);
        Redis::expire($key, config('broadcasty.replay.retention_sec',3600));
    }

    public function read(string $tenant, string $channel, int $partition, int $fromSequence, int $limit = 100): array
    {
        $key = "{$this->p}{$tenant}:{$channel}:{$partition}";
        $all = Redis::hgetall($key);
        $seqs = array_map('intval', array_keys($all));
        sort($seqs, SORT_NUMERIC);
        $out = [];
        foreach ($seqs as $s) {
            if ($s >= $fromSequence) {
                $out[] = ['sequence'=>$s, 'payload'=>$all[(string)$s]];
                if (count($out) >= $limit) break;
            }
        }
        return $out;
    }

    public function compact(string $tenant, string $channel): int { return 0; }
}