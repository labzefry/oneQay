<?php

declare(strict_types=1);

// Author by Lab | zefry
return [
    'enabled' => filter_var(env('ONEQAY_TECHNICAL_PREVIEW_ENABLED', false), FILTER_VALIDATE_BOOL),
    'runtime_class' => env('ONEQAY_RUNTIME_CLASS', ''),

    // Deployed Synthetic Technical Preview only. Values are captured by Laravel
    // configuration caching and applied to the shared session runtime only after
    // the fail-closed TechnicalPreviewRuntimePolicy accepts the whole envelope.
    'session' => [
        'driver' => env('SESSION_DRIVER', 'array'),
        'lifetime' => (int) env('SESSION_LIFETIME', 120),
        'encrypt' => filter_var(env('SESSION_ENCRYPT', false), FILTER_VALIDATE_BOOL),
        'secure' => filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOL),
        'http_only' => true,
        'same_site' => 'lax',
        'domain' => null,
        'path' => '/',
        'cookie' => env('SESSION_COOKIE', 'oneqay-session'),
    ],
];
