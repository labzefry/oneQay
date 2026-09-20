<?php

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

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

    'privileged_totp_mfa' => [
        // Local/Test/CI privileged TOTP MFA remains fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'privileged_step_up' => [
        // Local/Test/CI privileged step-up remains fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_PRIVILEGED_STEP_UP_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        // Sprint 31 fixed freshness window; not environment-configurable.
        'freshness_seconds' => 300,
    ],

    'authentication_recovery' => [
        // Sprint 32 recovery proof remains fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_AUTHENTICATION_RECOVERY_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        // Restricted recovery-session lifetime is fixed and not environment-configurable.
        'restricted_session_ttl_seconds' => 600,
    ],

    'session_control' => [
        // Sprint36 durable first-party session authority remains fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
        // Fixed idle lifetime; not environment-configurable.
        'idle_ttl_seconds' => 7200,
        // Sprint38 fixed absolute lifetime; not environment-configurable.
        'absolute_ttl_seconds' => 43200,
    ],

    'pos_sale_completion' => [
        // JRN-006 durable sale completion remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_SALE_COMPLETION_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_sale_void' => [
        // Sprint50 JRN-007 completed-sale void remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_SALE_VOID_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_sale_cash_refund' => [
        // Sprint52 JRN-007 full CASH refund evidence remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_SALE_CASH_REFUND_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_catalog_preparation' => [
        // Sprint47 JRN-004 catalog preparation remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_CATALOG_PREPARATION_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_shift_opening' => [
        // Sprint48 JRN-005 shift opening remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_SHIFT_OPENING_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_shift_opening_cash_evidence' => [
        // Sprint53 JRN-010 prerequisite opening-cash evidence remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_shift_closing_cash_evidence' => [
        // Sprint54 JRN-010 prerequisite closing-cash evidence remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

    'pos_inventory_baseline' => [
        // Sprint51 JRN-008 opening inventory baseline remains Local/Test/CI-only and fail-closed unless explicitly armed.
        'enabled' => filter_var(
            env('ONEQAY_POS_INVENTORY_BASELINE_ENABLED', false),
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
