<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Missing flag behavior
    |--------------------------------------------------------------------------
    |
    | When a flag key does not exist: "inactive" fails closed (recommended for
    | entitlements), "active" fails open (useful only in local experiments).
    |
    */
    'missing_flag' => env('FLAGDECK_MISSING_FLAG', 'inactive'),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('FLAGDECK_CACHE_ENABLED', true),
        'store' => env('FLAGDECK_CACHE_STORE', null),
        'prefix' => env('FLAGDECK_CACHE_PREFIX', 'flagdeck'),
        'ttl' => (int) env('FLAGDECK_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Used when a flag restricts environments. Defaults to APP_ENV.
    |
    */
    'environment' => env('FLAGDECK_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Percentage bucketing
    |--------------------------------------------------------------------------
    |
    | Sticky rollouts hash flag key + bucket key. Salt changes redistribute
    | users — treat rotations as intentional.
    |
    */
    'bucketing' => [
        'salt' => env('FLAGDECK_BUCKET_SALT', 'flagdeck'),
        // user | tenant | anonymous
        'fallback_order' => ['user', 'tenant', 'anonymous'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'abort_code' => 403,
        'abort_message' => 'This feature is not available.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Management API
    |--------------------------------------------------------------------------
    |
    | Disabled by default. Enable only behind authentication and authorization.
    |
    */
    'api' => [
        'enabled' => env('FLAGDECK_API_ENABLED', false),
        'prefix' => env('FLAGDECK_API_PREFIX', 'flagdeck'),
        'middleware' => ['api', 'auth:sanctum'],
        'allow_evaluate' => env('FLAGDECK_API_ALLOW_EVALUATE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('FLAGDECK_AUDIT_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Context resolvers (optional callables / class names)
    |--------------------------------------------------------------------------
    |
    | Return null when unknown. Override in a service provider if your app
    | resolves tenants differently.
    |
    */
    'context' => [
        'user_id' => null,
        'tenant_id' => null,
        'anonymous_id' => null,
    ],
];
