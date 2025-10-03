<?php
namespace Triyatna\Broadcasty\Security;

use Illuminate\Support\Facades\Cache;

class NonceStore {
    public function seen(?string $jti, int $ttl): bool {
        if (!$jti) return false;
        $key = 'bcy:nonce:'.$jti;
        if (Cache::has($key)) return true;
        Cache::put($key, 1, $ttl);
        return false;
    }
}