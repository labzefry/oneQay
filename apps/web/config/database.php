<?php

return [
    // Author by Lab | zefry
    // Sprint 19 durable persistence is Local/Test/CI-only and disabled by default.
    'oneqay_persistence_enabled' => filter_var(
        env('ONEQAY_PERSISTENCE_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),

    // Laravel resolves connections lazily. Application boot/readiness does not connect here.
    'default' => 'oneqay',

    'connections' => [
        'oneqay' => [
            'driver' => env('ONEQAY_DB_DRIVER', 'mysql'),
            'url' => null,
            'host' => env('ONEQAY_DB_HOST', '127.0.0.1'),
            'port' => env('ONEQAY_DB_PORT', '3306'),
            'database' => env('ONEQAY_DB_DATABASE', ''),
            'username' => env('ONEQAY_DB_USERNAME', ''),
            'password' => env('ONEQAY_DB_PASSWORD', ''),
            'unix_socket' => env('ONEQAY_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => [],
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
];
