<?php
namespace Triyatna\Broadcasty\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Triyatna\Broadcasty\Contracts\{TransportDriver, ReplayStore};

class SseController extends Controller
{
    public function __construct(protected TransportDriver $driver, protected ReplayStore $replay) {}

    public function stream(Request $r): StreamedResponse
    {
        $tenant = $r->attributes->get('bcy.tid');
        $channel = (string) $r->query('channel');
        $from = (int) $r->query('from', 0);
        $partition = (int) $r->query('partition', 0);

        return response()->stream(function () use ($tenant, $channel, $from, $partition) {
            echo "retry: 3000\n\n";
            @ob_flush(); @flush();

            foreach ($this->replay->read($tenant, $channel, $partition, $from, 200) as $evt) {
                echo "id: {$evt['sequence']}\n";
                echo "event: replay\n";
                echo "data: {$evt['payload']}\n\n";
                @ob_flush(); @flush();
            }

            $this->driver->subscribe($tenant, $channel, function ($message) {
                echo "event: message\n";
                echo "data: {$message}\n\n";
                @ob_flush(); @flush();
            });
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache', 'X-Accel-Buffering' => 'no']);
    }
}