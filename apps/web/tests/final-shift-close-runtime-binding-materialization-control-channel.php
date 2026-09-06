<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingMaterializationRequest;

require dirname(__DIR__).'/vendor/autoload.php';

function expectFailure(callable $operation, string $label): void
{
    try {
        $operation();
    } catch (\InvalidArgumentException) {
        return;
    }

    throw new RuntimeException('Expected failure: '.$label);
}

$manifest = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'environment_id' => 'isolated-durable-staging-01',
    'runtime_class' => 'durable-staging',
    'exact_running_source_commit' => str_repeat('a', 40),
    'exact_running_artifact_sha256' => str_repeat('b', 64),
    'readiness_attestation_sha256' => str_repeat('c', 64),
    'selection_fingerprint_sha256' => str_repeat('d', 64),
    'trusted_ingestion' => [
        'run_id' => 12345,
        'run_attempt' => 1,
        'ingestion_fingerprint_sha256' => str_repeat('e', 64),
    ],
    'secrets_embedded' => false,
];

$canonicalManifest = new App\Application\Pos\FinalShiftCloseRuntimeBindingManifest($manifest);
$manifestSha256 = hash('sha256', $canonicalManifest->toCanonicalJson());
$payload = [
    'schema_version' => 1,
    'operation' => 'final-shift-close.runtime-binding.materialize',
    'operation_id' => 'fscbind_'.substr($manifestSha256, 0, 32),
    'manifest_sha256' => $manifestSha256,
    'manifest' => $manifest,
];

$request = new FinalShiftCloseRuntimeBindingMaterializationRequest($payload);
if ($request->operationId() !== $payload['operation_id']
    || $request->manifestSha256() !== $manifestSha256
    || $request->manifest()->toCanonicalJson() !== $canonicalManifest->toCanonicalJson()
) {
    throw new RuntimeException('Valid materialization request did not round-trip deterministically.');
}

$tamperedFingerprint = $payload;
$tamperedFingerprint['manifest_sha256'] = str_repeat('f', 64);
expectFailure(
    fn () => new FinalShiftCloseRuntimeBindingMaterializationRequest($tamperedFingerprint),
    'tampered manifest fingerprint',
);

$tamperedOperation = $payload;
$tamperedOperation['operation_id'] = 'fscbind_'.str_repeat('0', 32);
expectFailure(
    fn () => new FinalShiftCloseRuntimeBindingMaterializationRequest($tamperedOperation),
    'non-deterministic operation id',
);

$preview = $payload;
$preview['manifest']['runtime_class'] = 'preview';
expectFailure(
    fn () => new FinalShiftCloseRuntimeBindingMaterializationRequest($preview),
    'preview runtime',
);

$secretBearing = $payload;
$secretBearing['manifest']['secrets_embedded'] = true;
expectFailure(
    fn () => new FinalShiftCloseRuntimeBindingMaterializationRequest($secretBearing),
    'secret-bearing manifest',
);

$extraField = $payload;
$extraField['selected_target'] = 'caller-controlled';
expectFailure(
    fn () => new FinalShiftCloseRuntimeBindingMaterializationRequest($extraField),
    'unknown top-level field',
);

fwrite(STDOUT, "Final Shift Close runtime binding materialization control-channel regression passed.\n");
