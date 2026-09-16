<?php

declare(strict_types=1);

// Author by Lab | zefry

return [
    // Sprint177 guarded merchant-context delivery is denied unless explicitly armed.
    'enabled' => filter_var(
        env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),

    // The exact tuple below is the sole authorization source. Console input cannot mint or alter it.
    'grant' => [
        'tenant_id' => env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_TENANT_ID', ''),
        'identity_id' => env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_IDENTITY_ID', ''),
        'organization_id' => env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ORGANIZATION_ID', ''),
        'outlet_id' => env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_OUTLET_ID', ''),
        'device_id' => env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_DEVICE_ID', ''),
        'provisioning_id' => env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_PROVISIONING_ID', ''),
    ],
];
