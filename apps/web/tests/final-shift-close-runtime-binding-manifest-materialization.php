<?php

declare(strict_types=1);

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeBindingManifest.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeBindingManifestWriter.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeBindingManifestMaterializationRequest.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseRuntimeBindingManifestMaterializer.php';

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationRequest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;

final class CapturingRuntimeBindingManifestWriter implements FinalShiftCloseRuntimeBindingManifestWriter
{
    public ?FinalShiftCloseRuntimeBindingManifest $manifest = null;

    public function write(FinalShiftCloseRuntimeBindingManifest $manifest): void
    {
        $this->manifest = $manifest;
    }
}

function expectThrows(callable $callback): void
{
    try {
        $callback();
    } catch (Throwable) {
        return;
    }

    throw new RuntimeException('Expected exception was not thrown.');
}

$root = sys_get_temp_dir().'/oneqay-sprint121-'.bin2hex(random_bytes(8));
if (! mkdir($root, 0700, true) && ! is_dir($root)) {
    throw new RuntimeException('Cannot create Sprint121 test directory.');
}
$selectionPath = $root.'/selection.json';
$fingerprint = str_repeat('a', 64);
$payload = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'durable-stage-01',
        'runtime_class' => 'durable-stage',
        'exact_running_source_commit' => str_repeat('b', 40),
        'exact_running_artifact_sha256' => str_repeat('c', 64),
        'readiness_attestation_sha256' => str_repeat('d', 64),
        'selection_fingerprint_sha256' => $fingerprint,
        'trusted_ingestion' => [
            'run_id' => 123,
            'run_attempt' => 1,
            'ingestion_fingerprint_sha256' => str_repeat('e', 64),
        ],
    ],
];
file_put_contents($selectionPath, json_encode($payload, JSON_THROW_ON_ERROR));
chmod($selectionPath, 0600);

$writer = new CapturingRuntimeBindingManifestWriter();
$service = new FinalShiftCloseRuntimeBindingManifestMaterializer($writer, $selectionPath);
$result = $service->materialize(new FinalShiftCloseRuntimeBindingManifestMaterializationRequest(
    'materialize-op-0001',
    $fingerprint,
));

if (($result['materialization_state'] ?? null) !== 'MATERIALIZED_SELECTED_TARGET_BINDING_MANIFEST') {
    throw new RuntimeException('Materialization state mismatch.');
}
if ($writer->manifest === null) {
    throw new RuntimeException('Writer was not called.');
}
$manifest = $writer->manifest->toArray();
if (($manifest['environment_id'] ?? null) !== 'durable-stage-01'
    || ($manifest['selection_fingerprint_sha256'] ?? null) !== $fingerprint
    || ($manifest['secrets_embedded'] ?? null) !== false
) {
    throw new RuntimeException('Materialized manifest provenance mismatch.');
}

expectThrows(fn () => $service->materialize(new FinalShiftCloseRuntimeBindingManifestMaterializationRequest(
    'materialize-op-0002',
    str_repeat('f', 64),
)));

$blocked = $payload;
$blocked['selection_state'] = 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET';
$blocked['selected_target'] = null;
file_put_contents($selectionPath, json_encode($blocked, JSON_THROW_ON_ERROR));
expectThrows(fn () => $service->materialize(new FinalShiftCloseRuntimeBindingManifestMaterializationRequest(
    'materialize-op-0003',
    $fingerprint,
)));

$extra = $payload;
$extra['selected_target']['caller_supplied_target'] = 'forbidden';
file_put_contents($selectionPath, json_encode($extra, JSON_THROW_ON_ERROR));
expectThrows(fn () => $service->materialize(new FinalShiftCloseRuntimeBindingManifestMaterializationRequest(
    'materialize-op-0004',
    $fingerprint,
)));

expectThrows(fn () => new FinalShiftCloseRuntimeBindingManifestMaterializationRequest('bad', $fingerprint));
expectThrows(fn () => new FinalShiftCloseRuntimeBindingManifestMaterializationRequest('materialize-op-0005', 'bad'));

@unlink($selectionPath);
@rmdir($root);

echo "Sprint121 runtime binding manifest materialization regression: OK\n";
