<?php
use Orchestra\Testbench\TestCase;
use Triyatna\Broadcasty\BroadcastyServiceProvider;

class SmokeTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [BroadcastyServiceProvider::class];
    }

    public function test_config_loaded()
    {
        $this->assertNotEmpty(config('broadcasty'));
    }
}