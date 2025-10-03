<?php
namespace Triyatna\Broadcasty\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallBroadcasty extends Command
{
    protected $signature = 'broadcasty:install {--force : Overwrite existing .env keys}';
    protected $description = 'Publish config, run migrations, and scaffold .env for Broadcasty-Lary';

    public function handle(): int
    {
        $this->callSilent('vendor:publish', ['--tag'=>'broadcasty-config', '--force'=>true]);
        $this->call('migrate');

        $env = base_path('.env');
        $fs = new Filesystem();
        if (!$fs->exists($env)) {
            $this->warn('.env not found, creating from .env.example');
            $fs->copy(base_path('.env.example'), $env);
        }

        $pairs = [
            'BROADCASTY_DRIVER' => 'auto',
            'BROADCASTY_REDIS_CONN' => 'default',
            'BROADCASTY_REDIS_PREFIX' => 'bcy:',
            'BROADCASTY_AUTO_ORDER' => 'reverb,pusher,ably,redis',
            'BROADCASTY_PROM' => 'true',
            'BROADCASTY_JWT_PUBLIC_KEY' => '-----BEGIN PUBLIC KEY-----\nREPLACE_ME\n-----END PUBLIC KEY-----',
            'BROADCASTY_WEBPUSH' => 'true',
            'WEBPUSH_VAPID_SUBJECT' => 'mailto:you@example.com',
            'WEBPUSH_VAPID_PUBLIC_KEY' => '',
            'WEBPUSH_VAPID_PRIVATE_KEY' => ''
        ];

        $content = $fs->get($env);
        foreach ($pairs as $k=>$v) {
            $line = $k.'=';
            if (str_contains($content, $line)) {
                if ($this->option('force')) {
                    $content = preg_replace('/^'+k+'=.*/m', $k.'='+$v, $content)
                }
            } else {
                $content .= PHP_EOL.$k.'='.$v;
            }
        }
        $fs->put($env, $content);
        $this->info('Broadcasty-Lary installed.');
        return self::SUCCESS;
    }
}