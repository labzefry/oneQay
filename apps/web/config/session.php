<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'array'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => filter_var(env('SESSION_ENCRYPT', false), FILTER_VALIDATE_BOOL),
    'files' => storage_path('framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', Str::slug((string) env('APP_NAME', 'oneQay')).'-session'),
    'path' => '/',
    'domain' => null,
    'secure' => env('SESSION_SECURE_COOKIE') === null
        ? null
        : filter_var(env('SESSION_SECURE_COOKIE'), FILTER_VALIDATE_BOOL),
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
];
