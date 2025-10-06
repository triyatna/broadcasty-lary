# Broadcasty-Lary

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)
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
9. [Environment Variables – Complete Guide](#environment-variables--complete-guide)
10. [Laravel Integration](#laravel-integration)
11. [JS SDK (TypeScript)](#js-sdk-typescript)
12. [Realtime: Publish/Subscribe](#realtime-publishsubscribe)
13. [Presence](#presence)
14. [RBAC Channel Policy](#rbac-channel-policy)
15. [Replay API](#replay-api)
16. [Push Notifications](#push-notifications)
17. [Automatic Drivers & Failover](#automatic-drivers--failover)
18. [Database & Storage Model](#database--storage-model)
19. [Observability & Admin](#observability--admin)
20. [Security Hardening](#security-hardening)
21. [Multi-Tenant Model](#multi-tenant-model)
22. [Request/Response Pattern](#requestresponse-pattern)
23. [Offline Queue & Backpressure](#offline-queue--backpressure)
24. [Rate Limiting & Throttling](#rate-limiting--throttling)
25. [Disaster Readiness](#disaster-readiness)
26. [Testing & Benchmarks](#testing--benchmarks)
27. [Troubleshooting](#troubleshooting)
28. [External Provider Setup & Docs](#external-provider-setup--docs)
29. [FAQ](#faq)
30. [Contributors](#contributors)
31. [License](#license)

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

## Environment Variables – Complete Guide

Set these in your project’s `.env`. Each variable below explains **what it is**, **when you need it**, and **how to obtain or format it**. If a provider is unused, leave its vars empty.

### Core / General

| Key                          | Required    | What it does                                                     | How to set / Notes                                                                                       |
| ---------------------------- | ----------- | ---------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| `BROADCASTY_DRIVER`          | Yes         | Selects transport driver. Use `auto` for detection and failover. | `auto` \| `redis` \| `ably` \| `pusher` \| `reverb` \| `null`. Default: `auto`.                          |
| `BROADCASTY_AUTO_ORDER`      | No          | Driver preference order in `auto` mode.                          | Comma list; default: `reverb,pusher,ably,redis`.                                                         |
| `BROADCASTY_ALLOWED_ORIGINS` | Recommended | CORS allowlist for browsers.                                     | Use exact origins: e.g. `https://app.example.com,https://admin.example.com`. Use `*` only for local/dev. |
| `BROADCASTY_PROM`            | No          | Enables Prometheus metrics route.                                | `true` or `false`. Default `true`.                                                                       |
| `BROADCASTY_JWT_PUBLIC_KEY`  | Yes         | Public key used to verify client JWTs.                           | Paste PEM **public** key (multiline). See “JWT Keys” below.                                              |
| `BROADCASTY_REDIS_CONN`      | Recommended | Laravel Redis connection name.                                   | Matches `config/database.php` `redis.connections`. Usually `default`.                                    |
| `BROADCASTY_REDIS_PREFIX`    | No          | Prefix for Broadcasty keys.                                      | Default `bcy:`. Change if you share Redis across apps.                                                   |

#### JWT Keys (how to generate)

```bash
# RSA 2048 example
openssl genrsa -out jwtRS256.key 2048
openssl rsa -in jwtRS256.key -pubout -out jwtRS256.key.pub
# Put the *public* key content (jwtRS256.key.pub) into BROADCASTY_JWT_PUBLIC_KEY
# Keep the private key in your app/IdP to issue client tokens.
```

Paste public key as a single line in `.env` with `\n` newlines:

```
BROADCASTY_JWT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqh...snip...\n-----END PUBLIC KEY-----"
```

---

### Redis (baseline for presence/replay even if you use a provider)

| Key                             | Required    | Notes                                                                                |
| ------------------------------- | ----------- | ------------------------------------------------------------------------------------ |
| `BROADCASTY_REDIS_CONN=default` | Recommended | Ensure `redis` is configured in `config/database.php` and Redis server is reachable. |
| `BROADCASTY_REDIS_PREFIX=bcy:`  | Recommended | Namespace isolation for keys.                                                        |

> Redis install: Ubuntu `apt install redis-server`; Docker `docker run -p 6379:6379 redis`. Laravel config: <https://laravel.com/docs/12.x/redis>

---

### Laravel Reverb (Pusher protocol)

Use **Reverb** if you want a first-party WebSocket service for Laravel.  
Docs: <https://laravel.com/docs/12.x/reverb>

| Key                 | Required              | What                                                   |
| ------------------- | --------------------- | ------------------------------------------------------ |
| `REVERB_APP_ID`     | Yes (if using Reverb) | Your Reverb app ID.                                    |
| `REVERB_APP_KEY`    | Yes                   | Public key.                                            |
| `REVERB_APP_SECRET` | Yes                   | Secret key.                                            |
| `REVERB_HOST`       | Yes                   | Host of your Reverb server, e.g. `reverb.example.com`. |
| `REVERB_PORT`       | No                    | Usually `443`.                                         |
| `REVERB_SCHEME`     | No                    | `https` for TLS (recommended).                         |

> Set `BROADCASTY_DRIVER=auto` and fill Reverb vars; Broadcasty will pick Reverb first by default.

---

### Pusher

Create an app and obtain keys at: <https://dashboard.pusher.com/>

| Key                 | Required              | What                                          |
| ------------------- | --------------------- | --------------------------------------------- |
| `PUSHER_APP_ID`     | Yes (if using Pusher) | App ID.                                       |
| `PUSHER_APP_KEY`    | Yes                   | Public key.                                   |
| `PUSHER_APP_SECRET` | Yes                   | Secret key.                                   |
| `PUSHER_HOST`       | Yes                   | `api-pusher.pusher.com` or your cluster host. |
| `PUSHER_PORT`       | No                    | `443` (TLS).                                  |
| `PUSHER_SCHEME`     | No                    | `https`.                                      |

> In `auto` mode, Pusher is attempted if Reverb is absent/unhealthy and Pusher vars are present.

---

### Ably

Create an API key at: <https://ably.com/accounts>

| Key        | Required            | What                         |
| ---------- | ------------------- | ---------------------------- |
| `ABLY_KEY` | Yes (if using Ably) | In the form `appKey:secret`. |

> In auto mode, Ably is tried after Reverb/Pusher when `ABLY_KEY` is present.

---

### Web Push (VAPID) – Browser Notifications

Generate VAPID keys using your preferred tool (e.g. PHP WebPush or node-web-push).

- PHP: <https://github.com/web-push-libs/web-push-php>
- Node: <https://github.com/web-push-libs/web-push>

| Key                         | Required         | What                                 |
| --------------------------- | ---------------- | ------------------------------------ |
| `BROADCASTY_WEBPUSH`        | No               | `true` to enable Web Push bridge.    |
| `WEBPUSH_VAPID_SUBJECT`     | Yes when enabled | Contact URI `mailto:you@example.com` |
| `WEBPUSH_VAPID_PUBLIC_KEY`  | Yes when enabled | Base64URL public key.                |
| `WEBPUSH_VAPID_PRIVATE_KEY` | Yes when enabled | Private key.                         |

> Client must register a service worker and call `pushManager.subscribe()` using your `PUBLIC_KEY` (base64url).

---

### Firebase Cloud Messaging (FCM)

Create a Firebase project → Cloud Messaging → Server key.  
Console: <https://console.firebase.google.com/>

| Key              | Required         | What                                                      |
| ---------------- | ---------------- | --------------------------------------------------------- |
| `BROADCASTY_FCM` | No               | `true` to enable FCM bridge.                              |
| `FCM_SERVER_KEY` | Yes when enabled | Legacy server key (HTTP v1 not required for this bridge). |

> Client must provide its device/web FCM token to your backend; send with `['token' => $fcmToken]`.

---

### Apple Push Notification service (APNs)

Set up a Key (.p8) in Apple Developer → Keys → APNs.  
Docs: <https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server>

| Key               | Required         | What                                           |
| ----------------- | ---------------- | ---------------------------------------------- |
| `BROADCASTY_APNS` | No               | `true` to enable APNs bridge.                  |
| `APNS_KEY_ID`     | Yes when enabled | Key ID of your .p8.                            |
| `APNS_TEAM_ID`    | Yes when enabled | Apple Team ID.                                 |
| `APNS_P8_PATH`    | Yes when enabled | Absolute path to `.p8` file (secure location). |
| `APNS_BUNDLE_ID`  | Yes when enabled | iOS bundle identifier.                         |
| `APNS_SANDBOX`    | No               | `true` for sandbox; `false` for production.    |

> You must generate an APNs provider **JWT** for each request. Hook your JWT creation inside `ApnsBridge` where indicated.

---

### OneSignal

Create an app and find App ID + REST API Key at: <https://dashboard.onesignal.com/>

| Key                    | Required         | What                               |
| ---------------------- | ---------------- | ---------------------------------- |
| `BROADCASTY_ONESIGNAL` | No               | `true` to enable OneSignal bridge. |
| `ONESIGNAL_APP_ID`     | Yes when enabled | App ID.                            |
| `ONESIGNAL_API_KEY`    | Yes when enabled | REST API Key.                      |

> Client must provide `player_id` (OneSignal device ID). Send payload with `['player_id' => '...']`.

---

### CORS, TLS, and Origins

- Always serve production over **HTTPS**.
- Set `BROADCASTY_ALLOWED_ORIGINS` to comma-separated origins, e.g. `https://app.example.com,https://admin.example.com`.
- Avoid `*` in production.

---

### Provider Selection Cheatsheet

- Use **Reverb** for first-party WS in Laravel. Fill `REVERB_*` and keep `BROADCASTY_DRIVER=auto`.
- Use **Pusher** if you prefer managed WS; fill `PUSHER_*`.
- Use **Ably** alternatively; fill `ABLY_KEY`.
- Regardless of provider, keep **Redis** available for presence/replay.

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

---

## Database & Storage Model

Broadcasty-Lary uses **RDBMS for metadata/audit** and **Redis for presence + replay** (by default). Below is a precise map of what’s stored where, how to tune it, and what to change for production.

### 1) RDBMS Tables (migrations included)

Engine recommendation:

- **MySQL 8+/MariaDB 10.6+** or **PostgreSQL 13+**
- Charset/Collation: `utf8mb4` (MySQL) or `UTF8` (PostgreSQL)
- Row format: dynamic/compact (MySQL InnoDB)

**a) `broadcasty_channels`**

- Purpose: optional per-channel policy snapshot.
- Columns: `tenant_id (index)`, `name (index, unique with tenant)`, `policy (JSON)`, timestamps.
- Indexes: unique `(tenant_id, name)`; both are already indexed.
- Notes: if channels are ephemeral, you can leave this empty.

**b) `broadcasty_api_keys`**

- Purpose: service-to-service access (if you extend the package with HMAC/API-key flows).
- Columns: `tenant_id (index)`, `name`, `key (unique)`, `hash (unique)`, `scopes (JSON)`, `rotated_at`, timestamps.
- Rotation: use `php artisan broadcasty:key:rotate` to integrate with Vault/KMS in your app.

**c) `broadcasty_events`**

- Purpose: optional durable audit/replay mirror **(disabled by default)**; by default, replay lives in Redis for performance.
- Columns: `tenant_id (index)`, `channel (index)`, `partition (index)`, `sequence (index)`, `envelope_id (idempotency)`, `payload (BLOB/BYTEA)`, `meta (JSON)`, `published_at (index)`.
- Uniqueness: `(tenant_id, channel, partition, sequence)`.
- When to enable: only if you need **long-term retention**, compliance/regulatory replay, or cross-region ETL. For most cases, rely on Redis replay + compact retention.
- Tuning: partition by tenant or channel if this grows large; add composite indexes for typical queries.

**d) `broadcasty_audit_logs`**

- Purpose: action trails (publish, presence, admin actions).
- Columns: `tenant_id (index)`, `action`, `actor_id`, `ip`, `ctx (JSON)`, timestamps.
- Rotation: schedule deletion/archival every N days (see rotation section).

> **Table prefixing**: Laravel supports a per-connection prefix in `config/database.php` (e.g. `'prefix' => 'bcy_'`). The bundled migration uses explicit table names. If you need a table prefix, either (1) configure the DB connection prefix, or (2) edit the migration file names to include your prefix. We don’t force a custom prefix from `.env` to avoid conflicts.

#### Suggested extra indexes (optional, heavy traffic)

MySQL:

```sql
CREATE INDEX bcy_evt_tenant_chan_pub ON broadcasty_events (tenant_id, channel, published_at);
CREATE INDEX bcy_audit_tenant_time ON broadcasty_audit_logs (tenant_id, created_at);
```

PostgreSQL:

```sql
CREATE INDEX bcy_evt_tenant_chan_pub ON broadcasty_events (tenant_id, channel, published_at);
CREATE INDEX bcy_audit_tenant_time ON broadcasty_audit_logs (tenant_id, created_at);
```

### 2) Redis Keyspaces (default)

- Presence store: `bcy:prs:{tenant}:{channel}` → `HSET memberId -> JSON(member)`; TTL refreshed on join/heartbeat, base TTL from `config('broadcasty.presence.ttl')`.
- Sequences: `bcy:seq:{tenant}:{channel}:{partition}` → `INCR` for strict ordering.
- Replay store: `bcy:rpl:{tenant}:{channel}:{partition}` → `HSET sequence -> payload` with `EXPIRE` = `replay.retention_sec`.

> **Isolation & multi-tenant**: `BROADCASTY_REDIS_PREFIX` defaults to `bcy:`. If your Redis is shared, change the prefix and keep non-overlapping tenant IDs.

### 3) Replay retention & compaction

Config: `config('broadcasty.replay')`

- `retention_sec`: how long per-partition data stays in Redis (default 3600s).
- `max_bytes_per_channel`: soft target if you implement your own compactor.
- `partitions`: number of partitions per channel from the policy resolver.

**Compaction & rotation**: the included `RedisReplayStore` relies on Redis `EXPIRE`. For long-term retention, either:

- Mirror events into `broadcasty_events` (toggle in your app’s publisher path), or
- Export Redis replay to another store on a schedule (queue job).

### 4) Migrations in production (zero-downtime tips)

- Use `php artisan migrate` behind maintenance mode or deploy windows.
- Add new large indexes concurrently (PostgreSQL `CREATE INDEX CONCURRENTLY`, MySQL pt-online-schema-change or gh-ost).
- For very large `broadcasty_events`, prefer partitioned tables (PostgreSQL declarative partitioning by month/tenant; MySQL 8 range/list partitioning).

### 5) Backup & restore

- DB: include `broadcasty_*` tables in your normal backups.
- Redis: snapshotting (RDB) or AOF; ensure persistence if replay durability matters.
- Test restores for a representative tenant/channel before enabling strict SLAs.

---

### Operational Housekeeping

#### Rotations & retention

- **JWT/public keys**: rotate regularly via your IdP/secret manager, then refresh `BROADCASTY_JWT_PUBLIC_KEY`.
- **API keys**: `php artisan broadcasty:key:rotate` (extend the command to your KMS/Vault).
- **Audit logs**: add a scheduled task to delete/archive rows older than N days:

```php
// app/Console/Kernel.php
$schedule->call(fn() => \DB::table('broadcasty_audit_logs')->where('created_at','<',now()->subDays(90))->delete())->daily();
```

- **Replay**: set `replay.retention_sec` to match your use case; for bigger values, monitor Redis memory.

#### Monitoring & alerts

- Scrape `GET /broadcasty/metrics` (enable via `BROADCASTY_PROM=true`).
- Alert on: driver failover, publish failures, 4xx/5xx spikes, Redis memory pressure, JWT verification errors.
- Enable OpenTelemetry to your collector to trace publish/subscribe paths.

#### Scaling guidance

- Increase **partitions** for hot channels in your `PolicyResolver`.
- Horizontal scale your PHP workers; SSE runs efficiently behind Nginx/Apache with buffering disabled (`X-Accel-Buffering: no` header is set).
- For WebSockets, offload to **Reverb/Pusher/Ably** and keep Laravel focused on auth/publish and SSE fallback.

#### Data compliance

- Avoid placing PII in message payloads. Prefer IDs and fetch details server-side.
- Use `meta.schema` for versioning. Add a transformer layer (server or client) when schema evolves.
- For GDPR requests, delete user presence entries and prune audit logs referencing the user ID.

---

### Deployment & CI/CD Notes

- Ensure `.env` is templated with all keys in this README to avoid ambiguity.
- Run `npm run build` in `resources/js-sdk` only if you plan to publish/pack the SDK to your registry; otherwise import from source.
- Health-checks: an app route that calls `app('broadcasty')->metrics()` is already exposed at `/broadcasty/metrics` (when enabled). Use it for liveness/readiness.
- If you need DB table prefixes, prefer Laravel DB **connection prefix** to keep migrations simple.

---

### Choosing Database vs Redis for Replay

- **Redis (default)**: fastest; great for 1–24h retention, reconnect catch-up, dashboards.
- **Database mirror (optional, custom)**: implement a simple listener to also insert into `broadcasty_events` during publish for long-term retention/compliance. Query via indexes shown above.
- **Don’t switch `replay.store` to `database`** unless you implement `DatabaseReplayStore` in your app—by default, this package wires Redis replay.

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

## External Provider Setup & Docs

Use these official links to create accounts, obtain API keys, and follow provider-specific guides.

### Pusher / Laravel Reverb (Pusher protocol)

- Create an app / get keys: https://dashboard.pusher.com/
- Pusher HTTP API docs: https://pusher.com/docs/channels/server_api/http-api/
- Laravel Reverb docs: https://laravel.com/docs/reverb

### Ably

- Sign up / dashboard: https://ably.com/
- Create API key: https://ably.com/docs/auth/basic
- REST publish: https://ably.com/docs/api/rest-api

### Firebase Cloud Messaging (FCM)

- Console: https://console.firebase.google.com/
- Docs: https://firebase.google.com/docs/cloud-messaging
- Server key (legacy) lives under: Project Settings → Cloud Messaging → “Server key”.  
  If you use the HTTP v1 API instead, adjust the bridge or issue a PR to add OAuth2 service account support.

### Apple Push Notification service (APNs)

- Create .p8 Auth Key: https://developer.apple.com/account/resources/authkeys/list
- APNs provider token (JWT) guide: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server
- Bundle ID & entitlements: https://developer.apple.com/documentation/bundleresources/entitlements

### OneSignal

- Dashboard: https://dashboard.onesignal.com/
- REST API docs: https://documentation.onesignal.com/reference/create-notification

### Web Push (VAPID)

- Web Push library (PHP): https://github.com/web-push-libs/web-push-php#vapid
- Concept overview: https://web.dev/articles/push-notifications-web-push-protocol

### Observability

- Prometheus: https://prometheus.io/
- OpenTelemetry: https://opentelemetry.io/

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

- **@triyatna** — creator & maintainer
- Community PRs are welcome. Please open issues with clear repro steps and propose focused PRs. Follow PSR-12/Laravel conventions. Add tests when changing core flows (publish/subscribe/replay/security).

---

## License

This package is released under the [MIT License](LICENSE).

---
