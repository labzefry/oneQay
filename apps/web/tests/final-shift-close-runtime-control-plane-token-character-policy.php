<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
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
        throw new RuntimeException('Final Shift Close runtime control-plane token policy regression failed: '.$case);
    }
};

$invalidColonToken = str_repeat('a', 31).':';

if ($mode === 'regex-matrix') {
    foreach (['a', 'Z', '0', '.', '_', '~', '+', '=', '/', '-'] as $character) {
        $assert(
            FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(str_repeat($character, 32)),
            'POLICY-001 allowed character '.$character,
        );
    }

    $assert(
        FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(str_repeat('a', 512)),
        'POLICY-002 maximum length accepted',
    );
    $assert(
        ! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(str_repeat('a', 31)),
        'POLICY-003 length 31 rejected',
    );
    $assert(
        ! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(str_repeat('a', 513)),
        'POLICY-004 length 513 rejected',
    );

    foreach ([':', ';', '<', '>', '@', ' ', "\t"] as $character) {
        $assert(
            ! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(str_repeat($character, 32)),
            'POLICY-005 rejected character '.json_encode($character),
        );
    }

    $validBearer = 'Bearer '.str_repeat('a', 32);
    $assert(
        FinalShiftCloseRuntimeControlPlaneTokenPolicy::parseBearerCredential($validBearer) === str_repeat('a', 32),
        'POLICY-006 canonical bearer accepted',
    );
    foreach ([null, '', 'bearer '.str_repeat('a', 32), 'Bearer  '.str_repeat('a', 32), 'Basic '.str_repeat('a', 32), 'Bearer '.str_repeat(':', 32)] as $authorization) {
        $assert(
            FinalShiftCloseRuntimeControlPlaneTokenPolicy::parseBearerCredential($authorization) === null,
            'POLICY-007 malformed or invalid bearer rejected',
        );
    }

    fwrite(STDOUT, "Final Shift Close canonical token policy matrix: OK\n");
    exit(0);
}

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$bootstrapRequest = Request::create('/health/live', 'GET');
$bootstrapResponse = $kernel->handle($bootstrapRequest);
$assert($bootstrapResponse->getStatusCode() === 200, 'POLICY-008 application bootstrap remains healthy');
$kernel->terminate($bootstrapRequest, $bootstrapResponse);

$materializationReject = static function (
    string $expectedToken,
    ?string $authorization,
    int $expectedStatus,
    string $case,
) use ($assert): void {
    $middleware = new RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware($expectedToken);
    $request = Request::create('/_ci/token-policy', 'POST', server: $authorization === null ? [] : [
        'HTTP_AUTHORIZATION' => $authorization,
    ]);
    $nextInvoked = false;

    try {
        $middleware->handle($request, static function () use (&$nextInvoked) {
            $nextInvoked = true;

            return response('', 204);
        });
    } catch (HttpExceptionInterface $exception) {
        $assert($exception->getStatusCode() === $expectedStatus, $case.' disposition');
        $assert($nextInvoked === false, $case.' next/controller path not invoked');

        return;
    }

    throw new RuntimeException($case.' was not rejected.');
};

$dbReject = static function (
    string $expectedToken,
    ?string $authorization,
    string $case,
) use ($assert): void {
    $middleware = new RequireFinalShiftCloseRuntimeBindingTokenMiddleware($expectedToken);
    $request = Request::create('/_ci/token-policy', 'GET', server: $authorization === null ? [] : [
        'HTTP_AUTHORIZATION' => $authorization,
    ]);
    $nextInvoked = false;
    $response = $middleware->handle($request, static function () use (&$nextInvoked) {
        $nextInvoked = true;

        return response('', 204);
    });

    $assert($response->getStatusCode() === 404, $case.' cloaked HTTP 404');
    $assert($response->headers->get('Cache-Control') === 'no-store, private', $case.' no-store cache control');
    $assert($nextInvoked === false, $case.' next/controller path not invoked');
};

if ($mode === 'materialization-invalid-expected') {
    $materializationReject($invalidColonToken, null, 503, 'POLICY-009 materialization invalid expected token');
    fwrite(STDOUT, "Final Shift Close materialization invalid expected token disposition: OK\n");
    exit(0);
}

if ($mode === 'materialization-missing-bearer') {
    $materializationReject(str_repeat('a', 32), null, 401, 'POLICY-010 materialization missing bearer');
    fwrite(STDOUT, "Final Shift Close materialization missing bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'materialization-malformed-bearer') {
    $materializationReject(str_repeat('a', 32), 'Basic '.str_repeat('a', 32), 401, 'POLICY-011 materialization malformed bearer');
    fwrite(STDOUT, "Final Shift Close materialization malformed bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'materialization-invalid-bearer') {
    $materializationReject(str_repeat('a', 32), 'Bearer '.str_repeat(':', 32), 401, 'POLICY-012 materialization invalid bearer');
    fwrite(STDOUT, "Final Shift Close materialization invalid bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'materialization-mismatched-bearer') {
    $materializationReject(str_repeat('a', 32), 'Bearer '.str_repeat('b', 32), 401, 'POLICY-013 materialization mismatched bearer');
    fwrite(STDOUT, "Final Shift Close materialization mismatched bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'db-invalid-expected') {
    $dbReject($invalidColonToken, null, 'POLICY-014 DB invalid expected token');
    fwrite(STDOUT, "Final Shift Close DB invalid expected token disposition: OK\n");
    exit(0);
}

if ($mode === 'db-missing-bearer') {
    $dbReject(str_repeat('a', 32), null, 'POLICY-015 DB missing bearer');
    fwrite(STDOUT, "Final Shift Close DB missing bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'db-malformed-bearer') {
    $dbReject(str_repeat('a', 32), 'Basic '.str_repeat('a', 32), 'POLICY-016 DB malformed bearer');
    fwrite(STDOUT, "Final Shift Close DB malformed bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'db-invalid-bearer') {
    $dbReject(str_repeat('a', 32), 'Bearer '.str_repeat(':', 32), 'POLICY-017 DB invalid bearer');
    fwrite(STDOUT, "Final Shift Close DB invalid bearer disposition: OK\n");
    exit(0);
}

if ($mode === 'db-mismatched-bearer') {
    $dbReject(str_repeat('a', 32), 'Bearer '.str_repeat('b', 32), 'POLICY-018 DB mismatched bearer');
    fwrite(STDOUT, "Final Shift Close DB mismatched bearer disposition: OK\n");
    exit(0);
}

$expectedRoute = match ($mode) {
    'materialization-valid-hyphen',
    'materialization-route-present' => 'internal.final-shift-close.runtime-binding-manifest.materialize',
    'materialization-invalid-colon',
    'materialization-route-absent' => null,
    'db-valid-hyphen',
    'db-route-present' => 'internal.final-shift-close.runtime-db-binding-attestation',
    'db-invalid-colon',
    'db-route-absent' => null,
    default => throw new InvalidArgumentException('Unknown token policy qualification mode.'),
};

$router = $app->make('router')->getRoutes();
if (in_array($mode, ['materialization-valid-hyphen', 'materialization-route-present'], true)) {
    $assert($router->getByName($expectedRoute) !== null, 'POLICY-019 valid token registers materialization route');
} elseif (in_array($mode, ['materialization-invalid-colon', 'materialization-route-absent'], true)) {
    $assert($router->getByName('internal.final-shift-close.runtime-binding-manifest.materialize') === null, 'POLICY-020 invalid/disabled materialization route remains absent');
} elseif (in_array($mode, ['db-valid-hyphen', 'db-route-present'], true)) {
    $assert($router->getByName($expectedRoute) !== null, 'POLICY-021 valid token registers DB attestation route');
} else {
    $assert($router->getByName('internal.final-shift-close.runtime-db-binding-attestation') === null, 'POLICY-022 invalid/disabled DB attestation route remains absent');
}

fwrite(STDOUT, "Final Shift Close runtime control-plane token policy passed for {$mode}.\n");
