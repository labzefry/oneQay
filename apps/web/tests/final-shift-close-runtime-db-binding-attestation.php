<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentity;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeDatabaseIdentity.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeDatabaseIdentityReader.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeDbBindingAttestation.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Final Shift Close runtime DB binding attestation regression failed: '.$case);
    }
};

$expectRejected = static function (callable $attempt, string $case) use ($assert): void {
    $rejected = false;
    try {
        $attempt();
    } catch (RuntimeException) {
        $rejected = true;
    }
    $assert($rejected, $case);
};

$root = sys_get_temp_dir().'/oneqay-fsc-runtime-db-binding-'.bin2hex(random_bytes(8));
mkdir($root, 0700, true);
$manifestPath = $root.'/binding.json';

$manifest = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'environment_id' => 'isolated-durable-stage-01',
    'runtime_class' => 'durable-isolated-stage',
    'exact_running_source_commit' => str_repeat('a', 40),
    'exact_running_artifact_sha256' => str_repeat('b', 64),
    'readiness_attestation_sha256' => str_repeat('c', 64),
    'selection_fingerprint_sha256' => str_repeat('d', 64),
    'trusted_ingestion' => [
        'run_id' => 12345,
        'run_attempt' => 2,
        'ingestion_fingerprint_sha256' => str_repeat('e', 64),
    ],
    'secrets_embedded' => false,
];

$writeManifest = static function (array $payload, int $mode = 0600) use ($manifestPath): void {
    file_put_contents(
        $manifestPath,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        LOCK_EX,
    );
    chmod($manifestPath, $mode);
    clearstatcache(true, $manifestPath);
};

$reader = new class implements FinalShiftCloseRuntimeDatabaseIdentityReader {
    public int $reads = 0;

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        $this->reads++;

        return new FinalShiftCloseRuntimeDatabaseIdentity('oneqay_stage', 'db-stage-01', 3306);
    }
};

try {
    $writeManifest($manifest);

    $service = new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath);
    $result = $service->attest();

    $expectedKeys = [
        'attestation_mode',
        'binding_state',
        'database_binding_algorithm',
        'database_binding_sha256',
        'database_identity_source',
        'environment_id',
        'exact_running_artifact_sha256',
        'exact_running_source_commit',
        'feature',
        'migration27_state',
        'readiness_attestation_sha256',
        'runtime_class',
        'schema_version',
        'secrets_embedded',
        'selection_fingerprint_sha256',
        'trusted_ingestion',
    ];
    $actualKeys = array_keys($result);
    sort($actualKeys, SORT_STRING);
    sort($expectedKeys, SORT_STRING);
    $assert($actualKeys === $expectedKeys, 'exact producer-compatible response field set');
    $assert($reader->reads === 1, 'single database identity read');
    $assert($result['binding_state'] === 'VERIFIED_SELECTED_TARGET_DATABASE', 'verified binding state');
    $assert($result['migration27_state'] === 'NOT_EXECUTED', 'pre-migration state');
    $assert($result['attestation_mode'] === 'READ_ONLY', 'read-only mode');
    $assert($result['database_identity_source'] === 'ACTIVE_APPLICATION_DATABASE_CONNECTION', 'active application database identity');
    $assert($result['secrets_embedded'] === false, 'secret-free response');

    $payload = [
        'database_name' => 'oneqay_stage',
        'server_hostname' => 'db-stage-01',
        'server_port' => 3306,
    ];
    ksort($payload, SORT_STRING);
    $expectedFingerprint = hash('sha256', json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ));
    $assert(hash_equals($expectedFingerprint, $result['database_binding_sha256']), 'canonical database fingerprint');

    $invalid = $manifest;
    $invalid['runtime_class'] = 'preview';
    $writeManifest($invalid);
    $expectRejected(
        static fn () => (new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath))->attest(),
        'synthetic Preview runtime rejected',
    );

    $invalid = $manifest;
    $invalid['secrets_embedded'] = true;
    $writeManifest($invalid);
    $expectRejected(
        static fn () => (new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath))->attest(),
        'secret-bearing manifest rejected',
    );

    $writeManifest($manifest, 0644);
    $expectRejected(
        static fn () => (new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath))->attest(),
        'group/world-readable manifest rejected',
    );

    @unlink($manifestPath);
    $target = $root.'/target.json';
    file_put_contents($target, json_encode($manifest, JSON_THROW_ON_ERROR));
    chmod($target, 0600);
    symlink($target, $manifestPath);
    $expectRejected(
        static fn () => (new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath))->attest(),
        'symlink manifest rejected',
    );

    fwrite(STDOUT, "Final Shift Close runtime DB binding attestation regression passed.\n");
} finally {
    @unlink($manifestPath);
    @unlink($root.'/target.json');
    @rmdir($root);
}
