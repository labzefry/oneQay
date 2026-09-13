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
        throw new RuntimeException('Sprint145 action identity regression failed: '.$case);
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

$make = static function (string $method, string $path, string $name, array $methods, mixed $action): Request {
    $request = Request::create('/'.$path, $method);
    $route = new Route($methods, $path, $action);
    $route->name($name);
    $request->setRouteResolver(static fn (): Route => $route);
    return $request;
};

$throttled = static fn (int $limit, string $body): Closure => static fn (Request $request): Response => new Response(
    $body,
    429,
    [
        'Retry-After' => '60',
        'X-RateLimit-Limit' => (string) $limit,
        'X-RateLimit-Remaining' => '0',
        'X-RateLimit-Reset' => '1999999999',
        'X-Sprint145-Probe' => 'preserved',
    ],
);

$frameworkOwned = static function (Response $response, string $body, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === $body, $case.' body');
    $assert($response->headers->get('X-Sprint145-Probe') === 'preserved', $case.' marker');
    $assert($response->headers->get('X-Robots-Tag') === null, $case.' no hardening');
};

$hardened = static function (Response $response, int $limit, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === '', $case.' empty body');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'no-store'), $case.' no-store');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'private'), $case.' private');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff');
    $assert($response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive', $case.' robots');
    $assert($response->headers->get('Retry-After') === '60', $case.' retry-after');
    $assert((int) $response->headers->get('X-RateLimit-Limit') === $limit, $case.' limit');
    $assert($response->headers->get('X-Sprint145-Probe') === 'preserved', $case.' marker preserved');
};

$body = 'materialization-noncanonical-action';
$frameworkOwned(
    $middleware->handle($make('POST', $materializationPath, $materializationName, ['POST'], static fn (): Response => new Response('', 204)), $throttled(1, $body)),
    $body,
    'ACTION-ID-001',
);

$body = 'db-get-noncanonical-action';
$frameworkOwned(
    $middleware->handle($make('GET', $dbPath, $dbName, ['GET', 'HEAD'], static fn (): Response => new Response('', 204)), $throttled(2, $body)),
    $body,
    'ACTION-ID-002',
);

$body = 'db-head-noncanonical-action';
$frameworkOwned(
    $middleware->handle($make('HEAD', $dbPath, $dbName, ['GET', 'HEAD'], static fn (): Response => new Response('', 204)), $throttled(2, $body)),
    $body,
    'ACTION-ID-003',
);

$materializationRequest = $make(
    'POST',
    $materializationPath,
    $materializationName,
    ['POST'],
    $controllerAction($materializationActionName),
);
$assert($materializationRequest->route() instanceof Route, 'ACTION-ID-004 route resolves');
$assert($materializationRequest->route()->getActionName() === $materializationActionName, 'ACTION-ID-005 exact action');
$hardened($middleware->handle($materializationRequest, $throttled(1, 'canonical')), 1, 'ACTION-ID-006');

$dbGetRequest = $make('GET', $dbPath, $dbName, ['GET', 'HEAD'], $controllerAction($dbActionName));
$assert($dbGetRequest->route() instanceof Route, 'ACTION-ID-007 route resolves');
$assert($dbGetRequest->route()->getActionName() === $dbActionName, 'ACTION-ID-008 exact action');
$hardened($middleware->handle($dbGetRequest, $throttled(2, 'canonical')), 2, 'ACTION-ID-009');

$dbHeadRequest = $make('HEAD', $dbPath, $dbName, ['GET', 'HEAD'], $controllerAction($dbActionName));
$assert($dbHeadRequest->route() instanceof Route, 'ACTION-ID-010 route resolves');
$assert($dbHeadRequest->route()->getActionName() === $dbActionName, 'ACTION-ID-011 exact action');
$hardened($middleware->handle($dbHeadRequest, $throttled(2, 'canonical')), 2, 'ACTION-ID-012');

echo "Final Shift Close canonical action identity throttle response hardening regression passed.\n";
