<?php
namespace Triyatna\Broadcasty\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Triyatna\Broadcasty\Contracts\TransportDriver;

class PresenceController extends Controller
{
    public function __construct(protected TransportDriver $driver) {}

    public function join(Request $r)
    {
        $tenant = $r->attributes->get('bcy.tid');
        $channel = (string) $r->input('channel');
        $member = (array) $r->input('member', []);
        $this->driver->presenceJoin($tenant, $channel, $member);
        return ['ok'=>true];
    }

    public function leave(Request $r)
    {
        $tenant = $r->attributes->get('bcy.tid');
        $channel = (string) $r->input('channel');
        $id = (string) $r->input('id');
        $this->driver->presenceLeave($tenant, $channel, $id);
        return ['ok'=>true];
    }
}