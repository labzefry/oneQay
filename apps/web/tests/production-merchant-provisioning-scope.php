<?php

declare(strict_types=1);

// Author by Lab | zefry. Must fail closed in CI and when unarmed.
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('q', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Infrastructure\Bootstrap\ProductionMerchantProvisioningScope;
use App\Infrastructure\Configuration\CriticalConfiguration;

if (ProductionMerchantProvisioningScope::allows('production')
    || ProductionMerchantProvisioningScope::allows('ci')
    || ProductionMerchantProvisioningScope::allows('preview')
    || ProductionMerchantProvisioningScope::allows('')) {
    throw new RuntimeException('Unarmed production merchant bootstrap must be denied.');
}
$valid = [
    'app_key' => 'base64:'.base64_encode(str_repeat('a', 32)),
    'runtime_class' => 'production',
    'app_debug' => false,
    'app_env' => 'production',
];
if (! CriticalConfiguration::isReady($valid)) {
    throw new RuntimeException('Production configuration should support readiness.');
}
$failed = false;
try {
    ProductionMerchantProvisioningScope::execute(static fn (): bool => true);
} catch (RuntimeException) {
    $failed = true;
}
if (! $failed || ProductionMerchantProvisioningScope::allows('production')) {
    throw new RuntimeException('Production provisioning must deny unqualified caller.');
}
echo "production_merchant_provisioning_scope_fail_closed_ok\n";
