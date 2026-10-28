<?php
// Author by Lab | zefry. Ownership is a private host binding, never a browser-submitted role.
return [
    'enabled' => (bool) env('ONEQAY_PLATFORM_ENABLED', false),
    'owner_tenant' => env('ONEQAY_PLATFORM_OWNER_TENANT', ''),
    'owner_identity' => env('ONEQAY_PLATFORM_OWNER_IDENTITY', ''),
    // Shared private storage survives release archiving; never place reset backups in public/build.
    'reset_backup_path' => env('ONEQAY_BUSINESS_RESET_BACKUP_PATH', dirname((string) env('SESSION_FILES', storage_path('framework/sessions'))).'/business-reset-backups'),
    'business_domains_enabled' => (bool) env('ONEQAY_BUSINESS_DOMAINS_ENABLED', false),
    // Tenant-only HTTPS base; empty preserves the current website-based addresses.
    'business_domain_url' => env('ONEQAY_BUSINESS_DOMAIN_URL', ''),
    'business_domain_reserved' => explode(',', (string) env('ONEQAY_BUSINESS_DOMAIN_RESERVED', '')),
    'public_url' => env('APP_URL', ''),
    'bounce_address' => env('ONEQAY_MAIL_BOUNCE_ADDRESS', ''),
    'feedback_secret' => env('ONEQAY_MAIL_FEEDBACK_SECRET', ''),
];
