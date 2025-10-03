<?php
namespace Triyatna\Broadcasty\Http\Controllers;

use Illuminate\Routing\Controller;

class HandshakeController extends Controller
{
    public function handle()
    {
        return ['ok'=>true];
    }
}