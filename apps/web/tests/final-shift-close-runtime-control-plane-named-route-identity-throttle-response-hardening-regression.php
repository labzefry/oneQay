<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint144 route identity regression failed: '.$case);
    }
};

$middleware = new HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware();
$materializationPath = 'internal/final-shift-close/runtime-binding-manifest/materialize';
$dbPath = 'internal/final-shift-close/runtime-db-binding-attestation';
$materializationName = 'internal.final-shift-close.runtime-binding-manifest.materialize';
$dbName = 'internal.final-shift-close.runtime-db-binding-attestation';
$materializationActionName = FinalShiftCloseRuntimeBindingManifestMaterializationController::class.'@__invoke';
$dbActionName = FinalShiftCloseRuntimeDbBindingAttestationController::class.'@__invoke';
$controllerAction = static fn (string $action): array => ['uses' => $action, 'controller' => $action];

$make = static function (string $method, string $path, ?string $name, array $methods, mixed $action = null): Request {
    $request = Request::create('/'.$path, $method);
    if ($name !== null) {
        $route = new Route($methods, $path, $action ?? static fn (): Response => new Response('', 204));
        $route->name($name);
        $request->setRouteResolver(static fn (): Route => $route);
    }
    return $request;
};

$throttled = static fn (int $limit, string $body): Closure => static fn (Request $request): Response => new Response(
    $body,
    429,
    ['Retry-After' => '60', 'X-RateLimit-Limit' => (string) $limit, 'X-Sprint144-Probe' => 'preserved'],
);

$frameworkOwned = static function (Response $response, string $body, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === $body, $case.' body');
    $assert($response->headers->get('X-Sprint144-Probe') === 'preserved', $case.' marker');
    $assert($response->headers->get('X-Robots-Tag') === null, $case.' no hardening');
};

$hardened = static function (Response $response, int $limit, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === '', $case.' empty body');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'no-store'), $case.' no-store');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff');
    $assert($response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive', $case.' robots');
    $assert((int) $response->headers->get('X-RateLimit-Limit') === $limit, $case.' limit');
};

$body = 'materialization-shadow';
$frameworkOwned(
    $middleware->handle($make('POST', $materializationPath, 'internal.sprint144.shadow.materialization', ['POST']), $throttled(1, $body)),
    $body,
    'ROUTE-ID-001',
);

$body = 'db-get-shadow';
$frameworkOwned(
    $middleware->handle($make('GET', $dbPath, 'internal.sprint144.shadow.db', ['GET', 'HEAD']), $throttled(2, $body)),
    $body,
    'ROUTE-ID-002',
);

$body = 'db-head-shadow';
$frameworkOwned(
    $middleware->handle($make('HEAD', $dbPath, 'internal.sprint144.shadow.db', ['GET', 'HEAD']), $throttled(2, $body)),
    $body,
    'ROUTE-ID-003',
);

$body = 'unresolved-route';
$frameworkOwned(
    $middleware->handle($make('GET', $dbPath, null, ['GET', 'HEAD']), $throttled(2, $body)),
    $body,
    'ROUTE-ID-004',
);

$hardened(
    $middleware->handle($make('POST', $materializationPath, $materializationName, ['POST'], $controllerAction($materializationActionName)), $throttled(1, 'canonical')),
    1,
    'ROUTE-ID-005',
);
$hardened(
    $middleware->handle($make('GET', $dbPath, $dbName, ['GET', 'HEAD'], $controllerAction($dbActionName)), $throttled(2, 'canonical')),
    2,
    'ROUTE-ID-006',
);
$hardened(
    $middleware->handle($make('HEAD', $dbPath, $dbName, ['GET', 'HEAD'], $controllerAction($dbActionName)), $throttled(2, 'canonical')),
    2,
    'ROUTE-ID-007',
);

echo "Final Shift Close canonical named-route identity throttle response hardening regression passed.\n";
