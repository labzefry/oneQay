<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Sprint144 Final Shift Close named-route identity throttle response hardening regression failed: '.$case,
        );
    }
};

$middleware = new HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware();

$materializationPath = 'internal/final-shift-close/runtime-binding-manifest/materialize';
$dbAttestationPath = 'internal/final-shift-close/runtime-db-binding-attestation';
$materializationRouteName = 'internal.final-shift-close.runtime-binding-manifest.materialize';
$dbAttestationRouteName = 'internal.final-shift-close.runtime-db-binding-attestation';

$makeRequest = static function (
    string $method,
    string $path,
    ?string $routeName,
    array $routeMethods,
): Request {
    $request = Request::create('/'.$path, $method);

    if ($routeName !== null) {
        $route = new Route(
            $routeMethods,
            $path,
            static fn (): Response => new Response('', 204),
        );
        $route->name($routeName);
        $request->setRouteResolver(static fn (): Route => $route);
    }

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
            'X-Sprint144-Framework-Probe' => 'preserved',
        ],
    );
};

$assertFrameworkOwned = static function (Response $response, string $body, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status remains 429');
    $assert((string) $response->getContent() === $body, $case.' framework body remains untouched');
    $assert(
        $response->headers->get('X-Sprint144-Framework-Probe') === 'preserved',
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
        $response->headers->get('X-Sprint144-Framework-Probe') === 'preserved',
        $case.' unrelated framework header remains preserved',
    );
};

$nonCanonicalMaterializationBody = 'framework-owned-materialization-shadow';
$nonCanonicalMaterialization = $middleware->handle(
    $makeRequest(
        'POST',
        $materializationPath,
        'internal.sprint144.shadow.materialization',
        ['POST'],
    ),
    $frameworkThrottle(1, $nonCanonicalMaterializationBody),
);
$assertFrameworkOwned(
    $nonCanonicalMaterialization,
    $nonCanonicalMaterializationBody,
    'ROUTE-ID-001 same-path POST with noncanonical route name',
);

$nonCanonicalDbGetBody = 'framework-owned-db-get-shadow';
$nonCanonicalDbGet = $middleware->handle(
    $makeRequest(
        'GET',
        $dbAttestationPath,
        'internal.sprint144.shadow.db-attestation',
        ['GET', 'HEAD'],
    ),
    $frameworkThrottle(2, $nonCanonicalDbGetBody),
);
$assertFrameworkOwned(
    $nonCanonicalDbGet,
    $nonCanonicalDbGetBody,
    'ROUTE-ID-002 same-path GET with noncanonical route name',
);

$nonCanonicalDbHeadBody = 'framework-owned-db-head-shadow';
$nonCanonicalDbHead = $middleware->handle(
    $makeRequest(
        'HEAD',
        $dbAttestationPath,
        'internal.sprint144.shadow.db-attestation',
        ['GET', 'HEAD'],
    ),
    $frameworkThrottle(2, $nonCanonicalDbHeadBody),
);
$assertFrameworkOwned(
    $nonCanonicalDbHead,
    $nonCanonicalDbHeadBody,
    'ROUTE-ID-003 same-path HEAD with noncanonical route name',
);

$unresolvedRouteBody = 'framework-owned-unresolved-route';
$unresolvedRoute = $middleware->handle(
    $makeRequest('GET', $dbAttestationPath, null, ['GET', 'HEAD']),
    $frameworkThrottle(2, $unresolvedRouteBody),
);
$assertFrameworkOwned(
    $unresolvedRoute,
    $unresolvedRouteBody,
    'ROUTE-ID-004 same-path request without resolved canonical route',
);

$canonicalMaterialization = $middleware->handle(
    $makeRequest(
        'POST',
        $materializationPath,
        $materializationRouteName,
        ['POST'],
    ),
    $frameworkThrottle(1, 'canonical-materialization-framework-body'),
);
$assertCanonicallyHardened(
    $canonicalMaterialization,
    1,
    'ROUTE-ID-005 canonical materialization POST route identity',
);

$canonicalDbGet = $middleware->handle(
    $makeRequest(
        'GET',
        $dbAttestationPath,
        $dbAttestationRouteName,
        ['GET', 'HEAD'],
    ),
    $frameworkThrottle(2, 'canonical-db-get-framework-body'),
);
$assertCanonicallyHardened(
    $canonicalDbGet,
    2,
    'ROUTE-ID-006 canonical DB-attestation GET route identity',
);

$canonicalDbHead = $middleware->handle(
    $makeRequest(
        'HEAD',
        $dbAttestationPath,
        $dbAttestationRouteName,
        ['GET', 'HEAD'],
    ),
    $frameworkThrottle(2, 'canonical-db-head-framework-body'),
);
$assertCanonicallyHardened(
    $canonicalDbHead,
    2,
    'ROUTE-ID-007 canonical DB-attestation HEAD route identity',
);

echo "Final Shift Close canonical named-route identity throttle response hardening regression passed.\n";
