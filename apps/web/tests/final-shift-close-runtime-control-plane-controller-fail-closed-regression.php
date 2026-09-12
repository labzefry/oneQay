<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
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
        throw new RuntimeException('Final Shift Close controller fail-closed regression failed: '.$case);
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

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$root = sys_get_temp_dir().'/oneqay-sprint133-controller-fail-closed-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'CTRL-FAIL-001 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$invalidManifestPath = $root.'/invalid-binding.json';
$validManifestPath = $root.'/valid-binding.json';
$selectionFingerprint = str_repeat('d', 64);
$operationId = 'sprint133-controller-op-0001';

$writer = new class implements FinalShiftCloseRuntimeBindingManifestWriter {
    public int $writes = 0;

    public function write(FinalShiftCloseRuntimeBindingManifest $manifest): void
    {
        $this->writes++;
    }
};

$nonThrowingReader = new class implements FinalShiftCloseRuntimeDatabaseIdentityReader {
    public int $reads = 0;

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        $this->reads++;

        return new FinalShiftCloseRuntimeDatabaseIdentity(
            'oneqay_sprint133_synthetic',
            'db-sprint133-synthetic',
            3306,
        );
    }
};

$throwingReader = new class implements FinalShiftCloseRuntimeDatabaseIdentityReader {
    public int $reads = 0;

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        $this->reads++;

        throw new RuntimeException('SPRINT133_SYNTHETIC_DB_READER_FAILURE_INTERNAL_ONLY');
    }
};

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
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'CTRL-FAIL-002 blocked synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'CTRL-FAIL-003 blocked selection fixture permissions restricted');
    clearstatcache(true, $selectionPath);

    $materializer = new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath);
    $materializationController = new FinalShiftCloseRuntimeBindingManifestMaterializationController($materializer);

    $invalidFieldSetRequest = Request::create(
        '/_ci/sprint133/controller/materialization-invalid-field-set',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'operation_id' => $operationId,
            'expected_selection_fingerprint_sha256' => $selectionFingerprint,
            'forbidden_extra_field' => 'SPRINT133_INTERNAL_FIELD_MARKER',
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );
    $invalidFieldSetResponse = $materializationController($invalidFieldSetRequest);
    $assertExactError($invalidFieldSetResponse, 422, 'invalid_request', 'CTRL-FAIL-004 invalid field set');
    $assert($writer->writes === 0, 'CTRL-FAIL-005 invalid field set rejected before writer invocation');
    $assertMaterializationHeaders($invalidFieldSetResponse, 'CTRL-FAIL-006 invalid field set');
    $assert(
        ! str_contains((string) $invalidFieldSetResponse->getContent(), 'SPRINT133_INTERNAL_FIELD_MARKER'),
        'CTRL-FAIL-007 invalid field value not reflected',
    );

    $applicationFailureRequest = Request::create(
        '/_ci/sprint133/controller/materialization-application-failure',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'operation_id' => $operationId,
            'expected_selection_fingerprint_sha256' => $selectionFingerprint,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    );
    $applicationFailureResponse = $materializationController($applicationFailureRequest);
    $assertExactError(
        $applicationFailureResponse,
        503,
        'materialization_unavailable',
        'CTRL-FAIL-008 materialization application failure',
    );
    $assert($writer->writes === 0, 'CTRL-FAIL-009 blocked selection never reaches writer');
    $assertMaterializationHeaders($applicationFailureResponse, 'CTRL-FAIL-010 materialization application failure');
    $materializationFailureBody = (string) $applicationFailureResponse->getContent();
    $assert(
        ! str_contains($materializationFailureBody, 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET')
        && ! str_contains($materializationFailureBody, $selectionPath)
        && ! str_contains($materializationFailureBody, $selectionFingerprint),
        'CTRL-FAIL-011 materialization failure exposes no synthetic internals',
    );

    $invalidManifest = [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'selection_state' => 'SELECTED_NOT_AUTHORIZED',
        'environment_id' => 'sprint133-invalid-preview',
        'runtime_class' => 'preview',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 133001,
            'run_attempt' => 1,
            'ingestion_fingerprint_sha256' => str_repeat('e', 64),
        ],
        'secrets_embedded' => false,
    ];
    $invalidManifestBytes = file_put_contents(
        $invalidManifestPath,
        json_encode($invalidManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($invalidManifestBytes) && $invalidManifestBytes > 0, 'CTRL-FAIL-012 invalid synthetic manifest written');
    $assert(chmod($invalidManifestPath, 0600), 'CTRL-FAIL-013 invalid manifest permissions restricted');
    clearstatcache(true, $invalidManifestPath);

    $invalidAttestation = new FinalShiftCloseRuntimeDbBindingAttestation($nonThrowingReader, $invalidManifestPath);
    $invalidAttestationController = new FinalShiftCloseRuntimeDbBindingAttestationController($invalidAttestation);
    $invalidAttestationResponse = $invalidAttestationController();
    $assertExactError(
        $invalidAttestationResponse,
        503,
        'RUNTIME_DB_BINDING_ATTESTATION_UNAVAILABLE',
        'CTRL-FAIL-014 DB attestation application failure',
    );
    $assert($nonThrowingReader->reads === 0, 'CTRL-FAIL-015 invalid manifest rejected before database identity read');
    $assertAttestationHeaders($invalidAttestationResponse, 'CTRL-FAIL-016 DB attestation application failure');
    $invalidAttestationBody = (string) $invalidAttestationResponse->getContent();
    $assert(
        ! str_contains($invalidAttestationBody, 'sprint133-invalid-preview')
        && ! str_contains($invalidAttestationBody, $invalidManifestPath),
        'CTRL-FAIL-017 DB attestation application failure exposes no synthetic internals',
    );

    $validManifest = $invalidManifest;
    $validManifest['environment_id'] = 'sprint133-durable-stage-01';
    $validManifest['runtime_class'] = 'durable-isolated-stage';
    $validManifestBytes = file_put_contents(
        $validManifestPath,
        json_encode($validManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($validManifestBytes) && $validManifestBytes > 0, 'CTRL-FAIL-018 valid synthetic manifest written');
    $assert(chmod($validManifestPath, 0600), 'CTRL-FAIL-019 valid manifest permissions restricted');
    clearstatcache(true, $validManifestPath);

    $readerFailureAttestation = new FinalShiftCloseRuntimeDbBindingAttestation($throwingReader, $validManifestPath);
    $readerFailureController = new FinalShiftCloseRuntimeDbBindingAttestationController($readerFailureAttestation);
    $readerFailureResponse = $readerFailureController();
    $assertExactError(
        $readerFailureResponse,
        503,
        'RUNTIME_DB_BINDING_ATTESTATION_UNAVAILABLE',
        'CTRL-FAIL-020 DB identity-reader failure',
    );
    $assert($throwingReader->reads === 1, 'CTRL-FAIL-021 throwing synthetic database identity reader invoked exactly once');
    $assertAttestationHeaders($readerFailureResponse, 'CTRL-FAIL-022 DB identity-reader failure');
    $readerFailureBody = (string) $readerFailureResponse->getContent();
    $assert(
        ! str_contains($readerFailureBody, 'SPRINT133_SYNTHETIC_DB_READER_FAILURE_INTERNAL_ONLY')
        && ! str_contains($readerFailureBody, 'oneqay_sprint133_synthetic')
        && ! str_contains($readerFailureBody, 'db-sprint133-synthetic')
        && ! str_contains($readerFailureBody, $validManifestPath),
        'CTRL-FAIL-023 DB identity-reader failure exposes no database or exception internals',
    );

    $assert(
        ! file_exists(storage_path('app/private/final-shift-close-runtime-binding.json')),
        'CTRL-FAIL-024 canonical runtime manifest path remains absent',
    );

    fwrite(STDOUT, "Sprint133 Final Shift Close controller fail-closed regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($invalidManifestPath);
    @unlink($validManifestPath);
    @rmdir($root);
}
