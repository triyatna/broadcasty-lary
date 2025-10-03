<?php
namespace Triyatna\Broadcasty\Drivers;

use Triyatna\Broadcasty\Contracts\TransportDriver;
use Triyatna\Broadcasty\Support\Envelope;

class NullDriver implements TransportDriver {
    public function publish(Envelope $envelope): void {}
    public function subscribe(string $tenant, string $channel, callable $onMessage): void {}
    public function presenceJoin(string $tenant, string $channel, array $member): void {}
    public function presenceLeave(string $tenant, string $channel, string $memberId): void {}
}