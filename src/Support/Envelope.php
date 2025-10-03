<?php
namespace Triyatna\Broadcasty\Support;

class Envelope {
    public string $id;
    public string $tenant;
    public string $channel;
    public string $payload;
    public array $meta;
    public int $partition = 0;
    public int $sequence = 0;

    public function __construct(string $id, string $tenant, string $channel, string $payload, array $meta = []) {
        $this->id = $id;
        $this->tenant = $tenant;
        $this->channel = $channel;
        $this->payload = $payload;
        $this->meta = $meta;
    }
}