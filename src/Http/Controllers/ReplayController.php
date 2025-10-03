<?php
namespace Triyatna\Broadcasty\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Triyatna\Broadcasty\Contracts\ReplayStore;

class ReplayController extends Controller
{
    public function __construct(protected ReplayStore $replay) {}

    public function read(Request $r)
    {
        $tenant = $r->attributes->get('bcy.tid');
        $channel = (string) $r->query('channel');
        $from = (int) $r->query('from', 0);
        $partition = (int) $r->query('partition', 0);
        return $this->replay->read($tenant, $channel, $partition, $from, 200);
    }
}