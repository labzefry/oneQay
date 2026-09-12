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
            'Sprint136 Final Shift Close authenticated HTTP positive-path regression failed: '.$case,
        );
    }
};

$validToken = str_repeat('H', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$assert(
    strlen($validToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'HTTP-POS-001 fixture remains exactly at canonical minimum token length',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken) === true,
    'HTTP-POS-002 canonical Sprint130 token policy accepts synthetic bearer fixture',
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
    'HTTP-POS-003 canonical materialization route is registered',
);
$assert(
    $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation') !== null,
    'HTTP-POS-004 canonical DB-attestation route is registered',
);

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'HTTP-POS-005 canonical runtime manifest is absent before dispatch');

$root = sys_get_temp_dir().'/oneqay-sprint136-authenticated-http-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'HTTP-POS-006 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$manifestPath = $root.'/binding.json';
$selectionFingerprint = str_repeat('d', 64);
$operationId = 'sprint136-http-op-0001';

$selection = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'sprint136-durable-stage-01',
        'runtime_class' => 'durable-isolated-stage',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 136001,
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
            'oneqay_sprint136_synthetic',
            'db-sprint136-synthetic',
            3306,
        );
    }
};

$productionWriterResolutionAttempts = 0;
$productionDatabaseReaderResolutionAttempts = 0;

try {
    $selectionBytes = file_put_contents(
        $selectionPath,
        json_encode($selection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'HTTP-POS-007 synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'HTTP-POS-008 synthetic selection fixture permissions restricted');
    clearstatcache(true, $selectionPath);

    $app->scoped(
        FinalShiftCloseRuntimeBindingManifestWriter::class,
        static function () use (&$productionWriterResolutionAttempts): FinalShiftCloseRuntimeBindingManifestWriter {
            $productionWriterResolutionAttempts++;
            throw new RuntimeException('Production manifest writer resolution is forbidden in Sprint136.');
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
    $assert($materializationResponse->getStatusCode() === 200, 'HTTP-POS-009 authenticated materialization HTTP dispatch returns 200');
    $kernel->terminate($materializationRequest, $materializationResponse);

    $materializationPayload = json_decode(
        (string) $materializationResponse->getContent(),
        true,
        32,
        JSON_THROW_ON_ERROR,
    );
    $assert(is_array($materializationPayload), 'HTTP-POS-010 materialization response is a JSON object');
    $assert(($materializationPayload['operation_id'] ?? null) === $operationId, 'HTTP-POS-011 operation ID preserved');
    $assert(
        ($materializationPayload['selection_fingerprint_sha256'] ?? null) === $selectionFingerprint,
        'HTTP-POS-012 selection fingerprint preserved',
    );
    $assert(
        ($materializationPayload['materialization_state'] ?? null) === 'MATERIALIZED_SELECTED_TARGET_BINDING_MANIFEST',
        'HTTP-POS-013 materialization state preserved',
    );
    $assert($writer->writes === 1, 'HTTP-POS-014 in-memory writer invoked exactly once');
    $assert($writer->manifest instanceof FinalShiftCloseRuntimeBindingManifest, 'HTTP-POS-015 synthetic manifest captured');
    $assert($productionWriterResolutionAttempts === 0, 'HTTP-POS-016 production writer binding was never resolved');
    $assert(
        $app->resolved(FinalShiftCloseRuntimeBindingManifestMaterializationController::class) === true,
        'HTTP-POS-017 real materialization controller resolved through HTTP dispatch',
    );
    $assert(! file_exists($canonicalManifestPath), 'HTTP-POS-018 canonical runtime manifest remains absent');

    $materializationCache = (string) $materializationResponse->headers->get('Cache-Control');
    $assert(
        str_contains($materializationCache, 'no-store') && str_contains($materializationCache, 'private'),
        'HTTP-POS-019 materialization response remains non-cacheable and private',
    );
    $assert($materializationResponse->headers->get('Pragma') === 'no-cache', 'HTTP-POS-020 materialization response keeps no-cache pragma');
    $assert(
        $materializationResponse->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        'HTTP-POS-021 materialization response keeps robot exclusion',
    );

    $manifestBytes = file_put_contents(
        $manifestPath,
        $writer->manifest->toCanonicalJson(),
        LOCK_EX,
    );
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'HTTP-POS-022 synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'HTTP-POS-023 synthetic manifest fixture permissions restricted');
    clearstatcache(true, $manifestPath);

    $app->scoped(
        FinalShiftCloseRuntimeDatabaseIdentityReader::class,
        static function () use (&$productionDatabaseReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
            $productionDatabaseReaderResolutionAttempts++;
            throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint136.');
        },
    );

    $attestation = new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath);
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
    $assert($attestationResponse->getStatusCode() === 200, 'HTTP-POS-024 authenticated DB-attestation HTTP dispatch returns 200');
    $kernel->terminate($attestationRequest, $attestationResponse);

    $attestationPayload = json_decode(
        (string) $attestationResponse->getContent(),
        true,
        32,
        JSON_THROW_ON_ERROR,
    );
    $assert(is_array($attestationPayload), 'HTTP-POS-025 DB-attestation response is a JSON object');
    $assert($reader->reads === 1, 'HTTP-POS-026 synthetic database identity reader invoked exactly once');
    $assert(
        $productionDatabaseReaderResolutionAttempts === 0,
        'HTTP-POS-027 production database identity reader binding was never resolved',
    );
    $assert(
        $app->resolved(FinalShiftCloseRuntimeDbBindingAttestationController::class) === true,
        'HTTP-POS-028 real DB-attestation controller resolved through HTTP dispatch',
    );
    $assert(
        ($attestationPayload['binding_state'] ?? null) === 'VERIFIED_SELECTED_TARGET_DATABASE',
        'HTTP-POS-029 verified database binding state preserved',
    );
    $assert(($attestationPayload['migration27_state'] ?? null) === 'NOT_EXECUTED', 'HTTP-POS-030 migration 27 remains not executed');
    $assert(($attestationPayload['attestation_mode'] ?? null) === 'READ_ONLY', 'HTTP-POS-031 attestation remains read-only');
    $assert(
        ($attestationPayload['selection_fingerprint_sha256'] ?? null) === $selectionFingerprint,
        'HTTP-POS-032 selection provenance preserved through HTTP attestation',
    );
    $assert(($attestationPayload['secrets_embedded'] ?? null) === false, 'HTTP-POS-033 DB attestation remains secret-free');

    $expectedIdentity = new FinalShiftCloseRuntimeDatabaseIdentity(
        'oneqay_sprint136_synthetic',
        'db-sprint136-synthetic',
        3306,
    );
    $assert(
        hash_equals(
            $expectedIdentity->fingerprintSha256(),
            (string) ($attestationPayload['database_binding_sha256'] ?? ''),
        ),
        'HTTP-POS-034 deterministic synthetic database fingerprint preserved',
    );

    $attestationCache = (string) $attestationResponse->headers->get('Cache-Control');
    $assert(
        str_contains($attestationCache, 'no-store') && str_contains($attestationCache, 'private'),
        'HTTP-POS-035 DB-attestation response remains non-cacheable and private',
    );
    $assert($attestationResponse->headers->get('Pragma') === 'no-cache', 'HTTP-POS-036 DB-attestation response keeps no-cache pragma');
    $assert(
        $attestationResponse->headers->get('X-Content-Type-Options') === 'nosniff',
        'HTTP-POS-037 DB-attestation response keeps nosniff',
    );
    $assert(
        $attestationResponse->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        'HTTP-POS-038 DB-attestation response keeps robot exclusion',
    );
    $assert(! file_exists($canonicalManifestPath), 'HTTP-POS-039 canonical runtime manifest remains absent after both requests');
    $assert($productionWriterResolutionAttempts === 0, 'HTTP-POS-040 production writer remained untouched through full regression');
    $assert(
        $productionDatabaseReaderResolutionAttempts === 0,
        'HTTP-POS-041 production database reader remained untouched through full regression',
    );

    fwrite(STDOUT, "Sprint136 Final Shift Close authenticated HTTP positive-path regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($manifestPath);
    @rmdir($root);
}
