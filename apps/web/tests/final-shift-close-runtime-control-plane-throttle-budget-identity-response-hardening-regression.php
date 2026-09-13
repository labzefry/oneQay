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
        throw new RuntimeException('Sprint146 throttle budget identity regression failed: '.$case);
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

$make = static function (string $method, string $path, string $name, array $methods, array $action): Request {
    $request = Request::create('/'.$path, $method);
    $route = new Route($methods, $path, $action);
    $route->name($name);
    $request->setRouteResolver(static fn (): Route => $route);

    return $request;
};

$throttled = static fn (string $limit, string $body): Closure => static fn (Request $request): Response => new Response(
    $body,
    429,
    [
        'Retry-After' => '60',
        'X-RateLimit-Limit' => $limit,
        'X-RateLimit-Remaining' => '0',
        'X-RateLimit-Reset' => '1999999999',
        'X-Sprint146-Probe' => 'preserved',
    ],
);

$frameworkOwned = static function (Response $response, string $body, string $limit, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === $body, $case.' body preserved');
    $assert($response->headers->get('X-RateLimit-Limit') === $limit, $case.' limit preserved');
    $assert($response->headers->get('X-Sprint146-Probe') === 'preserved', $case.' marker preserved');
    $assert($response->headers->get('X-Robots-Tag') === null, $case.' no hardening');
};

$hardened = static function (Response $response, string $limit, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === '', $case.' empty body');
    $assert($response->headers->get('X-RateLimit-Limit') === $limit, $case.' canonical limit preserved');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'no-store'), $case.' no-store');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'private'), $case.' private');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff');
    $assert($response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive', $case.' robots');
    $assert($response->headers->get('Retry-After') === '60', $case.' retry-after preserved');
    $assert($response->headers->get('X-Sprint146-Probe') === 'preserved', $case.' marker preserved');
};

$materializationRequest = $make(
    'POST',
    $materializationPath,
    $materializationName,
    ['POST'],
    $controllerAction($materializationActionName),
);

foreach ([
    ['2', 'materialization-cross-budget'],
    ['99', 'materialization-arbitrary-budget'],
    ['01', 'materialization-numeric-alias-budget'],
] as [$limit, $body]) {
    $frameworkOwned(
        $middleware->handle($materializationRequest, $throttled($limit, $body)),
        $body,
        $limit,
        'BUDGET-ID-MATERIALIZATION-'.$limit,
    );
}

$hardened(
    $middleware->handle($materializationRequest, $throttled('1', 'materialization-canonical-budget')),
    '1',
    'BUDGET-ID-MATERIALIZATION-CANONICAL',
);

$dbGetRequest = $make(
    'GET',
    $dbPath,
    $dbName,
    ['GET', 'HEAD'],
    $controllerAction($dbActionName),
);

foreach ([
    ['1', 'db-get-cross-budget'],
    ['99', 'db-get-arbitrary-budget'],
    ['02', 'db-get-numeric-alias-budget'],
] as [$limit, $body]) {
    $frameworkOwned(
        $middleware->handle($dbGetRequest, $throttled($limit, $body)),
        $body,
        $limit,
        'BUDGET-ID-DB-GET-'.$limit,
    );
}

$hardened(
    $middleware->handle($dbGetRequest, $throttled('2', 'db-get-canonical-budget')),
    '2',
    'BUDGET-ID-DB-GET-CANONICAL',
);

$dbHeadRequest = $make(
    'HEAD',
    $dbPath,
    $dbName,
    ['GET', 'HEAD'],
    $controllerAction($dbActionName),
);

foreach ([
    ['1', 'db-head-cross-budget'],
    ['99', 'db-head-arbitrary-budget'],
    ['02', 'db-head-numeric-alias-budget'],
] as [$limit, $body]) {
    $frameworkOwned(
        $middleware->handle($dbHeadRequest, $throttled($limit, $body)),
        $body,
        $limit,
        'BUDGET-ID-DB-HEAD-'.$limit,
    );
}

$hardened(
    $middleware->handle($dbHeadRequest, $throttled('2', 'db-head-canonical-budget')),
    '2',
    'BUDGET-ID-DB-HEAD-CANONICAL',
);

echo "Final Shift Close canonical throttle budget identity response hardening regression passed.\n";
