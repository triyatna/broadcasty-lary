<?php

return [
    'default_driver' => env('BROADCASTY_DRIVER', 'auto'),

    'drivers' => [
        'redis' => [
            'connection' => env('BROADCASTY_REDIS_CONN', 'default'),
            'prefix' => env('BROADCASTY_REDIS_PREFIX', 'bcy:'),
        ],
        'ably' => [
            'key' => env('ABLY_KEY'),
        ],
        'pusher' => [
            'key' => env('PUSHER_APP_KEY') ?: env('REVERB_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET') ?: env('REVERB_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID') ?: env('REVERB_APP_ID'),
            'host' => env('PUSHER_HOST') ?: env('REVERB_HOST'),
            'port' => env('PUSHER_PORT') ?: env('REVERB_PORT'),
            'scheme' => env('PUSHER_SCHEME', env('REVERB_SCHEME', 'https')),
        ],
        'reverb' => [
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'host' => env('REVERB_HOST'),
            'port' => env('REVERB_PORT'),
            'scheme' => env('REVERB_SCHEME', 'https'),
        ],
        'null' => [],
    ],

    'auto' => [
        'order' => explode(',', env('BROADCASTY_AUTO_ORDER', 'reverb,pusher,ably,redis')),
        'health_timeout_ms' => 800,
        'circuit_break_ms' => 10000
    ],

    'handshake' => [
        'jwt_public_keys' => [env('BROADCASTY_JWT_PUBLIC_KEY')],
        'leeway' => 10,
        'allowed_algs' => ['RS256', 'EdDSA'],
        'max_skew_sec' => 15,
        'required_claims' => ['sub','iat','exp','tid','roles'],
        'nonce_ttl' => 60,
    ],

    'rbac' => [
        'resolver' => \Triyatna\Broadcasty\Policies\DefaultPolicyResolver::class,
        'cache_ttl' => 10,
    ],

    'replay' => [
        'store' => env('BROADCASTY_REPLAY_STORE', 'redis'),
        'retention_sec' => 3600,
        'max_bytes_per_channel' => 25000000,
        'partitions' => 8
    ],

    'presence' => [
        'driver' => 'redis',
        'ttl' => 120,
        'separate_store_prefix' => env('BROADCASTY_PRESENCE_PREFIX', 'bcy:prs:')
    ],

    'push' => [
        'webpush' => [
            'enabled' => env('BROADCASTY_WEBPUSH', true),
            'vapid' => [
                'subject' => env('WEBPUSH_VAPID_SUBJECT'),
                'public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
                'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
            ],
        ],
        'fcm' => [
            'enabled' => env('BROADCASTY_FCM', false),
            'server_key' => env('FCM_SERVER_KEY'),
        ],
        'apns' => [
            'enabled' => env('BROADCASTY_APNS', false),
            'key_id' => env('APNS_KEY_ID'),
            'team_id' => env('APNS_TEAM_ID'),
            'p8_path' => env('APNS_P8_PATH'),
            'bundle_id' => env('APNS_BUNDLE_ID'),
            'sandbox' => env('APNS_SANDBOX', false),
        ],
        'onesignal' => [
            'enabled' => env('BROADCASTY_ONESIGNAL', false),
            'app_id' => env('ONESIGNAL_APP_ID'),
            'api_key' => env('ONESIGNAL_API_KEY'),
        ],
    ],

    'limits' => [
        'per_ip_per_min' => 600,
        'per_user_per_min' => 1200,
        'per_channel_per_min' => 3000,
        'payload_bytes_max' => 65536,
    ],

    'security' => [
        'force_tls' => true,
        'hmac_required' => true,
        'timestamp_tolerance_sec' => 15,
        'rotate_days' => 60,
        'allowed_origins' => explode(',', env('BROADCASTY_ALLOWED_ORIGINS', '*')),
    ],

    'observability' => [
        'opentelemetry' => env('BROADCASTY_OTEL', false),
        'prometheus' => [
            'enabled' => env('BROADCASTY_PROM', true),
            'path' => '/broadcasty/metrics',
        ],
        'structured_log' => true,
    ],

    'tenancy' => [
        'header' => 'X-Tenant-Id',
        'claim' => 'tid',
        'isolation_prefix' => 'tenant:',
    ],
];