<?php
namespace Triyatna\Broadcasty\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Triyatna\Broadcasty\Contracts\{TransportDriver, PolicyResolver, ReplayStore};
use Triyatna\Broadcasty\Support\{Envelope, Sequence};
use Ramsey\Uuid\Uuid;

class PublishController extends Controller
{
    public function __construct(
        protected TransportDriver $driver,
        protected ReplayStore $replay,
        protected PolicyResolver $policy
    ) {}

    public function publish(Request $r)
    {
        $tenant = $r->attributes->get('bcy.tid');
        $user = $r->attributes->get('bcy.sub');
        $roles = $r->attributes->get('bcy.roles');

        $channel = (string) $r->input('channel');
        abort_unless($this->policy->authorize($tenant, $channel, $user, $roles, 'publish'), 403);

        $payload = (string) $r->input('payload');
        abort_if(strlen($payload) > config('broadcasty.limits.payload_bytes_max'), 413);

        $meta = (array) $r->input('meta', []);
        $cid = $r->input('correlationId') ?: Uuid::uuid7()->toString();

        $envelope = new Envelope(
            id: Uuid::uuid7()->toString(),
            tenant: $tenant,
            channel: $channel,
            payload: $payload,
            meta: array_merge($meta, ['correlationId'=>$cid, 'publisher'=>$user, 'ts'=>microtime(true)])
        );

        $partitions = max(1, $this->policy->partitions($tenant, $channel));
        [$partition, $sequence] = Sequence::next($tenant, $channel, $partitions);
        $envelope->partition = $partition;
        $envelope->sequence = $sequence;

        $this->replay->append($tenant, $channel, $partition, $sequence, $envelope);
        $this->driver->publish($envelope);

        return response()->json(['ok'=>true, 'id'=>$envelope->id, 'partition'=>$partition, 'sequence'=>$sequence]);
    }
}