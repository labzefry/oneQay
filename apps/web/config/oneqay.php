<?php

return [
    'runtime_class' => env('ONEQAY_RUNTIME_CLASS'),

    // Author by Lab | zefry
    'first_control_principal_credential_bootstrap' => [
        // Local/Test/CI console bootstrap is denied unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'system_update' => [
        // Backend control-plane visibility/checking may only be enabled explicitly.
        'control_plane_enabled' => filter_var(
            env('ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),

        // Install/activation is intentionally hard-disabled in this milestone.
        // A later separately authorized implementation must change source and pass security gates.
        'install_enabled' => false,
    ],

    // Technical Preview qualification only. This is not Production/business persistence.
    'preview_database_qualification' => [
        'enabled' => filter_var(env('ONEQAY_PREVIEW_DB_QUALIFICATION_ENABLED', false), FILTER_VALIDATE_BOOL),
        'profile' => env('ONEQAY_PREVIEW_DB_PROFILE', ''),
        'host' => env('ONEQAY_PREVIEW_DB_HOST', ''),
        'port' => (int) env('ONEQAY_PREVIEW_DB_PORT', 3306),
        'database' => env('ONEQAY_PREVIEW_DB_DATABASE', ''),
        'username' => env('ONEQAY_PREVIEW_DB_USERNAME', ''),
        'password' => env('ONEQAY_PREVIEW_DB_PASSWORD', ''),
        'charset' => env('ONEQAY_PREVIEW_DB_CHARSET', 'utf8mb4'),
    ],
];
