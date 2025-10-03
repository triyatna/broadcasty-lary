<?php
namespace Triyatna\Broadcasty;

use Illuminate\Contracts\Foundation\Application;
use Triyatna\Broadcasty\Contracts\TransportDriver;

class BroadcastyManager
{
    public function __construct(protected Application $app) {}

    public function driver(?string $name = null): TransportDriver
    {
        $name ??= config('broadcasty.default_driver', 'auto');
        return $name === 'auto' ? $this->auto() : $this->resolve($name);
    }

    protected function resolve(string $name): TransportDriver
    {
        return match ($name) {
            'redis'  => $this->app->make(Drivers\RedisDriver::class),
            'ably'   => $this->app->make(Drivers\AblyDriver::class),
            'pusher' => $this->app->make(Drivers\PusherLikeDriver::class),
            'reverb' => $this->app->make(Drivers\PusherLikeDriver::class),
            default  => $this->app->make(Drivers\NullDriver::class),
        };
    }

    protected function auto(): TransportDriver
    {
        $cacheKey = 'bcy:auto:circuit';
        $open = cache()->get($cacheKey);
        if (is_array($open) && ($open['until'] ?? 0) > microtime(true)) {
            return $this->resolve($open['fallback']);
        }

        $order = config('broadcasty.auto.order', ['reverb','pusher','ably','redis']);
        foreach ($order as $candidate) {
            $drv = $this->resolve(trim($candidate));
            if ($this->healthy($drv)) return $drv;
        }

        $fallback = 'redis';
        cache()->put($cacheKey, ['fallback' => $fallback, 'until' => microtime(true) + (config('broadcasty.auto.circuit_break_ms', 10000)/1000)], config('broadcasty.auto.circuit_break_ms', 10000)/1000);
        return $this->resolve($fallback);
    }

    protected function healthy(TransportDriver $drv): bool
    {
        try {
            if (method_exists($drv, 'health')) {
                return (bool) $drv->health(timeoutMs: (int) config('broadcasty.auto.health_timeout_ms', 800));
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function metrics(): string
    {
        return "broadcasty_up 1\n";
    }
}