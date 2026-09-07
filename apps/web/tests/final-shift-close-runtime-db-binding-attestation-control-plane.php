<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingTokenMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$mode = $argv[1] ?? '';
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Final Shift Close runtime DB binding attestation control-plane regression failed: '.$case);
    }
};

$expectedPresent = match ($mode) {
    'disabled', 'enabled-empty-token', 'enabled-short-token', 'enabled-oversized-token' => false,
    'enabled-valid-token' => true,
    default => throw new InvalidArgumentException('Unknown DB-binding attestation control-plane qualification mode.'),
};

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$bootstrapRequest = Request::create('/health/live', 'GET');
$bootstrapResponse = $kernel->handle($bootstrapRequest);
$assert($bootstrapResponse->getStatusCode() === 200, 'CTRL-001 application bootstrap remains healthy');
$kernel->terminate($bootstrapRequest, $bootstrapResponse);

$routeName = 'internal.final-shift-close.runtime-db-binding-attestation';
$routePath = '/internal/final-shift-close/runtime-db-binding-attestation';
$route = $app->make('router')->getRoutes()->getByName($routeName);
$registered = $route !== null;
$assert($registered === $expectedPresent, 'CTRL-002 '.$mode.' route registration disposition');

if (! $expectedPresent) {
    $request = Request::create($routePath, 'GET');
    $response = $kernel->handle($request);
    $assert($response->getStatusCode() === 404, 'CTRL-003 disabled/invalid gate has no HTTP surface');
    $kernel->terminate($request, $response);

    fwrite(STDOUT, "Final Shift Close runtime DB binding attestation control-plane regression passed for {$mode}.\n");
    exit(0);
}

$assert($route !== null, 'CTRL-004 valid gate route object exists');
$middleware = $route->gatherMiddleware();
$assert(in_array('throttle:2,1', $middleware, true), 'CTRL-005 throttle boundary remains two requests per minute');
$assert(
    in_array(RequireFinalShiftCloseRuntimeBindingTokenMiddleware::class, $middleware, true),
    'CTRL-006 bearer-token middleware remains attached',
);
$assert(
    strtoupper(implode(',', $route->methods())) === 'GET,HEAD',
    'CTRL-007 endpoint remains GET-only',
);

$unauthenticatedRequest = Request::create($routePath, 'GET');
$unauthenticatedResponse = $kernel->handle($unauthenticatedRequest);
$assert($unauthenticatedResponse->getStatusCode() === 404, 'CTRL-008 missing bearer credential fails closed before controller');
$cacheControl = (string) $unauthenticatedResponse->headers->get('Cache-Control');
$assert(
    str_contains($cacheControl, 'no-store') && str_contains($cacheControl, 'private'),
    'CTRL-009 authentication rejection remains non-cacheable and private',
);
$assert(
    $unauthenticatedResponse->headers->get('X-Content-Type-Options') === 'nosniff',
    'CTRL-010 authentication rejection keeps nosniff',
);
$kernel->terminate($unauthenticatedRequest, $unauthenticatedResponse);

$malformedRequest = Request::create($routePath, 'GET', server: [
    'HTTP_AUTHORIZATION' => 'Bearer short',
]);
$malformedResponse = $kernel->handle($malformedRequest);
$assert($malformedResponse->getStatusCode() === 404, 'CTRL-011 malformed bearer credential remains indistinguishable');
$kernel->terminate($malformedRequest, $malformedResponse);

fwrite(STDOUT, "Final Shift Close runtime DB binding attestation control-plane regression passed for {$mode}.\n");
