# Broadcasty-Lary

![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)
![License](https://img.shields.io/badge/license-MIT-green.svg)

<p align="center">
  <a href="https://github.com/triyatna/broadcasty-lary">
    <img src="https://raw.githubusercontent.com/triyatna/triyatna.github.io/refs/heads/main/assets/broadcast-lary.png" alt="Broadcasty-Lary" width="200" height="200">
  </a>
</p>

Feature-rich, secure, multi-tenant realtime broadcasting for **Laravel 12**.  
Transports: **Auto** (Reverb/Pusher/Ably/Redis), **SSE** endpoint included.  
Use cases: realtime data, notifications, presence, replay, RBAC, and push notifications (Web Push, FCM, APNs, OneSignal).  
Frontend: framework-agnostic **TypeScript SDK** with auto WS/SSE, reconnect, backpressure, offline queue, and request/response.

- Minimal comments, English code
- Defaults safe and lightweight
- Zero-config Tailwind friendly (UI you build)

---

## Table of Contents

1. [Features](#features)
2. [Why Broadcasty-Lary?](#why-broadcasty-lary)
3. [Requirements](#requirements)
4. [Architecture Overview](#architecture-overview)
5. [Installation](#installation)
6. [Quick Start](#quick-start)
7. [Configuration](#configuration)
8. [.env Reference](#env-reference)
9. [Laravel Integration](#laravel-integration)
10. [JS SDK (TypeScript)](#js-sdk-typescript)
11. [Realtime: Publish/Subscribe](#realtime-publishsubscribe)
12. [Presence](#presence)
13. [RBAC Channel Policy](#rbac-channel-policy)
14. [Replay API](#replay-api)
15. [Push Notifications](#push-notifications)
16. [Automatic Drivers & Failover](#automatic-drivers--failover)
17. [Observability & Admin](#observability--admin)
18. [Security Hardening](#security-hardening)
19. [Multi-Tenant Model](#multi-tenant-model)
20. [Request/Response Pattern](#requestresponse-pattern)
21. [Offline Queue & Backpressure](#offline-queue--backpressure)
22. [Rate Limiting & Throttling](#rate-limiting--throttling)
23. [Disaster Readiness](#disaster-readiness)
24. [Testing & Benchmarks](#testing--benchmarks)
25. [Troubleshooting](#troubleshooting)
26. [FAQ](#faq)
27. [Contributors](#contributors)
28. [License](#license)

---

## Features

- **Transports**
  - Auto driver selection: **Reverb → Pusher → Ably → Redis** (configurable)
  - **SSE** streaming endpoint (works under PHP-FPM/Octane)
- **Realtime Messaging**
  - Ordered events per **partition** with idempotency
  - **Replay API**: read from sequence offsets with retention policies
  - **Presence** with separate keyspace and TTL
- **Security**
  - **JWT** verification with required claims (`sub`, `tid`, `roles`, `iat`, `exp`)
  - Nonce/timestamp anti-replay, payload caps, CORS allowlist, TLS stance
- **RBAC Channel Policy**
  - Pluggable resolver, defaults for `private-*`, `presence-*`, publish roles
  - Wildcard/topic ready
- **Push Notifications**
  - **Web Push (VAPID)**, **FCM**, **APNs**, **OneSignal**
- **Observability**
  - **Prometheus** metrics endpoint, **OpenTelemetry** hooks
  - Audit log table, structured logging
- **Admin Toolkit Hooks**
  - Channel explorer, presence viewer, replay debugger, audit viewer (UI up to you)
- **SDK (TypeScript)**
  - Auto **WS/SSE**, reconnect with jitter, **backpressure** modes, **offline queue**
  - Request/Response (correlationId + timeouts)
- **Multi-Tenant First-Class**
  - Tenant isolation prefixes, required token claim
- **Disaster-Ready**
  - Auto failover across drivers, Redis replay fallback, rotation commands

---

## Why Broadcasty-Lary?

- **Drop-in** for Laravel 12 with safe defaults
- **Reusable** across Blade, Inertia (Vue/React), Livewire
- **Secure**: JWT + replay protection + RBAC + caps + CORS allowlist
- **Operationally sane**: metrics, tracing hooks, audit logs
- **No complex infra required**: SSE path runs with PHP-FPM; WS via Reverb/Pusher/Ably if desired

---

## Requirements

- PHP **8.2+**, Laravel **12**
- Redis (recommended; presence/replay rely on it even if using Ably/Pusher/Reverb)
- OpenSSL (TLS/JWT)
- Node **18+** to build the JS SDK (optional if consuming prebuilt)

---

## Architecture Overview

- **Package** provides:
  - Routes: `/broadcasty/sse`, `/broadcasty/publish`, `/broadcasty/replay`, `/broadcasty/presence/*`
  - Drivers: Redis, Pusher-compatible (incl. Reverb), Ably, Null
  - Security middlewares and policy resolver
  - Replay store and presence store
  - Push bridges (Web Push/FCM/APNs/OneSignal)
  - Console: installer, key rotation, serve info
- **Client SDK** abstracts networking and resilience (reconnect, backpressure, offline)

---

## Installation

```bash
composer require triyatna/broadcasty-lary
php artisan vendor:publish --tag=broadcasty-config
php artisan migrate
php artisan broadcasty:install

```

The installer:

- Publishes config
- Runs migrations
- Ensures `.env` exists and appends essential keys (idempotent; `--force` to overwrite appended keys)

## Quick Start

Issue a JWT on login, then use the JS SDK in any frontend.

See `resources/js-sdk` for a framework-agnostic TypeScript SDK with auto WS/SSE, backpressure, reconnect, offline queue.

---

## Configuration

File: `config/broadcasty.php`

Key areas:

- `default_driver`: `auto | redis | ably | pusher | reverb | null`
- `auto.order`: resolution order for auto mode
- `handshake`: JWT public keys, allowed algs, skew, required claims, nonce TTL
- `rbac.resolver`: policy resolver class
- `replay`: store (`redis|database`), retention, partitions
- `presence`: TTL, separate key prefix
- `push`: webpush/fcm/apns/onesignal toggles and credentials
- `limits`: payload caps and rates
- `security`: TLS stance, HMAC, timestamp tolerance, origins
- `observability`: Prometheus path, OpenTelemetry flag
- `tenancy`: required header/claim names, prefixing

---

## .env Reference

Minimal:

```env
BROADCASTY_DRIVER=auto
BROADCASTY_REDIS_CONN=default
BROADCASTY_REDIS_PREFIX=bcy:
BROADCASTY_AUTO_ORDER=reverb,pusher,ably,redis
BROADCASTY_ALLOWED_ORIGINS=*
BROADCASTY_PROM=true
BROADCASTY_JWT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\nREPLACE_ME\n-----END PUBLIC KEY-----"
```

Optional providers:

```env
# Reverb (Pusher protocol)
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=reverb.yourapp.tld
REVERB_PORT=443
REVERB_SCHEME=https

# Pusher
PUSHER_APP_ID=...
PUSHER_APP_KEY=...
PUSHER_APP_SECRET=...
PUSHER_HOST=api-pusher.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https

# Ably
ABLY_KEY=key:secret
```

Push bridges:

```env
# Web Push
BROADCASTY_WEBPUSH=true
WEBPUSH_VAPID_SUBJECT=mailto:you@example.com
WEBPUSH_VAPID_PUBLIC_KEY=...
WEBPUSH_VAPID_PRIVATE_KEY=...

# FCM
BROADCASTY_FCM=false
FCM_SERVER_KEY=...

# APNs (provider token JWT required; wire in ApnsBridge)
BROADCASTY_APNS=false
APNS_KEY_ID=...
APNS_TEAM_ID=...
APNS_P8_PATH=/secure/AuthKey_XXXX.p8
APNS_BUNDLE_ID=com.your.app
APNS_SANDBOX=false

# OneSignal
BROADCASTY_ONESIGNAL=false
ONESIGNAL_APP_ID=...
ONESIGNAL_API_KEY=...
```

---

## Laravel Integration

### Issue JWT for the client

```php
// routes/web.php
use Triyatna\Broadcasty\Security\Jwt;

Route::post('/auth/bcy-token', function () {
    $u = request()->user();
    $jwt = app(Jwt::class)->issue([
        'sub'   => (string)$u->id,
        'tid'   => (string)($u->tenant_id ?? 'default'),
        'roles' => $u->roles()->pluck('name')->all(),
        'jti'   => (string) \Str::uuid(),
    ], 900);
    return ['token' => $jwt];
})->middleware('auth');
```

Store this token in your SPA (e.g., `localStorage.setItem('bcy_jwt', token)`).

---

## JS SDK (TypeScript)

Folder: `resources/js-sdk/`

Build:

```bash
cd resources/js-sdk
npm i
npm run build
# Optional: npm publish --access public
```

Consume:

```ts
import BroadcastyClient from "@triyatna/broadcasty-js";
// or from copied source: '@/vendor/broadcasty/index';

export const bcy = new BroadcastyClient({
  baseUrl: import.meta.env.VITE_APP_URL,
  getToken: () => localStorage.getItem("bcy_jwt")!,
  transport: "auto",
  backpressure: "queue",
});
```

---

## Realtime: Publish/Subscribe

### Subscribe (with replay and reconnect)

```ts
const sub = await bcy.subscribe({
  channel: "private-notify:user:42",
  fromSequence: 0,
  onMessage: (raw) => {
    const msg = typeof raw === "string" ? JSON.parse(raw) : raw;
    // update UI
  },
});
// later: sub.close()
```

### Publish (ordered + idempotent)

```ts
await bcy.publish({
  channel: "private-notify:user:42",
  payload: { type: "status", orderId: 123, state: "PAID" },
  meta: { schema: "order.v1" },
  timeoutMs: 8000,
});
```

Server endpoint: `POST /broadcasty/publish`  
SSE stream: `GET /broadcasty/sse?channel=...`  
Replay: `GET /broadcasty/replay?channel=...&partition=0&from=0`

---

## Presence

Join/Leave:

```bash
# Join
curl -X POST /broadcasty/presence/join \
 -H "Authorization: Bearer <JWT>" -H "Content-Type: application/json" \
 -d '{"channel":"presence-room:global","member":{"id":"42","name":"TY"}}'

# Leave
curl -X POST /broadcasty/presence/leave \
 -H "Authorization: Bearer <JWT>" -H "Content-Type: application/json" \
 -d '{"channel":"presence-room:global","id":"42"}'
```

Presence keys are kept separate with TTL, avoiding interference with replay keys.

---

## RBAC Channel Policy

Default rules:

- `private-*`, `presence-*`: require `member` role to subscribe
- Publishing requires `admin` or `publisher` role
- Public channels are open to subscribe

Swap resolver:

```php
// config/broadcasty.php
'rbac' => [
  'resolver' => \App\Broadcast\MyPolicyResolver::class,
],
```

Implement:

```php
use Triyatna\Broadcasty\Contracts\PolicyResolver;

class MyPolicyResolver implements PolicyResolver {
  public function authorize(string $tenant, string $channel, string $userId, array $roles, string $action): bool {
    // your rules
    return true;
  }
  public function partitions(string $tenant, string $channel): int {
    return str_starts_with($channel,'highload-') ? 8 : 1;
  }
}
```

---

## Replay API

Catch up from sequence offsets:

```
GET /broadcasty/replay?channel=private-notify:user:42&partition=0&from=0
Authorization: Bearer <JWT>
```

Configure retention/partitioning in `config/broadcasty.php`.

---

## Push Notifications

### Web Push (browser)

Service worker (`public/sw.js`):

```js
self.addEventListener("push", (e) => {
  const data = e.data?.json() || {};
  e.waitUntil(
    self.registration.showNotification(data.title || "Notification", {
      body: data.body || "",
      icon: "/icon.png",
      data,
    })
  );
});
self.addEventListener("notificationclick", (e) => {
  e.notification.close();
  const url = e.notification.data?.url || "/";
  e.waitUntil(clients.openWindow(url));
});
```

Subscribe from SPA:

```ts
async function enableWebPush() {
  const reg = await navigator.serviceWorker.register("/sw.js");
  const sub = await reg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: "<VAPID_PUB_BASE64URL>",
  });
  await fetch("/push/subscribe", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": (window as any).csrf,
    },
    body: JSON.stringify(sub),
  });
}
```

Store subscription & send:

```php
use Triyatna\Broadcasty\Push\WebPushBridge;

Route::post('/push/subscribe', function () {
    request()->validate(['endpoint'=>'required','keys'=>'required|array']);
    \DB::table('user_push_subs')->updateOrInsert(
        ['user_id'=>auth()->id(), 'tenant_id'=>auth()->user()->tenant_id, 'endpoint'=>request('endpoint')],
        ['payload'=>json_encode(request()->only('endpoint','keys'))]
    );
    return ['ok'=>true];
})->middleware('auth');

Route::post('/push/send', function () {
    $subs = \DB::table('user_push_subs')->where('user_id', request('user_id'))->get();
    $bridge = app(\Triyatna\Broadcasty\Push\WebPushBridge::class);
    foreach ($subs as $s) {
        $bridge->send(json_decode($s->payload, true), [
            'title' => 'Update',
            'body'  => 'Order #123 is PAID',
            'url'   => url('/orders/123')
        ]);
    }
    return ['ok'=>true];
})->middleware('auth');
```

### FCM (Android/web), APNs (iOS), OneSignal

```php
// FCM
app(\Triyatna\Broadcasty\Push\FcmBridge::class)->send(
  ['token' => $fcmToken],
  ['title'=>'Hello','body'=>'FCM works','data'=>['foo'=>'bar']]
);

// APNs (implement Apple provider JWT and set headers inside bridge)
app(\Triyatna\Broadcasty\Push\ApnsBridge::class)->send(
  ['deviceToken' => $apnsDeviceToken],
  ['title'=>'Hello','body'=>'APNs works','url'=>url('/')]
);

// OneSignal
app(\Triyatna\Broadcasty\Push\OneSignalBridge::class)->send(
  ['player_id' => $playerId],
  ['title'=>'Hello','body'=>'OneSignal works','url'=>url('/')]
);
```

---

## Automatic Drivers & Failover

- `BROADCASTY_DRIVER=auto`
- Order: `BROADCASTY_AUTO_ORDER=reverb,pusher,ably,redis`
- Health probes with a short circuit breaker choose the first healthy driver
- Change driver by editing `.env`, no code changes

---

## Observability & Admin

- **Prometheus**: `GET /broadcasty/metrics`
- **OpenTelemetry**: enable in config; wire your exporter
- **Audit logs**: `broadcasty_audit_logs` table
- **Admin Toolkit**: UI not bundled; endpoints and data are ready for your Blade/Inertia pages

---

## Security Hardening

- Enforce TLS/WSS at the proxy/load balancer
- Required JWT claims (`sub`, `tid`, `roles`, `iat`, `exp`) with clock skew checks
- Nonce anti-replay
- Payload caps and CORS allowlist
- Rate limiting per IP/user/channel
- Secret rotation: `php artisan broadcasty:key:rotate`

---

## Multi-Tenant Model

- Token must include `tid` (tenant id)
- Keys in Redis and replay store are prefixed per tenant
- Policy resolver receives the tenant to scope permissions

---

## Request/Response Pattern

- Include `correlationId` in publish
- Use a `replyTo` channel convention to return responses
- Client sets timeouts; replays handle missed responses on reconnect

```ts
const cid = crypto.randomUUID();
await bcy.publish({
  channel: "rpc:inventory",
  payload: { op: "get", sku: "SKU-1", replyTo: "rpc:client:42" },
  correlationId: cid,
  timeoutMs: 8000,
});
```

---

## Offline Queue & Backpressure

- **Offline**: SDK stores failed publishes to localStorage/IndexedDB and retries via `flushOffline()`
- **Backpressure**: modes `drop | queue | slow-start` to protect the UI

---

## Rate Limiting & Throttling

- Per IP/user/channel throttling in middleware
- Adjust `limits` in config for your traffic patterns

---

## Disaster Readiness

- Auto driver failover with circuit break
- Redis replay fallback
- Retention and compaction knobs
- Rotation commands for secrets/nonces

---

## Testing & Benchmarks

- PHPUnit smoke test included (Orchestra Testbench)
- Add your benchmarking by calling publish in loops and scraping metrics at `/broadcasty/metrics`

Run tests:

```bash
vendor/bin/phpunit
```

---

## Troubleshooting

- **401 Unauthorized**: missing/invalid JWT, skew exceeded, or replay detected
- **403 Forbidden**: RBAC denies action; adjust roles or policy resolver
- **429 Too Many Requests**: rate limits hit; tune `limits` in config
- **No messages**: verify active driver credentials; check `/broadcasty/metrics`; ensure Redis reachable for presence/replay
- **APNs**: implement provider token (JWT) and set headers in `ApnsBridge` to pass Apple auth

---

## FAQ

**Q: Do I need WebSockets?**  
A: Not necessarily. SSE covers most realtime feeds and is simple to host with PHP-FPM. If you need WS, use Reverb/Pusher/Ably—auto mode will pick it up.

**Q: Can I use this with Livewire or Blade only?**  
A: Yes. Use the SDK in a `<script type="module">` or Alpine component.

**Q: How do I change partitioning?**  
A: Override `PolicyResolver::partitions()` per channel.

**Q: Is push notification mandatory?**  
A: No. Bridges are optional; enable and configure only what you need.

---

## Contributors

- **Triyatna** — creator & maintainer
- Community PRs are welcome. Please open issues with clear repro steps and propose focused PRs. Follow PSR-12/Laravel conventions. Add tests when changing core flows (publish/subscribe/replay/security).

---

## 📄 License

This package is released under the [MIT License](LICENSE).

---
