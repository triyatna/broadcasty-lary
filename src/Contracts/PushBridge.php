<?php
namespace Triyatna\Broadcasty\Contracts;

interface PushBridge {
    public function send(array $subscription, array $payload): array;
}