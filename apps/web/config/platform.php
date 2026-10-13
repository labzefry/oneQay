<?php
// Author by Lab | zefry. Ownership is a private host binding, never a browser-submitted role.
return [
    'enabled' => (bool) env('ONEQAY_PLATFORM_ENABLED', false),
    'owner_tenant' => env('ONEQAY_PLATFORM_OWNER_TENANT', ''),
    'owner_identity' => env('ONEQAY_PLATFORM_OWNER_IDENTITY', ''),
    'public_url' => env('APP_URL', ''),
    'bounce_address' => env('ONEQAY_MAIL_BOUNCE_ADDRESS', ''),
    'feedback_secret' => env('ONEQAY_MAIL_FEEDBACK_SECRET', ''),
];
