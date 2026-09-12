<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentity;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingTokenMiddleware;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Sprint139 Final Shift Close auth-before-throttle regression failed: '.$case,
        );
    }
};

$validToken = str_repeat('T', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$wrongToken = str_repeat('W', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);

$assert(
    strlen($validToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'AUTH-THR-001 expected-token fixture remains at canonical minimum length',
);
$assert(
    strlen($wrongToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'AUTH-THR-002 wrong-bearer fixture remains at canonical minimum length',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken) === true,
    'AUTH-THR-003 canonical Sprint130 policy accepts expected synthetic token',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($wrongToken) === true,
    'AUTH-THR-004 wrong bearer is structurally canonical-valid and fails only by mismatch',
);
$assert($validToken !== $wrongToken, 'AUTH-THR-005 expected and wrong bearer fixtures differ');

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

$routes = $app->make('router')->getRoutes();
$materializationRoute = $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize');
$attestationRoute = $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation');
$assert($materializationRoute !== null, 'AUTH-THR-006 canonical materialization route is registered');
$assert($attestationRoute !== null, 'AUTH-THR-007 canonical DB-attestation route is registered');

$materializationMiddleware = array_values($materializationRoute->middleware());
$materializationAuthIndex = array_search(
    RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class,
    $materializationMiddleware,
    true,
);
$materializationThrottleIndex = array_search('throttle:1,1', $materializationMiddleware, true);
$assert($materializationAuthIndex !== false, 'AUTH-THR-008 materialization auth middleware is attached');
$assert($materializationThrottleIndex !== false, 'AUTH-THR-009 materialization throttle middleware is attached');
$assert(
    $materializationAuthIndex < $materializationThrottleIndex,
    'AUTH-THR-010 materialization auth middleware precedes throttle accounting',
);

$attestationMiddleware = array_values($attestationRoute->middleware());
$attestationAuthIndex = array_search(
    RequireFinalShiftCloseRuntimeBindingTokenMiddleware::class,
    $attestationMiddleware,
    true,
);
$attestationThrottleIndex = array_search('throttle:2,1', $attestationMiddleware, true);
$assert($attestationAuthIndex !== false, 'AUTH-THR-011 DB-attestation auth middleware is attached');
$assert($attestationThrottleIndex !== false, 'AUTH-THR-012 DB-attestation throttle middleware is attached');
$assert(
    $attestationAuthIndex < $attestationThrottleIndex,
    'AUTH-THR-013 DB-attestation auth middleware precedes throttle accounting',
);

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'AUTH-THR-014 canonical runtime manifest is absent before dispatch');

$root = sys_get_temp_dir().'/oneqay-sprint139-auth-before-throttle-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'AUTH-THR-015 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$manifestPath = $root.'/binding.json';
$selectionFingerprint = str_repeat('d', 64);

$selection = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'sprint139-durable-stage-01',
        'runtime_class' => 'durable-isolated-stage',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 139001,
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
            'oneqay_sprint139_synthetic',
            'db-sprint139-synthetic',
            3306,
        );
    }
};

$productionWriterResolutionAttempts = 0;
$productionDatabaseReaderResolutionAttempts = 0;

$makeMaterializationRequest = static function (
    string $token,
    string $operationId,
) use ($selectionFingerprint): Request {
    return Request::create(
        '/internal/final-shift-close/runtime-binding-manifest/materialize',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'REMOTE_ADDR' => '127.0.0.139',
        ],
        json_encode([
            'operation_id' => $operationId,
            'expected_selection_fingerprint_sha256' => $selectionFingerprint,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );
};

$makeAttestationRequest = static function (string $token): Request {
    return Request::create(
        '/internal/final-shift-close/runtime-db-binding-attestation',
        'GET',
        [],
        [],
        [],
        [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'REMOTE_ADDR' => '127.0.1.139',
        ],
    );
};

try {
    $selectionBytes = file_put_contents(
        $selectionPath,
        json_encode($selection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'AUTH-THR-016 synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'AUTH-THR-017 synthetic selection fixture permissions restricted');
    clearstatcache(true, $selectionPath);

    $app->scoped(
        FinalShiftCloseRuntimeBindingManifestWriter::class,
        static function () use (&$productionWriterResolutionAttempts): FinalShiftCloseRuntimeBindingManifestWriter {
            $productionWriterResolutionAttempts++;
            throw new RuntimeException('Production manifest writer resolution is forbidden in Sprint139.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeBindingManifestMaterializer::class,
        new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath),
    );

    $materializationWrongRequest = $makeMaterializationRequest(
        $wrongToken,
        'sprint139-auth-throttle-wrong-0001',
    );
    $materializationWrongResponse = $kernel->handle($materializationWrongRequest);
    $assert($materializationWrongResponse->getStatusCode() === 401, 'AUTH-THR-018 wrong materialization bearer returns 401');
    $kernel->terminate($materializationWrongRequest, $materializationWrongResponse);
    $assert($writer->writes === 0, 'AUTH-THR-019 wrong materialization bearer reaches no writer side effect');
    $assert($productionWriterResolutionAttempts === 0, 'AUTH-THR-020 production writer remains unresolved after wrong bearer');

    $materializationValidRequestOne = $makeMaterializationRequest(
        $validToken,
        'sprint139-auth-throttle-valid-0001',
    );
    $materializationValidResponseOne = $kernel->handle($materializationValidRequestOne);
    $assert(
        $materializationValidResponseOne->getStatusCode() === 200,
        'AUTH-THR-021 first valid materialization request from same IP retains full authenticated budget',
    );
    $kernel->terminate($materializationValidRequestOne, $materializationValidResponseOne);
    $assert($writer->writes === 1, 'AUTH-THR-022 first valid materialization request performs exactly one write');
    $assert($writer->manifest instanceof FinalShiftCloseRuntimeBindingManifest, 'AUTH-THR-023 synthetic manifest captured after allowed request');
    $assert(
        $app->resolved(FinalShiftCloseRuntimeBindingManifestMaterializationController::class) === true,
        'AUTH-THR-024 real materialization controller resolves only for allowed valid request',
    );

    $materializationValidRequestTwo = $makeMaterializationRequest(
        $validToken,
        'sprint139-auth-throttle-valid-0002',
    );
    $materializationValidResponseTwo = $kernel->handle($materializationValidRequestTwo);
    $assert($materializationValidResponseTwo->getStatusCode() === 429, 'AUTH-THR-025 second valid materialization request is throttled with 429');
    $kernel->terminate($materializationValidRequestTwo, $materializationValidResponseTwo);
    $assert($writer->writes === 1, 'AUTH-THR-026 throttled valid materialization request causes no second write');
    $assert($productionWriterResolutionAttempts === 0, 'AUTH-THR-027 production writer remains unresolved through materialization sequence');
    $assert(
        ! str_contains((string) $materializationWrongResponse->getContent(), $validToken)
        && ! str_contains((string) $materializationWrongResponse->getContent(), $wrongToken)
        && ! str_contains((string) $materializationValidResponseTwo->getContent(), $validToken),
        'AUTH-THR-028 materialization auth/throttle responses expose no bearer fixture',
    );
    $assert(
        ! str_contains((string) $materializationWrongResponse->getContent(), $selectionPath)
        && ! str_contains((string) $materializationValidResponseTwo->getContent(), $selectionPath),
        'AUTH-THR-029 materialization auth/throttle responses expose no synthetic selection path',
    );
    $assert(! file_exists($canonicalManifestPath), 'AUTH-THR-030 canonical runtime manifest remains absent');

    $manifestBytes = file_put_contents(
        $manifestPath,
        $writer->manifest->toCanonicalJson(),
        LOCK_EX,
    );
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'AUTH-THR-031 synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'AUTH-THR-032 synthetic manifest fixture permissions restricted');
    clearstatcache(true, $manifestPath);

    $app->scoped(
        FinalShiftCloseRuntimeDatabaseIdentityReader::class,
        static function () use (&$productionDatabaseReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
            $productionDatabaseReaderResolutionAttempts++;
            throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint139.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeDbBindingAttestation::class,
        new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath),
    );

    $attestationWrongRequest = $makeAttestationRequest($wrongToken);
    $attestationWrongResponse = $kernel->handle($attestationWrongRequest);
    $assert($attestationWrongResponse->getStatusCode() === 404, 'AUTH-THR-033 wrong DB-attestation bearer retains cloaked 404');
    $kernel->terminate($attestationWrongRequest, $attestationWrongResponse);
    $assert($reader->reads === 0, 'AUTH-THR-034 wrong DB-attestation bearer reaches no identity read');
    $assert($productionDatabaseReaderResolutionAttempts === 0, 'AUTH-THR-035 production DB reader remains unresolved after wrong bearer');

    $attestationValidRequestOne = $makeAttestationRequest($validToken);
    $attestationValidResponseOne = $kernel->handle($attestationValidRequestOne);
    $assert(
        $attestationValidResponseOne->getStatusCode() === 200,
        'AUTH-THR-036 first valid DB-attestation request from same IP retains authenticated budget',
    );
    $kernel->terminate($attestationValidRequestOne, $attestationValidResponseOne);
    $assert($reader->reads === 1, 'AUTH-THR-037 first valid DB-attestation request performs one synthetic read');

    $attestationValidRequestTwo = $makeAttestationRequest($validToken);
    $attestationValidResponseTwo = $kernel->handle($attestationValidRequestTwo);
    $assert(
        $attestationValidResponseTwo->getStatusCode() === 200,
        'AUTH-THR-038 second valid DB-attestation request retains second authenticated budget slot',
    );
    $kernel->terminate($attestationValidRequestTwo, $attestationValidResponseTwo);
    $assert($reader->reads === 2, 'AUTH-THR-039 two valid DB-attestation requests perform exactly two synthetic reads');
    $assert(
        $app->resolved(FinalShiftCloseRuntimeDbBindingAttestationController::class) === true,
        'AUTH-THR-040 real DB-attestation controller resolves for allowed valid requests',
    );

    $attestationValidRequestThree = $makeAttestationRequest($validToken);
    $attestationValidResponseThree = $kernel->handle($attestationValidRequestThree);
    $assert($attestationValidResponseThree->getStatusCode() === 429, 'AUTH-THR-041 third valid DB-attestation request is throttled with 429');
    $kernel->terminate($attestationValidRequestThree, $attestationValidResponseThree);
    $assert($reader->reads === 2, 'AUTH-THR-042 throttled valid DB-attestation request causes no third synthetic read');
    $assert(
        $productionDatabaseReaderResolutionAttempts === 0,
        'AUTH-THR-043 production DB reader remains unresolved through DB-attestation sequence',
    );
    $assert(
        ! str_contains((string) $attestationWrongResponse->getContent(), $validToken)
        && ! str_contains((string) $attestationWrongResponse->getContent(), $wrongToken)
        && ! str_contains((string) $attestationValidResponseThree->getContent(), $validToken),
        'AUTH-THR-044 DB-attestation auth/throttle responses expose no bearer fixture',
    );
    $assert(
        ! str_contains((string) $attestationWrongResponse->getContent(), $manifestPath)
        && ! str_contains((string) $attestationValidResponseThree->getContent(), $manifestPath),
        'AUTH-THR-045 DB-attestation auth/throttle responses expose no synthetic manifest path',
    );
    $assert(! file_exists($canonicalManifestPath), 'AUTH-THR-046 canonical runtime manifest remains absent after full regression');
    $assert($writer->writes === 1, 'AUTH-THR-047 materialization write count remains one through full regression');
    $assert($reader->reads === 2, 'AUTH-THR-048 database identity read count remains two through full regression');

    fwrite(STDOUT, "Sprint139 Final Shift Close auth-before-throttle regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($manifestPath);
    @rmdir($root);
}
