<?php
namespace Triyatna\Broadcasty\Policies;

use Triyatna\Broadcasty\Contracts\PolicyResolver;

class DefaultPolicyResolver implements PolicyResolver
{
    public function authorize(string $tenant, string $channel, string $userId, array $roles, string $action): bool
    {
        if (str_starts_with($channel, 'private-')) return in_array('member', $roles, true);
        if (str_starts_with($channel, 'presence-')) return in_array('member', $roles, true);
        if ($action === 'publish') return in_array('admin', $roles, true) || in_array('publisher', $roles, true);
        return true;
    }

    public function partitions(string $tenant, string $channel): int
    {
        return str_starts_with($channel, 'highload-') ? 8 : 1;
    }
}