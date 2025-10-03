<?php
namespace Triyatna\Broadcasty\Console;

use Illuminate\Console\Command;

class BroadcastyServe extends Command
{
    protected $signature = 'broadcasty:serve {--port=8085}';
    protected $description = 'Start lightweight SSE gateway (Laravel HTTP).';

    public function handle(): int
    {
        $this->info('Broadcasty SSE served via /broadcasty/sse');
        return self::SUCCESS;
    }
}