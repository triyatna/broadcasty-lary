<?php
namespace Triyatna\Broadcasty\Contracts;

interface PolicyResolver {
    public function authorize(string $tenant, string $channel, string $userId, array $roles, string $action): bool;
    public function partitions(string $tenant, string $channel): int;
}