<?php

declare(strict_types=1);

// Author by Lab | zefry
// Dedicated Sprint220 durable-staging updater configuration.
// Keeping this in config/ makes the values part of Laravel config:cache while the
// historical shared oneqay.php contract remains byte-for-byte unchanged.
return [
    'enabled' => filter_var(env('ONEQAY_DEVELOPMENT_UPDATER_ENABLED', false), FILTER_VALIDATE_BOOL),
    'private_root' => env('ONEQAY_DEVELOPMENT_UPDATER_PRIVATE_ROOT', ''),
    'operator_token_sha256' => env('ONEQAY_DEVELOPMENT_UPDATER_OPERATOR_TOKEN_SHA256', ''),
    'totp_secret' => env('ONEQAY_DEVELOPMENT_UPDATER_TOTP_SECRET', ''),
    'request_hmac_key' => env('ONEQAY_DEVELOPMENT_UPDATER_REQUEST_HMAC_KEY', ''),
    'github_token' => env('ONEQAY_DEVELOPMENT_UPDATER_GITHUB_TOKEN', ''),
    'release_root' => env('ONEQAY_DEVELOPMENT_UPDATER_RELEASE_ROOT', ''),
    'active_release_pointer' => env('ONEQAY_DEVELOPMENT_UPDATER_ACTIVE_RELEASE_POINTER', ''),
    'runtime_env_path' => env('ONEQAY_DEVELOPMENT_UPDATER_RUNTIME_ENV_PATH', ''),
    'document_root' => env('ONEQAY_DEVELOPMENT_UPDATER_DOCUMENT_ROOT', ''),
    'document_root_mode' => env('ONEQAY_DEVELOPMENT_UPDATER_DOCUMENT_ROOT_MODE', 'FIXED_PUBLIC_BRIDGE'),
    'attestation_url' => env('ONEQAY_DEVELOPMENT_UPDATER_ATTESTATION_URL', ''),
    'attestation_token' => env('ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN', ''),
    'environment_id' => env('ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID', ''),
    'running_source_commit' => env('ONEQAY_RUNNING_SOURCE_COMMIT', ''),
    'running_artifact_sha256' => env('ONEQAY_RUNNING_ARTIFACT_SHA256', ''),
];
