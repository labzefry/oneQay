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
        throw new RuntimeException(
            'Sprint145 Final Shift Close action identity throttle response hardening regression failed: '.$case,
        );
    }
};

$middleware = new HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware();

$materializationPath = 'internal/final-shift-close/runtime-binding-manifest/materialize';
$dbAttestationPath = 'internal/final-shift-close/runtime-db-binding-attestation';
$materializationRouteName = 'internal.final-shift-close.runtime-binding-manifest.materialize';
$dbAttestationRouteName = 'internal.final-shift-close.runtime-db-binding-attestation';
$materializationAction = FinalShiftCloseRuntimeBindingManifestMaterializationController::class.'@__invoke';
$dbAttestationAction = FinalShiftCloseRuntimeDbBindingAttestationController::class.'@__invoke';

$makeRequest = static function (
    string $method,
    string $path,
    string $routeName,
    array $routeMethods,
    mixed $routeAction,
): Request {
    $request = Request::create('/'.$path, $method);
    $route = new Route($routeMethods, $path, $routeAction);
    $route->name($routeName);
    $request->setRouteResolver(static fn (): Route => $route);

    return $request;
};

$frameworkThrottle = static function (int $limit, string $body): Closure {
    return static fn (Request $request): Response => new Response(
        $body,
        429,
        [
            'Retry-After' => '60',
            'X-RateLimit-Limit' => (string) $limit,
            'X-RateLimit-Remaining' => '0',
            'X-RateLimit-Reset' => '1999999999',
            'X-Sprint145-Framework-Probe' => 'preserved',
        ],
    );
};

$assertFrameworkOwned = static function (Response $response, string $body, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status remains 429');
    $assert((string) $response->getContent() === $body, $case.' framework body remains untouched');
    $assert(
        $response->headers->get('X-Sprint145-Framework-Probe') === 'preserved',
        $case.' framework marker remains preserved',
    );
    $assert($response->headers->get('X-Robots-Tag') === null, $case.' hardening header is not injected');
};

$assertCanonicallyHardened = static function (Response $response, int $limit, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status remains 429');
    $assert((string) $response->getContent() === '', $case.' body is empty');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    $assert(str_contains($cacheControl, 'no-store'), $case.' cache-control contains no-store');
    $assert(str_contains($cacheControl, 'private'), $case.' cache-control contains private');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma is no-cache');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff is present');
    $assert(
        $response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        $case.' robot indexing is excluded',
    );
    $assert($response->headers->get('Retry-After') === '60', $case.' Retry-After remains preserved');
    $assert((int) $response->headers->get('X-RateLimit-Limit') === $limit, $case.' rate limit remains preserved');
    $assert(
        $response->headers->get('X-Sprint145-Framework-Probe') === 'preserved',
        $case.' unrelated framework header remains preserved',
    );
};

$shadowMaterializationBody = 'framework-owned-materialization-noncanonical-action';
$shadowMaterialization = $middleware->handle(
    $makeRequest(
        'POST',
        $materializationPath,
        $materializationRouteName,
        ['POST'],
        static fn (): Response => new Response('', 204),
    ),
    $frameworkThrottle(1, $shadowMaterializationBody),
);
$assertFrameworkOwned(
    $shadowMaterialization,
    $shadowMaterializationBody,
    'ACTION-ID-001 canonical materialization name/method/path with noncanonical action',
);

$shadowDbGetBody = 'framework-owned-db-get-noncanonical-action';
$shadowDbGet = $middleware->handle(
    $makeRequest(
        'GET',
        $dbAttestationPath,
        $dbAttestationRouteName,
        ['GET', 'HEAD'],
        static fn (): Response => new Response('', 204),
    ),
    $frameworkThrottle(2, $shadowDbGetBody),
);
$assertFrameworkOwned(
    $shadowDbGet,
    $shadowDbGetBody,
    'ACTION-ID-002 canonical DB-attestation GET name/method/path with noncanonical action',
);

$shadowDbHeadBody = 'framework-owned-db-head-noncanonical-action';
$shadowDbHead = $middleware->handle(
    $makeRequest(
        'HEAD',
        $dbAttestationPath,
        $dbAttestationRouteName,
        ['GET', 'HEAD'],
        static fn (): Response => new Response('', 204),
    ),
    $frameworkThrottle(2, $shadowDbHeadBody),
);
$assertFrameworkOwned(
    $shadowDbHead,
    $shadowDbHeadBody,
    'ACTION-ID-003 canonical DB-attestation HEAD name/method/path with noncanonical action',
);

$canonicalMaterializationRequest = $makeRequest(
    'POST',
    $materializationPath,
    $materializationRouteName,
    ['POST'],
    $materializationAction,
);
$materializationResolvedRoute = $canonicalMaterializationRequest->route();
$assert($materializationResolvedRoute instanceof Route, 'ACTION-ID-004 materialization canonical route resolves');
$assert(
    $materializationResolvedRoute->getActionName() === $materializationAction,
    'ACTION-ID-005 materialization canonical action identity is exact',
);
$canonicalMaterialization = $middleware->handle(
    $canonicalMaterializationRequest,
    $frameworkThrottle(1, 'canonical-materialization-framework-body'),
);
$assertCanonicallyHardened(
    $canonicalMaterialization,
    1,
    'ACTION-ID-006 canonical materialization route action identity',
);

$canonicalDbGetRequest = $makeRequest(
    'GET',
    $dbAttestationPath,
    $dbAttestationRouteName,
    ['GET', 'HEAD'],
    $dbAttestationAction,
);
$dbGetResolvedRoute = $canonicalDbGetRequest->route();
$assert($dbGetResolvedRoute instanceof Route, 'ACTION-ID-007 DB GET canonical route resolves');
$assert(
    $dbGetResolvedRoute->getActionName() === $dbAttestationAction,
    'ACTION-ID-008 DB GET canonical action identity is exact',
);
$canonicalDbGet = $middleware->handle(
    $canonicalDbGetRequest,
    $frameworkThrottle(2, 'canonical-db-get-framework-body'),
);
$assertCanonicallyHardened(
    $canonicalDbGet,
    2,
    'ACTION-ID-009 canonical DB-attestation GET route action identity',
);

$canonicalDbHeadRequest = $makeRequest(
    'HEAD',
    $dbAttestationPath,
    $dbAttestationRouteName,
    ['GET', 'HEAD'],
    $dbAttestationAction,
);
$dbHeadResolvedRoute = $canonicalDbHeadRequest->route();
$assert($dbHeadResolvedRoute instanceof Route, 'ACTION-ID-010 DB HEAD canonical route resolves');
$assert(
    $dbHeadResolvedRoute->getActionName() === $dbAttestationAction,
    'ACTION-ID-011 DB HEAD canonical action identity is exact',
);
$canonicalDbHead = $middleware->handle(
    $canonicalDbHeadRequest,
    $frameworkThrottle(2, 'canonical-db-head-framework-body'),
);
$assertCanonicallyHardened(
    $canonicalDbHead,
    2,
    'ACTION-ID-012 canonical DB-attestation HEAD route action identity',
);

echo "Final Shift Close canonical action identity throttle response hardening regression passed.\n";
