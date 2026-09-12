<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Sprint141 Final Shift Close HTTP auth rejection response regression failed: '.$case,
        );
    }
};

$expectedToken = str_repeat('T', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$wrongToken = str_repeat('W', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$syntheticInternalPath = '/tmp/oneqay-sprint141-http-auth-rejection-private.json';

$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($expectedToken),
    'HTTP-AUTH-RESP-001 expected token remains canonical-valid',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($wrongToken),
    'HTTP-AUTH-RESP-002 mismatched token remains structurally canonical-valid',
);
$assert($expectedToken !== $wrongToken, 'HTTP-AUTH-RESP-003 expected and mismatched tokens differ');
$assert(
    ! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken('short'),
    'HTTP-AUTH-RESP-004 malformed bearer payload remains canonically invalid',
);

$environment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN' => $expectedToken,
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_TOKEN' => $expectedToken,
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

$routes = $app->make('router')->getRoutes();
$materializationRoute = $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize');
$dbAttestationRoute = $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation');
$assert($materializationRoute !== null, 'HTTP-AUTH-RESP-005 materialization route is registered');
$assert($dbAttestationRoute !== null, 'HTTP-AUTH-RESP-006 DB-attestation route is registered');

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'HTTP-AUTH-RESP-007 canonical runtime manifest is absent before dispatch');

$productionWriterResolutionAttempts = 0;
$productionDatabaseReaderResolutionAttempts = 0;

$app->scoped(
    FinalShiftCloseRuntimeBindingManifestWriter::class,
    static function () use (&$productionWriterResolutionAttempts): FinalShiftCloseRuntimeBindingManifestWriter {
        $productionWriterResolutionAttempts++;
        throw new RuntimeException('Production manifest writer resolution is forbidden in Sprint141.');
    },
);
$app->scoped(
    FinalShiftCloseRuntimeDatabaseIdentityReader::class,
    static function () use (&$productionDatabaseReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
        $productionDatabaseReaderResolutionAttempts++;
        throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint141.');
    },
);

$makeMaterializationRequest = static function (
    ?string $authorization,
    string $sourceIp,
) use ($syntheticInternalPath): Request {
    $server = [
        'CONTENT_TYPE' => 'application/json',
        'REMOTE_ADDR' => $sourceIp,
    ];
    if ($authorization !== null) {
        $server['HTTP_AUTHORIZATION'] = $authorization;
    }

    return Request::create(
        '/internal/final-shift-close/runtime-binding-manifest/materialize',
        'POST',
        [],
        [],
        [],
        $server,
        json_encode([
            'operation_id' => 'sprint141-http-auth-rejection-probe',
            'expected_selection_fingerprint_sha256' => str_repeat('a', 64),
            'internal_path_probe' => $syntheticInternalPath,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );
};

$makeDbAttestationRequest = static function (
    ?string $authorization,
    string $sourceIp,
) use ($syntheticInternalPath): Request {
    $server = [
        'REMOTE_ADDR' => $sourceIp,
    ];
    if ($authorization !== null) {
        $server['HTTP_AUTHORIZATION'] = $authorization;
    }

    return Request::create(
        '/internal/final-shift-close/runtime-db-binding-attestation?internal_path_probe='.rawurlencode($syntheticInternalPath),
        'GET',
        [],
        [],
        [],
        $server,
    );
};

$assertHardenedHttpRejection = static function (
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
    $assert(! str_contains($surface, $wrongToken), $case.' mismatched bearer is not reflected');
    $assert(! str_contains($surface, $syntheticInternalPath), $case.' synthetic internal path is not reflected');
};

$dispatch = static function (Request $request) use ($kernel): Response {
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    return $response;
};

$assertHardenedHttpRejection(
    $dispatch($makeMaterializationRequest(null, '127.0.0.141')),
    401,
    'HTTP-AUTH-RESP-008 materialization missing bearer',
);
$assertHardenedHttpRejection(
    $dispatch($makeMaterializationRequest('Bearer short', '127.0.0.142')),
    401,
    'HTTP-AUTH-RESP-009 materialization malformed bearer',
);
$assertHardenedHttpRejection(
    $dispatch($makeMaterializationRequest('Bearer '.$wrongToken, '127.0.0.143')),
    401,
    'HTTP-AUTH-RESP-010 materialization mismatched bearer',
);

$assertHardenedHttpRejection(
    $dispatch($makeDbAttestationRequest(null, '127.0.1.141')),
    404,
    'HTTP-AUTH-RESP-011 DB attestation missing bearer remains cloaked',
);
$assertHardenedHttpRejection(
    $dispatch($makeDbAttestationRequest('Bearer short', '127.0.1.142')),
    404,
    'HTTP-AUTH-RESP-012 DB attestation malformed bearer remains cloaked',
);
$assertHardenedHttpRejection(
    $dispatch($makeDbAttestationRequest('Bearer '.$wrongToken, '127.0.1.143')),
    404,
    'HTTP-AUTH-RESP-013 DB attestation mismatched bearer remains cloaked',
);

$assert($productionWriterResolutionAttempts === 0, 'HTTP-AUTH-RESP-014 production manifest writer remains unresolved');
$assert($productionDatabaseReaderResolutionAttempts === 0, 'HTTP-AUTH-RESP-015 production DB identity reader remains unresolved');
$assert(
    $app->resolved(FinalShiftCloseRuntimeBindingManifestMaterializer::class) === false,
    'HTTP-AUTH-RESP-016 materializer service remains unresolved',
);
$assert(
    $app->resolved(FinalShiftCloseRuntimeDbBindingAttestation::class) === false,
    'HTTP-AUTH-RESP-017 DB-attestation service remains unresolved',
);
$assert(
    $app->resolved(FinalShiftCloseRuntimeBindingManifestMaterializationController::class) === false,
    'HTTP-AUTH-RESP-018 materialization controller remains unresolved',
);
$assert(
    $app->resolved(FinalShiftCloseRuntimeDbBindingAttestationController::class) === false,
    'HTTP-AUTH-RESP-019 DB-attestation controller remains unresolved',
);
$assert(! file_exists($canonicalManifestPath), 'HTTP-AUTH-RESP-020 canonical runtime manifest remains absent after dispatch');

fwrite(STDOUT, "Sprint141 Final Shift Close HTTP auth rejection response regression: OK\n");
