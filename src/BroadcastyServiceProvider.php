<?php
namespace Triyatna\Broadcasty;

use Illuminate\Support\ServiceProvider;
use Triyatna\Broadcasty\Contracts\{TransportDriver, PolicyResolver, ReplayStore};

class BroadcastyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/broadcasty.php', 'broadcasty');
        $this->app->singleton(BroadcastyManager::class, fn($app) => new BroadcastyManager($app));
        $this->app->alias(BroadcastyManager::class, 'broadcasty');

        $this->app->bind(TransportDriver::class, fn($app) => $app->make(BroadcastyManager::class)->driver());
        $this->app->bind(PolicyResolver::class, config('broadcasty.rbac.resolver'));
        $this->app->bind(ReplayStore::class, function ($app) {
            return match (config('broadcasty.replay.store')) {
                'database' => $app->make(\Triyatna\Broadcasty\Replay\DatabaseReplayStore::class),
                default => $app->make(\Triyatna\Broadcasty\Replay\RedisReplayStore::class),
            };
        });
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/broadcasty.php' => config_path('broadcasty.php')], 'broadcasty-config');
        $this->loadRoutesFrom(__DIR__.'/../routes/broadcasty.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if (config('broadcasty.observability.prometheus.enabled')) {
            app('router')->get(config('broadcasty.observability.prometheus.path'), function () {
                return response()->make(app(BroadcastyManager::class)->metrics(), 200, ['Content-Type'=>'text/plain; version=0.0.4']);
            })->name('broadcasty.metrics');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Triyatna\Broadcasty\Console\BroadcastyServe::class,
                \Triyatna\Broadcasty\Console\BroadcastyKeyRotate::class,
                \Triyatna\Broadcasty\Console\InstallBroadcasty::class,
            ]);
        }
    }
}