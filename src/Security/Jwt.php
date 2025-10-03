<?php
namespace Triyatna\Broadcasty\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Ecdsa\Sha256 as ES256;
use Lcobucci\JWT\Signer\Key\InMemory;

class Jwt
{
    protected Configuration $config;

    public function __construct()
    {
        $pub = (string) (config('broadcasty.handshake.jwt_public_keys')[0] ?? '');
        $this->config = Configuration::forAsymmetricSigner(new Sha256(), InMemory::plainText(''), InMemory::plainText($pub));
    }

    public function verify(string $token): array
    {
        $t = $this->config->parser()->parse($token);
        $claims = [];
        foreach ($t->claims()->all() as $k => $v) $claims[$k] = $v;
        return $claims;
    }

    public function issue(array $claims, int $ttlSec = 900): string
    {
        $now = time();
        $b = $this->config->builder();
        foreach ($claims as $k=>$v) $b = $b->withClaim($k, $v);
        $b = $b->issuedAt(new \DateTimeImmutable("@$now"))
               ->expiresAt(new \DateTimeImmutable("@".($now+$ttlSec)))
               ->withClaim('iat',$now)->withClaim('exp',$now+$ttlSec);
        return $b->getToken($this->config->signer(), $this->config->signingKey())->toString();
    }
}