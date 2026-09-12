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
        throw new RuntimeException('Final Shift Close controller positive-path regression failed: '.$case);
    }
};

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$root = sys_get_temp_dir().'/oneqay-sprint132-controller-positive-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'CTRL-POS-001 isolated temporary directory created');

$selectionPath = $root.'/selection.json';
$manifestPath = $root.'/binding.json';
$selectionFingerprint = str_repeat('d', 64);
$operationId = 'sprint132-controller-op-0001';

$selection = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'sprint132-durable-stage-01',
        'runtime_class' => 'durable-isolated-stage',
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'readiness_attestation_sha256' => str_repeat('c', 64),
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion' => [
            'run_id' => 132001,
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
            'oneqay_sprint132_synthetic',
            'db-sprint132-synthetic',
            3306,
        );
    }
};

try {
    $selectionBytes = file_put_contents(
        $selectionPath,
        json_encode($selection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    $assert(is_int($selectionBytes) && $selectionBytes > 0, 'CTRL-POS-002 synthetic selection fixture written');
    $assert(chmod($selectionPath, 0600), 'CTRL-POS-003 synthetic selection fixture permissions restricted');
    clearstatcache(true, $selectionPath);

    $materializer = new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath);
    $materializationController = new FinalShiftCloseRuntimeBindingManifestMaterializationController($materializer);

    $materializationRequest = Request::create(
        '/_ci/sprint132/controller/materialization',
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

    $materializationResponse = $materializationController($materializationRequest);
    $assert($materializationResponse->getStatusCode() === 200, 'CTRL-POS-004 materialization controller returns HTTP 200');

    $materializationPayload = json_decode(
        (string) $materializationResponse->getContent(),
        true,
        32,
        JSON_THROW_ON_ERROR,
    );
    $assert(is_array($materializationPayload), 'CTRL-POS-005 materialization response is JSON object');
    $assert(($materializationPayload['operation_id'] ?? null) === $operationId, 'CTRL-POS-006 operation ID preserved');
    $assert(
        ($materializationPayload['selection_fingerprint_sha256'] ?? null) === $selectionFingerprint,
        'CTRL-POS-007 selection fingerprint preserved',
    );
    $assert(
        ($materializationPayload['materialization_state'] ?? null) === 'MATERIALIZED_SELECTED_TARGET_BINDING_MANIFEST',
        'CTRL-POS-008 materialization state preserved',
    );
    $assert($writer->writes === 1, 'CTRL-POS-009 in-memory manifest writer invoked exactly once');
    $assert($writer->manifest instanceof FinalShiftCloseRuntimeBindingManifest, 'CTRL-POS-010 in-memory manifest captured');
    $assert(! file_exists(storage_path('app/private/final-shift-close-runtime-binding.json')), 'CTRL-POS-011 canonical runtime manifest path remains absent');

    $materializationCache = (string) $materializationResponse->headers->get('Cache-Control');
    $assert(
        str_contains($materializationCache, 'no-store') && str_contains($materializationCache, 'private'),
        'CTRL-POS-012 materialization response remains non-cacheable and private',
    );
    $assert($materializationResponse->headers->get('Pragma') === 'no-cache', 'CTRL-POS-013 materialization response keeps no-cache pragma');
    $assert(
        $materializationResponse->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        'CTRL-POS-014 materialization response keeps robot exclusion',
    );

    $manifestBytes = file_put_contents(
        $manifestPath,
        $writer->manifest->toCanonicalJson(),
        LOCK_EX,
    );
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'CTRL-POS-015 synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'CTRL-POS-016 synthetic manifest fixture permissions restricted');
    clearstatcache(true, $manifestPath);

    $attestation = new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath);
    $attestationController = new FinalShiftCloseRuntimeDbBindingAttestationController($attestation);
    $attestationResponse = $attestationController();

    $assert($attestationResponse->getStatusCode() === 200, 'CTRL-POS-017 DB attestation controller returns HTTP 200');
    $attestationPayload = json_decode(
        (string) $attestationResponse->getContent(),
        true,
        32,
        JSON_THROW_ON_ERROR,
    );
    $assert(is_array($attestationPayload), 'CTRL-POS-018 DB attestation response is JSON object');
    $assert($reader->reads === 1, 'CTRL-POS-019 synthetic database identity reader invoked exactly once');
    $assert(
        ($attestationPayload['binding_state'] ?? null) === 'VERIFIED_SELECTED_TARGET_DATABASE',
        'CTRL-POS-020 verified database binding state preserved',
    );
    $assert(($attestationPayload['migration27_state'] ?? null) === 'NOT_EXECUTED', 'CTRL-POS-021 migration 27 remains not executed');
    $assert(($attestationPayload['attestation_mode'] ?? null) === 'READ_ONLY', 'CTRL-POS-022 attestation remains read-only');
    $assert(
        ($attestationPayload['database_identity_source'] ?? null) === 'ACTIVE_APPLICATION_DATABASE_CONNECTION',
        'CTRL-POS-023 response contract retains database identity source label',
    );
    $assert(
        ($attestationPayload['selection_fingerprint_sha256'] ?? null) === $selectionFingerprint,
        'CTRL-POS-024 selection provenance preserved through attestation',
    );
    $assert(($attestationPayload['secrets_embedded'] ?? null) === false, 'CTRL-POS-025 attestation remains secret-free');

    $expectedIdentity = new FinalShiftCloseRuntimeDatabaseIdentity(
        'oneqay_sprint132_synthetic',
        'db-sprint132-synthetic',
        3306,
    );
    $assert(
        hash_equals(
            $expectedIdentity->fingerprintSha256(),
            (string) ($attestationPayload['database_binding_sha256'] ?? ''),
        ),
        'CTRL-POS-026 deterministic synthetic database fingerprint preserved',
    );

    $attestationCache = (string) $attestationResponse->headers->get('Cache-Control');
    $assert(
        str_contains($attestationCache, 'no-store') && str_contains($attestationCache, 'private'),
        'CTRL-POS-027 DB attestation response remains non-cacheable and private',
    );
    $assert($attestationResponse->headers->get('Pragma') === 'no-cache', 'CTRL-POS-028 DB attestation response keeps no-cache pragma');
    $assert(
        $attestationResponse->headers->get('X-Content-Type-Options') === 'nosniff',
        'CTRL-POS-029 DB attestation response keeps nosniff',
    );
    $assert(
        $attestationResponse->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        'CTRL-POS-030 DB attestation response keeps robot exclusion',
    );

    fwrite(STDOUT, "Sprint132 Final Shift Close controller positive-path regression: OK\n");
} finally {
    @unlink($selectionPath);
    @unlink($manifestPath);
    @rmdir($root);
}
