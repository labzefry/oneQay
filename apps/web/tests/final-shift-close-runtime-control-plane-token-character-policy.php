<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingTokenMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$mode = $argv[1] ?? '';
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Final Shift Close runtime control-plane token character policy regression failed: '.$case);
    }
};

$invalidColonToken = str_repeat('a', 31).':';
$pattern = '/\A[A-Za-z0-9._~+=\/-]{32,512}\z/D';

if ($mode === 'regex-matrix') {
    foreach (['a', 'Z', '0', '.', '_', '~', '+', '=', '/', '-'] as $character) {
        $assert(preg_match($pattern, str_repeat($character, 32)) === 1, 'CHAR-001 allowed character '.$character);
    }
    foreach ([':', ';', '<', '>', '@', ' ', "\t"] as $character) {
        $assert(preg_match($pattern, str_repeat($character, 32)) !== 1, 'CHAR-002 rejected character '.json_encode($character));
    }

    fwrite(STDOUT, "Final Shift Close token character regex matrix: OK\n");
    exit(0);
}

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$bootstrapRequest = Request::create('/health/live', 'GET');
$bootstrapResponse = $kernel->handle($bootstrapRequest);
$assert($bootstrapResponse->getStatusCode() === 200, 'CHAR-003 application bootstrap remains healthy');
$kernel->terminate($bootstrapRequest, $bootstrapResponse);

if ($mode === 'materialization-invalid-expected') {
    $middleware = new RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware($invalidColonToken);
    $request = Request::create('/_ci/token-character-policy', 'POST');
    try {
        $middleware->handle($request, static fn () => response('', 204));
    } catch (HttpExceptionInterface $exception) {
        $assert($exception->getStatusCode() === 503, 'CHAR-004 materialization invalid expected token remains HTTP 503');
        fwrite(STDOUT, "Final Shift Close materialization invalid expected token disposition: OK\n");
        exit(0);
    }

    throw new RuntimeException('CHAR-004 materialization invalid expected token was not rejected.');
}

if ($mode === 'db-invalid-expected') {
    $middleware = new RequireFinalShiftCloseRuntimeBindingTokenMiddleware($invalidColonToken);
    $request = Request::create('/_ci/token-character-policy', 'GET');
    $response = $middleware->handle($request, static fn () => response('', 204));
    $assert($response->getStatusCode() === 404, 'CHAR-005 DB invalid expected token remains cloaked HTTP 404');
    fwrite(STDOUT, "Final Shift Close DB invalid expected token disposition: OK\n");
    exit(0);
}

if ($mode === 'materialization-invalid-bearer') {
    $middleware = new RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware(str_repeat('a', 32));
    $request = Request::create('/_ci/token-character-policy', 'POST', server: [
        'HTTP_AUTHORIZATION' => 'Bearer '.str_repeat(':', 32),
    ]);
    try {
        $middleware->handle($request, static fn () => response('', 204));
    } catch (HttpExceptionInterface $exception) {
        $assert($exception->getStatusCode() === 401, 'CHAR-006 materialization invalid bearer remains HTTP 401');
        fwrite(STDOUT, "Final Shift Close materialization invalid bearer disposition: OK\n");
        exit(0);
    }

    throw new RuntimeException('CHAR-006 materialization invalid bearer was not rejected.');
}

if ($mode === 'db-invalid-bearer') {
    $middleware = new RequireFinalShiftCloseRuntimeBindingTokenMiddleware(str_repeat('a', 32));
    $request = Request::create('/_ci/token-character-policy', 'GET', server: [
        'HTTP_AUTHORIZATION' => 'Bearer '.str_repeat(':', 32),
    ]);
    $response = $middleware->handle($request, static fn () => response('', 204));
    $assert($response->getStatusCode() === 404, 'CHAR-007 DB invalid bearer remains cloaked HTTP 404');
    fwrite(STDOUT, "Final Shift Close DB invalid bearer disposition: OK\n");
    exit(0);
}

$expectedRoute = match ($mode) {
    'materialization-valid-hyphen' => 'internal.final-shift-close.runtime-binding-manifest.materialize',
    'materialization-invalid-colon' => null,
    'db-valid-hyphen' => 'internal.final-shift-close.runtime-db-binding-attestation',
    'db-invalid-colon' => null,
    default => throw new InvalidArgumentException('Unknown token character policy qualification mode.'),
};

$router = $app->make('router')->getRoutes();
if ($mode === 'materialization-valid-hyphen') {
    $assert($router->getByName($expectedRoute) !== null, 'CHAR-008 hyphen token registers materialization route');
} elseif ($mode === 'materialization-invalid-colon') {
    $assert($router->getByName('internal.final-shift-close.runtime-binding-manifest.materialize') === null, 'CHAR-009 colon token cannot register materialization route');
} elseif ($mode === 'db-valid-hyphen') {
    $assert($router->getByName($expectedRoute) !== null, 'CHAR-010 hyphen token registers DB attestation route');
} else {
    $assert($router->getByName('internal.final-shift-close.runtime-db-binding-attestation') === null, 'CHAR-011 colon token cannot register DB attestation route');
}

fwrite(STDOUT, "Final Shift Close runtime control-plane token character policy passed for {$mode}.\n");
