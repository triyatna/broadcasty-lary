<?php
namespace Triyatna\Broadcasty\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleChannel
{
    public function handle(Request $req, Closure $next)
    {
        $key = 'bcy:rate:'.sha1(($req->attributes->get('bcy.sub') ?? 'anon').':'.($req->input('channel') ?? ''));
        if (RateLimiter::tooManyAttempts($key, 60)) abort(429, 'Too Many Requests');
        RateLimiter::hit($key, 60);
        return $next($req);
    }
}