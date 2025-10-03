<?php

use Illuminate\Support\Facades\Route;
use Triyatna\Broadcasty\Http\Controllers\{
    HandshakeController, SseController, PublishController, PresenceController, ReplayController
};
use Triyatna\Broadcasty\Http\Middleware\{VerifyJwt, ThrottleChannel, TenantContext};

Route::prefix('broadcasty')->middleware([VerifyJwt::class, TenantContext::class])->group(function () {
    Route::post('handshake', [HandshakeController::class, 'handle'])->name('broadcasty.handshake');
    Route::get('sse', [SseController::class, 'stream'])->name('broadcasty.sse');
    Route::post('publish', [PublishController::class, 'publish'])->middleware(ThrottleChannel::class)->name('broadcasty.publish');
    Route::post('presence/join', [PresenceController::class, 'join'])->name('broadcasty.presence.join');
    Route::post('presence/leave', [PresenceController::class, 'leave'])->name('broadcasty.presence.leave');
    Route::get('replay', [ReplayController::class, 'read'])->name('broadcasty.replay');
});