<?php

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

return [
    'runtime_class' => env('ONEQAY_RUNTIME_CLASS'),

    // Author by Lab | zefry
    'first_control_principal_credential_bootstrap' => [
        'enabled' => filter_var(
            env('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'privileged_totp_mfa' => [
        'enabled' => filter_var(
            env('ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'privileged_step_up' => [
        'enabled' => filter_var(
            env('ONEQAY_PRIVILEGED_STEP_UP_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        'freshness_seconds' => 300,
    ],

    'authentication_recovery' => [
        'enabled' => filter_var(
            env('ONEQAY_AUTHENTICATION_RECOVERY_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        'restricted_session_ttl_seconds' => 600,
    ],

    'session_control' => [
        'enabled' => filter_var(
            env('ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        'idle_ttl_seconds' => 7200,
        'absolute_ttl_seconds' => 43200,
    ],

    'pos_sale_completion' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_SALE_COMPLETION_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_sale_void' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_SALE_VOID_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_sale_cash_refund' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_SALE_CASH_REFUND_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_catalog_preparation' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_CATALOG_PREPARATION_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_shift_opening' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_SHIFT_OPENING_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_shift_opening_cash_evidence' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_shift_closing_cash_evidence' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_inventory_baseline' => [
        'enabled' => filter_var(
            env('ONEQAY_POS_INVENTORY_BASELINE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_operational_reporting' => [
        // Sprint156 sales reporting is read-only, tenant/outlet scoped, Local/Test/CI-only, and fail-closed.
        'enabled' => filter_var(
            env('ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'system_update' => [
        'control_plane_enabled' => filter_var(
            env('ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        'install_enabled' => false,
    ],

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