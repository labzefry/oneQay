<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'array'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => env('SESSION_FILES', storage_path('framework/sessions')),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', Str::slug((string) env('APP_NAME', 'oneQay')).'-session'),
    'path' => '/',
    'domain' => null,
    // Production cookies MUST be Secure even if a stale environment binding sets false.
    // Keep test/CI HTTP sessions configurable to preserve isolated contract tests.
    'secure' => env('APP_ENV') === 'production'
        ? true
        : filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOL),
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
];
