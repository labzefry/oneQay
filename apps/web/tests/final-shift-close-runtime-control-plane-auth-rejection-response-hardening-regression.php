<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingTokenMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Sprint140 Final Shift Close auth rejection response hardening regression failed: '.$case,
        );
    }
};

$environment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED' => 'false',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN' => '',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_ENABLED' => 'false',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_TOKEN' => '',
];

foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$expectedToken = str_repeat('T', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$wrongToken = str_repeat('W', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$invalidExpectedToken = 'short';
$syntheticInternalPath = '/tmp/oneqay-sprint140-private-runtime-binding.json';

$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($expectedToken),
    'AUTH-RESP-001 expected token is canonical-valid',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($wrongToken),
    'AUTH-RESP-002 mismatched bearer is structurally canonical-valid',
);
$assert($expectedToken !== $wrongToken, 'AUTH-RESP-003 expected and mismatched fixtures differ');
$assert(
    ! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($invalidExpectedToken),
    'AUTH-RESP-004 invalid expected-token fixture remains canonically invalid',
);

$makeRequest = static function (?string $authorization) use ($syntheticInternalPath): Request {
    $server = [
        'REMOTE_ADDR' => '127.0.0.140',
    ];
    if ($authorization !== null) {
        $server['HTTP_AUTHORIZATION'] = $authorization;
    }

    return Request::create(
        '/internal/final-shift-close/sprint140-auth-rejection-probe',
        'GET',
        ['internal_path' => $syntheticInternalPath],
        [],
        [],
        $server,
    );
};

$assertHardenedRejection = static function (
    Response $response,
    int $expectedStatus,
    string $case,
) use ($assert, $expectedToken, $wrongToken, $syntheticInternalPath): void {
    $assert($response->getStatusCode() === $expectedStatus, $case.' status');
    $assert((string) $response->getContent() === '', $case.' body is empty');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    $assert(str_contains($cacheControl, 'no-store'), $case.' cache-control contains no-store');
    $assert(str_contains($cacheControl, 'private'), $case.' cache-control contains private');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma is no-cache');
    $assert(
        $response->headers->get('X-Content-Type-Options') === 'nosniff',
        $case.' content-type sniffing is disabled',
    );
    $assert(
        $response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        $case.' robot indexing is excluded',
    );

    $surface = (string) $response->getContent().json_encode(
        $response->headers->all(),
        JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    );
    $assert(! str_contains($surface, $expectedToken), $case.' expected bearer is not reflected');
    $assert(! str_contains($surface, $wrongToken), $case.' wrong bearer is not reflected');
    $assert(! str_contains($surface, $syntheticInternalPath), $case.' synthetic internal path is not reflected');
};

$nextInvocations = 0;
$next = static function (Request $request) use (&$nextInvocations): Response {
    $nextInvocations++;

    return new Response('', 204);
};

$materializationInvalidExpected = new RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware(
    $invalidExpectedToken,
);
$assertHardenedRejection(
    $materializationInvalidExpected->handle($makeRequest('Bearer '.$expectedToken), $next),
    503,
    'AUTH-RESP-005 materialization invalid expected token',
);
$assert($nextInvocations === 0, 'AUTH-RESP-006 materialization invalid expected token never reaches next');

$materialization = new RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware($expectedToken);
$assertHardenedRejection(
    $materialization->handle($makeRequest(null), $next),
    401,
    'AUTH-RESP-007 materialization missing bearer',
);
$assertHardenedRejection(
    $materialization->handle($makeRequest('Bearer short'), $next),
    401,
    'AUTH-RESP-008 materialization malformed bearer',
);
$assertHardenedRejection(
    $materialization->handle($makeRequest('Bearer '.$wrongToken), $next),
    401,
    'AUTH-RESP-009 materialization mismatched bearer',
);
$assert($nextInvocations === 0, 'AUTH-RESP-010 materialization rejections never reach next');

$materializationAllowed = $materialization->handle($makeRequest('Bearer '.$expectedToken), $next);
$assert($materializationAllowed->getStatusCode() === 204, 'AUTH-RESP-011 materialization matching bearer reaches next');
$assert($nextInvocations === 1, 'AUTH-RESP-012 materialization matching bearer invokes next once');

$dbInvalidExpected = new RequireFinalShiftCloseRuntimeBindingTokenMiddleware($invalidExpectedToken);
$assertHardenedRejection(
    $dbInvalidExpected->handle($makeRequest('Bearer '.$expectedToken), $next),
    404,
    'AUTH-RESP-013 DB attestation invalid expected token remains cloaked',
);
$assert($nextInvocations === 1, 'AUTH-RESP-014 DB invalid expected token never reaches next');

$dbAttestation = new RequireFinalShiftCloseRuntimeBindingTokenMiddleware($expectedToken);
$assertHardenedRejection(
    $dbAttestation->handle($makeRequest(null), $next),
    404,
    'AUTH-RESP-015 DB attestation missing bearer remains cloaked',
);
$assertHardenedRejection(
    $dbAttestation->handle($makeRequest('Bearer short'), $next),
    404,
    'AUTH-RESP-016 DB attestation malformed bearer remains cloaked',
);
$assertHardenedRejection(
    $dbAttestation->handle($makeRequest('Bearer '.$wrongToken), $next),
    404,
    'AUTH-RESP-017 DB attestation mismatched bearer remains cloaked',
);
$assert($nextInvocations === 1, 'AUTH-RESP-018 DB rejection cases never reach next');

$dbAllowed = $dbAttestation->handle($makeRequest('Bearer '.$expectedToken), $next);
$assert($dbAllowed->getStatusCode() === 204, 'AUTH-RESP-019 DB matching bearer reaches next');
$assert($nextInvocations === 2, 'AUTH-RESP-020 exactly two matching-bearer positive paths reached next');

$assert(
    $app->resolved(App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter::class) === false,
    'AUTH-RESP-021 production manifest writer remains unresolved',
);
$assert(
    $app->resolved(App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader::class) === false,
    'AUTH-RESP-022 production database identity reader remains unresolved',
);
$assert(
    $app->resolved(App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController::class) === false,
    'AUTH-RESP-023 materialization controller remains unresolved',
);
$assert(
    $app->resolved(App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController::class) === false,
    'AUTH-RESP-024 DB attestation controller remains unresolved',
);

fwrite(STDOUT, "Sprint140 Final Shift Close auth rejection response hardening regression: OK\n");
