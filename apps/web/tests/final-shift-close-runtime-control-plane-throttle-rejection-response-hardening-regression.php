<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentity;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Middleware\HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware;
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
            'Sprint142 Final Shift Close throttle rejection response hardening regression failed: '.$case,
        );
    }
};

$validToken = str_repeat('T', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken),
    'THR-RESP-001 expected bearer fixture remains canonical-valid',
);

$environment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN' => $validToken,
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_TOKEN' => $validToken,
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

$router = $app->make('router');
$routes = $router->getRoutes();
$materializationRoute = $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize');
$dbAttestationRoute = $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation');
$assert($materializationRoute !== null, 'THR-RESP-002 materialization route is registered');
$assert($dbAttestationRoute !== null, 'THR-RESP-003 DB-attestation route is registered');

$materializationMiddleware = array_values($materializationRoute->middleware());
$materializationAuthIndex = array_search(
    RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class,
    $materializationMiddleware,
    true,
);
$materializationThrottleIndex = array_search('throttle:1,1', $materializationMiddleware, true);
$assert($materializationAuthIndex !== false, 'THR-RESP-004 materialization auth middleware remains attached');
$assert($materializationThrottleIndex !== false, 'THR-RESP-005 materialization throttle remains exactly 1,1');
$assert(
    $materializationAuthIndex < $materializationThrottleIndex,
    'THR-RESP-006 materialization auth remains before throttle',
);
$assert(
    ! in_array(HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware::class, $materializationMiddleware, true),
    'THR-RESP-007 materialization route middleware stack is not rewritten by Sprint142',
);

$dbMiddleware = array_values($dbAttestationRoute->middleware());
$dbAuthIndex = array_search(RequireFinalShiftCloseRuntimeBindingTokenMiddleware::class, $dbMiddleware, true);
$dbThrottleIndex = array_search('throttle:2,1', $dbMiddleware, true);
$assert($dbAuthIndex !== false, 'THR-RESP-008 DB-attestation auth middleware remains attached');
$assert($dbThrottleIndex !== false, 'THR-RESP-009 DB-attestation throttle remains exactly 2,1');
$assert($dbAuthIndex < $dbThrottleIndex, 'THR-RESP-010 DB-attestation auth remains before throttle');
$assert(
    ! in_array(HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware::class, $dbMiddleware, true),
    'THR-RESP-011 DB-attestation route middleware stack is not rewritten by Sprint142',
);

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'THR-RESP-012 canonical runtime manifest is absent before dispatch');

$root = sys_get_temp_dir().'/oneqay-sprint142-throttle-response-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'THR-RESP-013 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$manifestPath = $root.'/binding.json';
$selectionFingerprint = str_repeat('d', 64);

$selection = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'sprint142-durable-stage-01',
        'runtime_class' => 'durable-isolated-stage',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 142001,
            'run_attempt' => 1,
            'ingestion_fingerprint_sha256' => str_repeat('e', 64),
        ],
    ],
];

$writer = new class implements FinalShiftCloseRuntimeBindingManifestWriter {
    public int $writes = 0;

    public ?FinalShiftCloseRuntimeBindingManifest $manifest = null;

    public function write(FinalShiftCloseRuntimeBindingManifest $manifest): void
    {
        $this->writes++;
        $this->manifest = $manifest;
    }
};

$reader = new class implements FinalShiftCloseRuntimeDatabaseIdentityReader {
    public int $reads = 0;

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        $this->reads++;

        return new FinalShiftCloseRuntimeDatabaseIdentity(
            'oneqay_sprint142_synthetic',
            'db-sprint142-synthetic',
            3306,
        );
    }
};

$productionWriterResolutionAttempts = 0;
$productionDatabaseReaderResolutionAttempts = 0;

$makeMaterializationRequest = static function (string $operationId) use ($validToken, $selectionFingerprint): Request {
    return Request::create(
        '/internal/final-shift-close/runtime-binding-manifest/materialize',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$validToken,
            'REMOTE_ADDR' => '127.0.0.142',
        ],
        json_encode([
            'operation_id' => $operationId,
            'expected_selection_fingerprint_sha256' => $selectionFingerprint,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );
};

$makeDbAttestationRequest = static function () use ($validToken): Request {
    return Request::create(
        '/internal/final-shift-close/runtime-db-binding-attestation',
        'GET',
        [],
        [],
        [],
        [
            'HTTP_AUTHORIZATION' => 'Bearer '.$validToken,
            'REMOTE_ADDR' => '127.0.1.142',
        ],
    );
};

$dispatch = static function (Request $request) use ($kernel): Response {
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    return $response;
};

$assertHardenedThrottle = static function (
    Response $response,
    int $expectedLimit,
    string $case,
    array $forbiddenValues,
) use ($assert): void {
    $assert($response->getStatusCode() === 429, $case.' status remains 429');
    $assert((string) $response->getContent() === '', $case.' body is empty');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    $assert(str_contains($cacheControl, 'no-store'), $case.' cache-control contains no-store');
    $assert(str_contains($cacheControl, 'private'), $case.' cache-control contains private');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma is no-cache');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff is preserved');
    $assert(
        $response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        $case.' robot indexing is excluded',
    );

    $assert((int) $response->headers->get('X-RateLimit-Limit') === $expectedLimit, $case.' rate-limit ceiling is preserved');
    $assert((int) $response->headers->get('X-RateLimit-Remaining') === 0, $case.' rate-limit remaining is zero');

    $retryAfter = (string) $response->headers->get('Retry-After');
    $reset = (string) $response->headers->get('X-RateLimit-Reset');
    $assert($retryAfter !== '' && ctype_digit($retryAfter), $case.' Retry-After is preserved');
    $assert($reset !== '' && ctype_digit($reset), $case.' X-RateLimit-Reset is preserved');

    $surface = (string) $response->getContent().json_encode(
        $response->headers->all(),
        JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    );
    foreach ($forbiddenValues as $forbiddenValue) {
        $assert(! str_contains($surface, $forbiddenValue), $case.' sensitive fixture is not reflected');
    }
};

try {
    $selectionBytes = file_put_contents(
        $selectionPath,
        json_encode($selection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'THR-RESP-014 synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'THR-RESP-015 synthetic selection fixture permissions restricted');

    $app->scoped(
        FinalShiftCloseRuntimeBindingManifestWriter::class,
        static function () use (&$productionWriterResolutionAttempts): FinalShiftCloseRuntimeBindingManifestWriter {
            $productionWriterResolutionAttempts++;
            throw new RuntimeException('Production manifest writer resolution is forbidden in Sprint142.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeBindingManifestMaterializer::class,
        new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath),
    );

    $materializationResponseOne = $dispatch($makeMaterializationRequest('sprint142-throttle-response-0001'));
    $assert($materializationResponseOne->getStatusCode() === 200, 'THR-RESP-016 first materialization request returns 200');
    $assert($writer->writes === 1, 'THR-RESP-017 first materialization request performs one synthetic write');

    $materializationResponseTwo = $dispatch($makeMaterializationRequest('sprint142-throttle-response-0002'));
    $assertHardenedThrottle(
        $materializationResponseTwo,
        1,
        'THR-RESP-018 materialization excess request',
        [$validToken, $selectionPath, $canonicalManifestPath],
    );
    $assert($writer->writes === 1, 'THR-RESP-019 materialization throttle rejection causes no second write');
    $assert($productionWriterResolutionAttempts === 0, 'THR-RESP-020 production writer remains unresolved');
    $assert($writer->manifest instanceof FinalShiftCloseRuntimeBindingManifest, 'THR-RESP-021 synthetic manifest captured');

    $manifestBytes = file_put_contents($manifestPath, $writer->manifest->toCanonicalJson(), LOCK_EX);
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'THR-RESP-022 synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'THR-RESP-023 synthetic manifest fixture permissions restricted');

    $app->scoped(
        FinalShiftCloseRuntimeDatabaseIdentityReader::class,
        static function () use (&$productionDatabaseReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
            $productionDatabaseReaderResolutionAttempts++;
            throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint142.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeDbBindingAttestation::class,
        new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath),
    );

    $dbResponseOne = $dispatch($makeDbAttestationRequest());
    $assert($dbResponseOne->getStatusCode() === 200, 'THR-RESP-024 first DB-attestation request returns 200');
    $dbResponseTwo = $dispatch($makeDbAttestationRequest());
    $assert($dbResponseTwo->getStatusCode() === 200, 'THR-RESP-025 second DB-attestation request returns 200');
    $assert($reader->reads === 2, 'THR-RESP-026 two DB-attestation requests perform exactly two synthetic reads');

    $dbResponseThree = $dispatch($makeDbAttestationRequest());
    $assertHardenedThrottle(
        $dbResponseThree,
        2,
        'THR-RESP-027 DB-attestation excess request',
        [$validToken, $manifestPath, $canonicalManifestPath],
    );
    $assert($reader->reads === 2, 'THR-RESP-028 DB-attestation throttle rejection causes no third read');
    $assert($productionDatabaseReaderResolutionAttempts === 0, 'THR-RESP-029 production DB reader remains unresolved');

    $router->get('/internal/sprint142-unrelated-throttle-probe', static fn (): Response => response('', 204))
        ->middleware('throttle:1,1');

    $unrelatedRequestOne = Request::create(
        '/internal/sprint142-unrelated-throttle-probe',
        'GET',
        [],
        [],
        [],
        ['REMOTE_ADDR' => '127.0.2.142', 'HTTP_ACCEPT' => 'application/json'],
    );
    $unrelatedResponseOne = $dispatch($unrelatedRequestOne);
    $assert($unrelatedResponseOne->getStatusCode() === 204, 'THR-RESP-030 unrelated throttle probe first request returns 204');

    $unrelatedRequestTwo = Request::create(
        '/internal/sprint142-unrelated-throttle-probe',
        'GET',
        [],
        [],
        [],
        ['REMOTE_ADDR' => '127.0.2.142', 'HTTP_ACCEPT' => 'application/json'],
    );
    $unrelatedResponseTwo = $dispatch($unrelatedRequestTwo);
    $assert($unrelatedResponseTwo->getStatusCode() === 429, 'THR-RESP-031 unrelated throttle probe still returns framework 429');
    $assert(
        $unrelatedResponseTwo->headers->get('X-Robots-Tag') === null,
        'THR-RESP-032 Sprint142 hardening does not apply to unrelated throttled route',
    );

    $assert(! file_exists($canonicalManifestPath), 'THR-RESP-033 canonical runtime manifest remains absent');
    $assert($writer->writes === 1, 'THR-RESP-034 materialization write count remains one');
    $assert($reader->reads === 2, 'THR-RESP-035 DB identity read count remains two');

    fwrite(STDOUT, "Sprint142 Final Shift Close throttle rejection response hardening regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($manifestPath);
    @rmdir($root);
}
