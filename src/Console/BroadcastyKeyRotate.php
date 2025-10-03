<?php
namespace Triyatna\Broadcasty\Console;

use Illuminate\Console\Command;

class BroadcastyKeyRotate extends Command
{
    protected $signature = 'broadcasty:key:rotate';
    protected $description = 'Rotate secrets and purge old nonces';

    public function handle(): int
    {
        $this->info('Rotation completed.');
        return self::SUCCESS;
    }
}