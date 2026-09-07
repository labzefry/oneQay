<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$mode = $argv[1] ?? '';
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Final Shift Close runtime binding manifest control-plane regression failed: '.$case);
    }
};

$expectedPresent = match ($mode) {
    'disabled', 'enabled-empty-token', 'enabled-short-token', 'enabled-oversized-token' => false,
    'enabled-valid-token-missing-auth', 'enabled-valid-token-malformed-auth' => true,
    default => throw new InvalidArgumentException('Unknown manifest control-plane qualification mode.'),
};

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$bootstrapRequest = Request::create('/health/live', 'GET');
$bootstrapResponse = $kernel->handle($bootstrapRequest);
$assert($bootstrapResponse->getStatusCode() === 200, 'CTRL-001 application bootstrap remains healthy');
$kernel->terminate($bootstrapRequest, $bootstrapResponse);

$routeName = 'internal.final-shift-close.runtime-binding-manifest.materialize';
$routePath = '/internal/final-shift-close/runtime-binding-manifest/materialize';
$route = $app->make('router')->getRoutes()->getByName($routeName);
$registered = $route !== null;
$assert($registered === $expectedPresent, 'CTRL-002 '.$mode.' route registration disposition');

if (! $expectedPresent) {
    $request = Request::create($routePath, 'POST');
    $response = $kernel->handle($request);
    $assert($response->getStatusCode() === 404, 'CTRL-003 disabled/invalid gate has no HTTP surface');
    $kernel->terminate($request, $response);

    fwrite(STDOUT, "Final Shift Close runtime binding manifest control-plane regression passed for {$mode}.\n");
    exit(0);
}

$assert($route !== null, 'CTRL-004 valid gate route object exists');
$methods = $route->methods();
$assert(in_array('POST', $methods, true), 'CTRL-005 endpoint retains POST method');
$assert(! in_array('GET', $methods, true), 'CTRL-006 endpoint exposes no GET method');
$middleware = $route->gatherMiddleware();
$assert(in_array('throttle:1,1', $middleware, true), 'CTRL-007 throttle remains one request per minute');
$assert(
    in_array(RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class, $middleware, true),
    'CTRL-008 bearer-token middleware remains attached',
);

$server = [];
if ($mode === 'enabled-valid-token-malformed-auth') {
    $server['HTTP_AUTHORIZATION'] = 'Bearer short';
}
$request = Request::create($routePath, 'POST', server: $server);
$response = $kernel->handle($request);
$assert($response->getStatusCode() === 401, 'CTRL-009 missing/malformed bearer credential fails before controller');
$kernel->terminate($request, $response);

fwrite(STDOUT, "Final Shift Close runtime binding manifest control-plane regression passed for {$mode}.\n");
