<?php
namespace Triyatna\Broadcasty\Contracts;

use Triyatna\Broadcasty\Support\Envelope;

interface ReplayStore {
    public function append(string $tenant, string $channel, int $partition, int $sequence, Envelope $event): void;
    public function read(string $tenant, string $channel, int $partition, int $fromSequence, int $limit = 100): array;
    public function compact(string $tenant, string $channel): int;
}