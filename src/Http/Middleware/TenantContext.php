<?php
namespace Triyatna\Broadcasty\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantContext
{
    public function handle(Request $req, Closure $next)
    {
        $tid = $req->attributes->get('bcy.tid', 'default');
        app()->instance('broadcasty.tenant', $tid);
        return $next($req);
    }
}