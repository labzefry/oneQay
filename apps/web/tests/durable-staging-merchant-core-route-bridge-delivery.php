<?php

declare(strict_types=1);

// Author by Lab | zefry

use App\Providers\DurableStagingMerchantCoreRequestBridge;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

$environment = [
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE=',
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'ONEQAY_RUNTIME_CLASS' => 'durable-staging',
    'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED' => 'true',
    'ONEQAY_RUNNING_SOURCE_COMMIT' => str_repeat('a', 40),
    'ONEQAY_RUNNING_ARTIFACT_SHA256' => str_repeat('b', 64),
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_IDLE_TTL_SECONDS' => '7200',
    'ONEQAY_AUTHENTICATION_SESSION_ABSOLUTE_TTL_SECONDS' => '43200',
    'ONEQAY_POS_OPERATIONS_HUB_ENABLED' => 'true',
    'ONEQAY_POS_CATALOG_INVENTORY_SETUP_ENABLED' => 'true',
    'ONEQAY_POS_CATALOG_PREPARATION_ENABLED' => 'true',
    'ONEQAY_POS_INVENTORY_BASELINE_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_START_WORKSPACE_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED' => 'true',
    'ONEQAY_POS_CASHIER_WORKSPACE_ENABLED' => 'true',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED' => 'true',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_TENANT_ID' => 'tenant-s264',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_IDENTITY_ID' => 'identity-s264',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ORGANIZATION_ID' => 'organization-s264',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_OUTLET_ID' => 'outlet-s264',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_DEVICE_ID' => 'device-s264',
];

foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

$runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
if ($runtimeClass !== 'durable-staging') {
    fwrite(STDERR, "Sprint264 expected durable-staging external runtime.\n");
    exit(1);
}

/** @var \Illuminate\Routing\Router $router */
$router = $app->make('router');
$loginRoute = $router->getRoutes()->getByName('auth.first-party.login');
if ($loginRoute === null) {
    fwrite(STDERR, "Sprint264 durable-staging login route was not registered.\n");
    exit(1);
}

// Route::gatherMiddleware() returns the route's declared middleware names; it does
// not expand middleware groups. The live HTTP router resolves the `web` group via
// Router::gatherRouteMiddleware(), so the regression must inspect that same
// effective pipeline instead of the unexpanded route declaration.
$gathered = $router->gatherRouteMiddleware($loginRoute);
if (! in_array(DurableStagingMerchantCoreRequestBridge::class, $gathered, true)) {
    fwrite(STDERR, "Sprint264 login route effective middleware did not include the durable-staging compatibility bridge.\n");
    exit(1);
}

$bridge = new DurableStagingMerchantCoreRequestBridge();
$request = Request::create('/auth/login', 'POST');
$observedRuntime = null;
$response = $bridge->handle(
    $request,
    static function (Request $request) use (&$observedRuntime): Response {
        $observedRuntime = config('oneqay.runtime_class');

        if ($request->attributes->get('oneqay.external_runtime_class') !== 'durable-staging'
            || $request->attributes->get('oneqay.runtime_compatibility_bridge') !== 'merchant-core-ci') {
            return new Response('attribute-mismatch', 500);
        }

        return new Response('', 204);
    },
);

if ($response->getStatusCode() !== 204 || $observedRuntime !== 'ci') {
    fwrite(STDERR, "Sprint264 login request was not projected into the guarded CI compatibility runtime.\n");
    exit(1);
}

if (config('oneqay.runtime_class') !== 'durable-staging') {
    fwrite(STDERR, "Sprint264 compatibility bridge did not restore durable-staging after request completion.\n");
    exit(1);
}

$unrelated = Request::create('/not-merchant-core', 'GET');
$unrelatedRuntime = null;
$bridge->handle(
    $unrelated,
    static function (Request $request) use (&$unrelatedRuntime): Response {
        $unrelatedRuntime = config('oneqay.runtime_class');

        return new Response('', 204);
    },
);

if ($unrelatedRuntime !== 'durable-staging') {
    fwrite(STDERR, "Sprint264 bridge expanded beyond the exact merchant-core request allowlist.\n");
    exit(1);
}

$controller = file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartySessionController.php');
$successor = file_get_contents(__DIR__.'/../app/Providers/DurableStagingMerchantCoreRouteBridgeServiceProvider.php');
$providers = file_get_contents(__DIR__.'/../bootstrap/providers.php');

if (! is_string($controller)
    || ! str_contains($controller, "in_array(\$runtime, ['local', 'test', 'ci'], true)")
    || str_contains($controller, "['local', 'test', 'ci', 'durable-staging']")) {
    fwrite(STDERR, "Sprint264 widened the historical identity-controller runtime allowlist.\n");
    exit(1);
}

if (! is_string($successor)
    || ! str_contains($successor, 'DurableStagingMerchantCoreBridge::armedFor($runtimeClass)')
    || ! str_contains($successor, "prependMiddlewareToGroup(\n            'web',\n            DurableStagingMerchantCoreRequestBridge::class")) {
    fwrite(STDERR, "Sprint264 successor route bridge provider contract is incomplete.\n");
    exit(1);
}

if (! is_string($providers)
    || ! str_contains($providers, 'DurableStagingMerchantCoreRouteBridgeServiceProvider::class')) {
    fwrite(STDERR, "Sprint264 successor route bridge provider is not registered.\n");
    exit(1);
}

foreach (['production', 'technical-preview'] as $forbiddenRuntime) {
    if (str_contains($successor, "'{$forbiddenRuntime}'")) {
        fwrite(STDERR, "Sprint264 successor bridge contains a forbidden runtime expansion.\n");
        exit(1);
    }
}

echo "Sprint264 durable-staging merchant-core route bridge delivery regression passed.\n";
