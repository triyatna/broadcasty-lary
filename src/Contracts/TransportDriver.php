<?php
namespace Triyatna\Broadcasty\Contracts;

use Triyatna\Broadcasty\Support\Envelope;

interface TransportDriver {
    public function publish(Envelope $envelope): void;
    public function subscribe(string $tenant, string $channel, callable $onMessage): void;
    public function presenceJoin(string $tenant, string $channel, array $member): void;
    public function presenceLeave(string $tenant, string $channel, string $memberId): void;
}