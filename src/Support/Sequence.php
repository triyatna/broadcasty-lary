<?php
namespace Triyatna\Broadcasty\Support;

use Illuminate\Support\Facades\Redis;

class Sequence {
    public static function next(string $tenant, string $channel, int $partitions): array {
        $p = abs(crc32($tenant.'|'.$channel)) % max(1, $partitions);
        $key = 'bcy:seq:'.$tenant.':'.$channel.':'.$p;
        $seq = (int) Redis::incr($key);
        return [$p, $seq];
    }
}