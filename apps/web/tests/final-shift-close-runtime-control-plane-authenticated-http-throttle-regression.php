<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentity;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Sprint138 Final Shift Close authenticated HTTP throttle regression failed: '.$case,
        );
    }
};

$validToken = str_repeat('T', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$assert(
    strlen($validToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'HTTP-THR-001 fixture remains exactly at canonical minimum token length',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken) === true,
    'HTTP-THR-002 canonical Sprint130 token policy accepts synthetic bearer fixture',
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

$routes = $app->make('router')->getRoutes();
$materializationRoute = $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize');
$attestationRoute = $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation');
$assert($materializationRoute !== null, 'HTTP-THR-003 canonical materialization route is registered');
$assert($attestationRoute !== null, 'HTTP-THR-004 canonical DB-attestation route is registered');
$assert(
    in_array('throttle:1,1', $materializationRoute->gatherMiddleware(), true),
    'HTTP-THR-005 materialization route retains one-request-per-minute throttle',
);
$assert(
    in_array('throttle:2,1', $attestationRoute->gatherMiddleware(), true),
    'HTTP-THR-006 DB-attestation route retains two-requests-per-minute throttle',
);

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'HTTP-THR-007 canonical runtime manifest is absent before dispatch');

$root = sys_get_temp_dir().'/oneqay-sprint138-authenticated-http-throttle-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'HTTP-THR-008 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$manifestPath = $root.'/binding.json';
$selectionFingerprint = str_repeat('d', 64);

$selection = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'sprint138-durable-stage-01',
        'runtime_class' => 'durable-isolated-stage',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 138001,
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
            'oneqay_sprint138_synthetic',
            'db-sprint138-synthetic',
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
            'REMOTE_ADDR' => '127.0.0.138',
        ],
        json_encode([
            'operation_id' => $operationId,
            'expected_selection_fingerprint_sha256' => $selectionFingerprint,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );
};

$makeAttestationRequest = static function () use ($validToken): Request {
    return Request::create(
        '/internal/final-shift-close/runtime-db-binding-attestation',
        'GET',
        [],
        [],
        [],
        [
            'HTTP_AUTHORIZATION' => 'Bearer '.$validToken,
            'REMOTE_ADDR' => '127.0.1.138',
        ],
    );
};

try {
    $selectionBytes = file_put_contents(
        $selectionPath,
        json_encode($selection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'HTTP-THR-009 synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'HTTP-THR-010 synthetic selection fixture permissions restricted');
    clearstatcache(true, $selectionPath);

    $app->scoped(
        FinalShiftCloseRuntimeBindingManifestWriter::class,
        static function () use (&$productionWriterResolutionAttempts): FinalShiftCloseRuntimeBindingManifestWriter {
            $productionWriterResolutionAttempts++;
            throw new RuntimeException('Production manifest writer resolution is forbidden in Sprint138.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeBindingManifestMaterializer::class,
        new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath),
    );

    $materializationRequestOne = $makeMaterializationRequest('sprint138-http-throttle-op-0001');
    $materializationResponseOne = $kernel->handle($materializationRequestOne);
    $assert($materializationResponseOne->getStatusCode() === 200, 'HTTP-THR-011 first authenticated materialization request returns 200');
    $kernel->terminate($materializationRequestOne, $materializationResponseOne);
    $assert($writer->writes === 1, 'HTTP-THR-012 materialization writer invoked exactly once after allowed request');
    $assert($writer->manifest instanceof FinalShiftCloseRuntimeBindingManifest, 'HTTP-THR-013 synthetic manifest captured after allowed request');
    $assert($productionWriterResolutionAttempts === 0, 'HTTP-THR-014 production writer binding remains unresolved');
    $assert(
        $app->resolved(FinalShiftCloseRuntimeBindingManifestMaterializationController::class) === true,
        'HTTP-THR-015 real materialization controller resolved for allowed request',
    );

    $materializationRequestTwo = $makeMaterializationRequest('sprint138-http-throttle-op-0002');
    $materializationResponseTwo = $kernel->handle($materializationRequestTwo);
    $assert($materializationResponseTwo->getStatusCode() === 429, 'HTTP-THR-016 second materialization request is throttled with 429');
    $kernel->terminate($materializationRequestTwo, $materializationResponseTwo);
    $assert($writer->writes === 1, 'HTTP-THR-017 throttled materialization request causes no second write');
    $assert($productionWriterResolutionAttempts === 0, 'HTTP-THR-018 production writer remains unresolved after throttle rejection');
    $assert(
        ! str_contains((string) $materializationResponseTwo->getContent(), $validToken),
        'HTTP-THR-019 materialization throttle response does not expose bearer fixture',
    );
    $assert(
        ! str_contains((string) $materializationResponseTwo->getContent(), $selectionPath),
        'HTTP-THR-020 materialization throttle response does not expose synthetic selection path',
    );
    $assert(! file_exists($canonicalManifestPath), 'HTTP-THR-021 canonical runtime manifest remains absent after materialization throttle check');

    $manifestBytes = file_put_contents(
        $manifestPath,
        $writer->manifest->toCanonicalJson(),
        LOCK_EX,
    );
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'HTTP-THR-022 synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'HTTP-THR-023 synthetic manifest fixture permissions restricted');
    clearstatcache(true, $manifestPath);

    $app->scoped(
        FinalShiftCloseRuntimeDatabaseIdentityReader::class,
        static function () use (&$productionDatabaseReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
            $productionDatabaseReaderResolutionAttempts++;
            throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint138.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeDbBindingAttestation::class,
        new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath),
    );

    $attestationRequestOne = $makeAttestationRequest();
    $attestationResponseOne = $kernel->handle($attestationRequestOne);
    $assert($attestationResponseOne->getStatusCode() === 200, 'HTTP-THR-024 first authenticated DB-attestation request returns 200');
    $kernel->terminate($attestationRequestOne, $attestationResponseOne);
    $assert($reader->reads === 1, 'HTTP-THR-025 first allowed DB-attestation request performs one synthetic read');

    $attestationRequestTwo = $makeAttestationRequest();
    $attestationResponseTwo = $kernel->handle($attestationRequestTwo);
    $assert($attestationResponseTwo->getStatusCode() === 200, 'HTTP-THR-026 second authenticated DB-attestation request returns 200');
    $kernel->terminate($attestationRequestTwo, $attestationResponseTwo);
    $assert($reader->reads === 2, 'HTTP-THR-027 two allowed DB-attestation requests perform exactly two synthetic reads');
    $assert($productionDatabaseReaderResolutionAttempts === 0, 'HTTP-THR-028 production database identity reader remains unresolved');
    $assert(
        $app->resolved(FinalShiftCloseRuntimeDbBindingAttestationController::class) === true,
        'HTTP-THR-029 real DB-attestation controller resolved for allowed requests',
    );

    $attestationRequestThree = $makeAttestationRequest();
    $attestationResponseThree = $kernel->handle($attestationRequestThree);
    $assert($attestationResponseThree->getStatusCode() === 429, 'HTTP-THR-030 third DB-attestation request is throttled with 429');
    $kernel->terminate($attestationRequestThree, $attestationResponseThree);
    $assert($reader->reads === 2, 'HTTP-THR-031 throttled DB-attestation request causes no third synthetic read');
    $assert(
        $productionDatabaseReaderResolutionAttempts === 0,
        'HTTP-THR-032 production database identity reader remains unresolved after throttle rejection',
    );
    $assert(
        ! str_contains((string) $attestationResponseThree->getContent(), $validToken),
        'HTTP-THR-033 DB-attestation throttle response does not expose bearer fixture',
    );
    $assert(
        ! str_contains((string) $attestationResponseThree->getContent(), $manifestPath),
        'HTTP-THR-034 DB-attestation throttle response does not expose synthetic manifest path',
    );
    $assert(! file_exists($canonicalManifestPath), 'HTTP-THR-035 canonical runtime manifest remains absent after full throttle regression');
    $assert($writer->writes === 1, 'HTTP-THR-036 materialization writer count remains one through full regression');
    $assert($reader->reads === 2, 'HTTP-THR-037 database identity read count remains two through full regression');

    fwrite(STDOUT, "Sprint138 Final Shift Close authenticated HTTP throttle regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($manifestPath);
    @rmdir($root);
}
