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
            'Sprint137 Final Shift Close authenticated HTTP fail-closed regression failed: '.$case,
        );
    }
};

$assertExactError = static function ($response, int $status, string $error, string $case) use ($assert): void {
    $assert($response->getStatusCode() === $status, $case.' status');
    $payload = json_decode((string) $response->getContent(), true, 32, JSON_THROW_ON_ERROR);
    $assert($payload === ['error' => $error], $case.' exact error contract');
};

$assertMaterializationHeaders = static function ($response, string $case) use ($assert): void {
    $cache = (string) $response->headers->get('Cache-Control');
    $assert(str_contains($cache, 'no-store') && str_contains($cache, 'private'), $case.' cache-control');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma');
    $assert(
        $response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        $case.' robot exclusion',
    );
};

$assertAttestationHeaders = static function ($response, string $case) use ($assert): void {
    $cache = (string) $response->headers->get('Cache-Control');
    $assert(str_contains($cache, 'no-store') && str_contains($cache, 'private'), $case.' cache-control');
    $assert($response->headers->get('Pragma') === 'no-cache', $case.' pragma');
    $assert($response->headers->get('X-Content-Type-Options') === 'nosniff', $case.' nosniff');
    $assert(
        $response->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        $case.' robot exclusion',
    );
};

$validToken = str_repeat('F', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$assert(
    strlen($validToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'HTTP-FAIL-001 fixture remains exactly at canonical minimum token length',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken) === true,
    'HTTP-FAIL-002 canonical Sprint130 token policy accepts synthetic bearer fixture',
);

$environment = [
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
$assert(
    $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize') !== null,
    'HTTP-FAIL-003 canonical materialization route is registered',
);
$assert(
    $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation') !== null,
    'HTTP-FAIL-004 canonical DB-attestation route is registered',
);

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'HTTP-FAIL-005 canonical runtime manifest is absent before dispatch');

$root = sys_get_temp_dir().'/oneqay-sprint137-authenticated-http-fail-closed-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'HTTP-FAIL-006 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$manifestPath = $root.'/binding.json';
$selectionFingerprint = str_repeat('d', 64);
$operationId = 'sprint137-http-fail-op-0001';

$writer = new class implements FinalShiftCloseRuntimeBindingManifestWriter {
    public int $writes = 0;

    public function write(FinalShiftCloseRuntimeBindingManifest $manifest): void
    {
        $this->writes++;
    }
};

$throwingReader = new class implements FinalShiftCloseRuntimeDatabaseIdentityReader {
    public int $reads = 0;

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        $this->reads++;

        throw new RuntimeException('SPRINT137_SYNTHETIC_DB_READER_FAILURE_INTERNAL_ONLY');
    }
};

$productionWriterResolutionAttempts = 0;
$productionDatabaseReaderResolutionAttempts = 0;

try {
    $blockedSelection = [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'selection_state' => 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET',
        'selected_target' => null,
    ];
    $selectionBytes = file_put_contents(
        $selectionPath,
        json_encode($blockedSelection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'HTTP-FAIL-007 blocked synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'HTTP-FAIL-008 blocked selection fixture permissions restricted');
    clearstatcache(true, $selectionPath);

    $app->scoped(
        FinalShiftCloseRuntimeBindingManifestWriter::class,
        static function () use (&$productionWriterResolutionAttempts): FinalShiftCloseRuntimeBindingManifestWriter {
            $productionWriterResolutionAttempts++;
            throw new RuntimeException('Production manifest writer resolution is forbidden in Sprint137.');
        },
    );

    $materializer = new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath);
    $app->instance(FinalShiftCloseRuntimeBindingManifestMaterializer::class, $materializer);

    $materializationRequest = Request::create(
        '/internal/final-shift-close/runtime-binding-manifest/materialize',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$validToken,
        ],
        json_encode([
            'operation_id' => $operationId,
            'expected_selection_fingerprint_sha256' => $selectionFingerprint,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );

    $materializationResponse = $kernel->handle($materializationRequest);
    $assertExactError(
        $materializationResponse,
        503,
        'materialization_unavailable',
        'HTTP-FAIL-009 authenticated materialization application failure',
    );
    $kernel->terminate($materializationRequest, $materializationResponse);

    $assert($writer->writes === 0, 'HTTP-FAIL-010 blocked target never reaches in-memory writer');
    $assert($productionWriterResolutionAttempts === 0, 'HTTP-FAIL-011 production writer binding was never resolved');
    $assert(
        $app->resolved(FinalShiftCloseRuntimeBindingManifestMaterializationController::class) === true,
        'HTTP-FAIL-012 real materialization controller resolved through HTTP dispatch',
    );
    $assertMaterializationHeaders($materializationResponse, 'HTTP-FAIL-013 materialization failure');
    $materializationBody = (string) $materializationResponse->getContent();
    $assert(
        ! str_contains($materializationBody, 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET')
        && ! str_contains($materializationBody, $selectionPath)
        && ! str_contains($materializationBody, $selectionFingerprint)
        && ! str_contains($materializationBody, $validToken),
        'HTTP-FAIL-014 materialization failure exposes no synthetic or credential internals',
    );
    $assert(! file_exists($canonicalManifestPath), 'HTTP-FAIL-015 canonical runtime manifest remains absent');

    $validManifest = [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'selection_state' => 'SELECTED_NOT_AUTHORIZED',
        'environment_id' => 'sprint137-durable-stage-01',
        'runtime_class' => 'durable-isolated-stage',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 137001,
            'run_attempt' => 1,
            'ingestion_fingerprint_sha256' => str_repeat('e', 64),
        ],
        'secrets_embedded' => false,
    ];
    $manifestBytes = file_put_contents(
        $manifestPath,
        json_encode($validManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'HTTP-FAIL-016 valid synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'HTTP-FAIL-017 synthetic manifest fixture permissions restricted');
    clearstatcache(true, $manifestPath);

    $app->scoped(
        FinalShiftCloseRuntimeDatabaseIdentityReader::class,
        static function () use (&$productionDatabaseReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
            $productionDatabaseReaderResolutionAttempts++;
            throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint137.');
        },
    );

    $attestation = new FinalShiftCloseRuntimeDbBindingAttestation($throwingReader, $manifestPath);
    $app->instance(FinalShiftCloseRuntimeDbBindingAttestation::class, $attestation);

    $attestationRequest = Request::create(
        '/internal/final-shift-close/runtime-db-binding-attestation',
        'GET',
        [],
        [],
        [],
        ['HTTP_AUTHORIZATION' => 'Bearer '.$validToken],
    );
    $attestationResponse = $kernel->handle($attestationRequest);
    $assertExactError(
        $attestationResponse,
        503,
        'RUNTIME_DB_BINDING_ATTESTATION_UNAVAILABLE',
        'HTTP-FAIL-018 authenticated DB identity-reader failure',
    );
    $kernel->terminate($attestationRequest, $attestationResponse);

    $assert($throwingReader->reads === 1, 'HTTP-FAIL-019 throwing synthetic database identity reader invoked exactly once');
    $assert(
        $productionDatabaseReaderResolutionAttempts === 0,
        'HTTP-FAIL-020 production database identity reader binding was never resolved',
    );
    $assert(
        $app->resolved(FinalShiftCloseRuntimeDbBindingAttestationController::class) === true,
        'HTTP-FAIL-021 real DB-attestation controller resolved through HTTP dispatch',
    );
    $assertAttestationHeaders($attestationResponse, 'HTTP-FAIL-022 DB-attestation failure');
    $attestationBody = (string) $attestationResponse->getContent();
    $assert(
        ! str_contains($attestationBody, 'SPRINT137_SYNTHETIC_DB_READER_FAILURE_INTERNAL_ONLY')
        && ! str_contains($attestationBody, 'sprint137-durable-stage-01')
        && ! str_contains($attestationBody, $manifestPath)
        && ! str_contains($attestationBody, $validToken),
        'HTTP-FAIL-023 DB-attestation failure exposes no exception, database, path, or credential internals',
    );

    $assert(! file_exists($canonicalManifestPath), 'HTTP-FAIL-024 canonical runtime manifest remains absent after both requests');
    $assert($productionWriterResolutionAttempts === 0, 'HTTP-FAIL-025 production writer remained untouched through full regression');
    $assert(
        $productionDatabaseReaderResolutionAttempts === 0,
        'HTTP-FAIL-026 production database reader remained untouched through full regression',
    );

    fwrite(STDOUT, "Sprint137 Final Shift Close authenticated HTTP fail-closed regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($manifestPath);
    @rmdir($root);
}
