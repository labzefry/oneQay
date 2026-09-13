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
        throw new RuntimeException('Sprint147 throttle rejection metadata identity regression failed: '.$case);
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

$makeRequest = static function (string $method, string $path, string $name, array $methods, string $action): Request {
    $request = Request::create('/'.$path, $method);
    $route = new Route($methods, $path, ['uses' => $action, 'controller' => $action]);
    $route->name($name);
    $request->setRouteResolver(static fn (): Route => $route);

    return $request;
};

$canonicalHeaders = static fn (string $limit): array => [
    'Retry-After' => '60',
    'X-RateLimit-Limit' => $limit,
    'X-RateLimit-Remaining' => '0',
    'X-RateLimit-Reset' => '1999999999',
    'X-Sprint147-Probe' => 'preserved',
];

$throttled = static function (string $limit, string $body, array $mutations = []) use ($canonicalHeaders): Closure {
    $headers = $canonicalHeaders($limit);

    foreach ($mutations as $name => $value) {
        if ($value === null) {
            unset($headers[$name]);
            continue;
        }

        $headers[$name] = $value;
    }

    return static fn (Request $request): Response => new Response($body, 429, $headers);
};

$frameworkOwned = static function (Response $response, string $body, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === $body, $case.' body preserved');
    $assert($response->headers->get('X-Sprint147-Probe') === 'preserved', $case.' marker preserved');
    $assert($response->headers->get('X-Robots-Tag') === null, $case.' no hardening');
};

$hardened = static function (Response $response, string $limit, string $case) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status');
    $assert((string) $response->getContent() === '', $case.' empty body');
    $assert($response->headers->get('X-RateLimit-Limit') === $limit, $case.' canonical limit preserved');
    $assert($response->headers->get('X-RateLimit-Remaining') === '0', $case.' zero remaining preserved');
    $assert($response->headers->get('Retry-After') === '60', $case.' retry-after preserved');
    $assert($response->headers->get('X-RateLimit-Reset') === '1999999999', $case.' reset preserved');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'no-store'), $case.' no-store');
    $assert(str_contains((string) $response->headers->get('Cache-Control'), 'private'), $case.' private');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff');
    $assert($response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive', $case.' robots');
    $assert($response->headers->get('X-Sprint147-Probe') === 'preserved', $case.' marker preserved');
};

$invalidMetadataCases = [
    ['remaining-missing', ['X-RateLimit-Remaining' => null]],
    ['remaining-nonzero', ['X-RateLimit-Remaining' => '1']],
    ['remaining-numeric-alias', ['X-RateLimit-Remaining' => '00']],
    ['retry-after-missing', ['Retry-After' => null]],
    ['retry-after-empty', ['Retry-After' => '']],
    ['retry-after-nondigit', ['Retry-After' => 'soon']],
    ['reset-missing', ['X-RateLimit-Reset' => null]],
    ['reset-empty', ['X-RateLimit-Reset' => '']],
    ['reset-nondigit', ['X-RateLimit-Reset' => 'later']],
];

$materializationRequest = $makeRequest(
    'POST',
    $materializationPath,
    $materializationName,
    ['POST'],
    $materializationActionName,
);

foreach ($invalidMetadataCases as [$label, $mutations]) {
    $body = 'materialization-'.$label;
    $frameworkOwned(
        $middleware->handle($materializationRequest, $throttled('1', $body, $mutations)),
        $body,
        'METADATA-ID-MATERIALIZATION-'.$label,
    );
}

$hardened(
    $middleware->handle($materializationRequest, $throttled('1', 'materialization-canonical-metadata')),
    '1',
    'METADATA-ID-MATERIALIZATION-CANONICAL',
);

$dbGetRequest = $makeRequest(
    'GET',
    $dbPath,
    $dbName,
    ['GET', 'HEAD'],
    $dbActionName,
);

foreach ($invalidMetadataCases as [$label, $mutations]) {
    $body = 'db-get-'.$label;
    $frameworkOwned(
        $middleware->handle($dbGetRequest, $throttled('2', $body, $mutations)),
        $body,
        'METADATA-ID-DB-GET-'.$label,
    );
}

$hardened(
    $middleware->handle($dbGetRequest, $throttled('2', 'db-get-canonical-metadata')),
    '2',
    'METADATA-ID-DB-GET-CANONICAL',
);

$dbHeadRequest = $makeRequest(
    'HEAD',
    $dbPath,
    $dbName,
    ['GET', 'HEAD'],
    $dbActionName,
);

foreach ($invalidMetadataCases as [$label, $mutations]) {
    $body = 'db-head-'.$label;
    $frameworkOwned(
        $middleware->handle($dbHeadRequest, $throttled('2', $body, $mutations)),
        $body,
        'METADATA-ID-DB-HEAD-'.$label,
    );
}

$hardened(
    $middleware->handle($dbHeadRequest, $throttled('2', 'db-head-canonical-metadata')),
    '2',
    'METADATA-ID-DB-HEAD-CANONICAL',
);

echo "Final Shift Close canonical throttle rejection metadata identity response hardening regression passed.\n";
