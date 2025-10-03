<?php
namespace Triyatna\Broadcasty\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Triyatna\Broadcasty\Security\Jwt;
use Triyatna\Broadcasty\Security\NonceStore;

class VerifyJwt
{
    public function __construct(protected Jwt $jwt, protected NonceStore $nonce) {}

    public function handle(Request $req, Closure $next)
    {
        $token = $req->bearerToken();
        abort_unless($token, 401, 'Missing token');
        $claims = $this->jwt->verify($token);
        $now = time();
        abort_if(abs(($claims['iat'] ?? 0) - $now) > config('broadcasty.handshake.max_skew_sec', 15), 401, 'Clock skew');
        abort_if($this->nonce->seen($claims['jti'] ?? null, config('broadcasty.handshake.nonce_ttl')), 401, 'Replay');
        $req->attributes->add(['bcy.tid' => $claims['tid'] ?? 'default', 'bcy.sub' => $claims['sub'] ?? 'anon', 'bcy.roles' => $claims['roles'] ?? []]);
        return $next($req);
    }
}