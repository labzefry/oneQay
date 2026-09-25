<?php

declare(strict_types=1);

// Author by Lab | zefry

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint248 regression failed: '.$message);
    }
};

$workflowPath = __DIR__.'/../../../.github/workflows/final-shift-close-feature-activation.yml';
$successorPath = __DIR__.'/../../../tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php';
$baseExecutorPath = __DIR__.'/../../../tools/cpanel/execute-final-shift-close-feature-activation.php';
$statePath = __DIR__.'/../../../ops/final-shift-close/STATE.json';

$workflow = (string) file_get_contents($workflowPath);
$successor = (string) file_get_contents($successorPath);
$baseExecutor = (string) file_get_contents($baseExecutorPath);
$state = json_decode((string) file_get_contents($statePath), true, 64, JSON_THROW_ON_ERROR);

$assert(str_contains(
    $workflow,
    'cp tools/cpanel/execute-final-shift-close-feature-activation.php "$kit/"',
), 'operator kit no longer carries the base executor required by successor');
$assert(str_contains(
    $workflow,
    'cp tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php "$kit/"',
), 'operator kit does not carry the Sprint247 readiness successor');
$assert(str_contains(
    $workflow,
    '<PHP_CLI> execute-final-shift-close-feature-activation-readiness-successor.php transport-envelope.json activation-authority.json runtime-manifest.json',
), 'README invocation does not use readiness successor as top-level CLI entry');
$assert(! str_contains(
    $workflow,
    '<PHP_CLI> execute-final-shift-close-feature-activation.php transport-envelope.json activation-authority.json runtime-manifest.json',
), 'legacy base executor is still advertised as top-level CLI activation entry');

$assert(str_contains(
    $successor,
    "require_once __DIR__.'/execute-final-shift-close-feature-activation.php';",
), 'readiness successor no longer binds to base executor');
$assert(str_contains(
    $successor,
    'fscActivationFetchCanonicalDurableStagingReadiness',
), 'canonical readiness mapper is not used by successor execution');
$assert(str_contains(
    $successor,
    'fscActivationExecuteReadinessSuccessor',
), 'successor execution entry is missing');
$assert(str_contains(
    $baseExecutor,
    'RESTORE_EXACT_PREWRITE_BYTES_ON_ANY_POST_WRITE_FAILURE',
), 'fail-closed exact-byte rollback contract drifted');

$assert(($state['migration27']['state'] ?? null) === 'EXECUTED', 'migration #27 state drifted');
$assert(($state['permission_provisioning']['state'] ?? null) === 'PROVISIONED', 'permission provisioning state drifted');
$assert(($state['permission_provisioning']['default_grant'] ?? null) === 'NONE', 'permission default grant drifted');
$assert(($state['feature_activation']['state'] ?? null) === 'ACTIVE', 'post-activation canonical state drifted');
$assert(($state['deployment_authority'] ?? null) === 'NOT_GRANTED', 'deployment authority drifted');
$assert(($state['technical_preview_activation'] ?? null) === 'NOT_AUTHORIZED', 'Technical Preview authority drifted');
$assert(($state['production_activation'] ?? null) === 'NOT_AUTHORIZED', 'Production authority drifted');
$assert(($state['updater_activation'] ?? null) === 'INACTIVE', 'updater state drifted');

foreach (['Artisan::call', 'production_activation = true', 'runtime_allowlist_change_allowed = true'] as $forbidden) {
    $assert(! str_contains($successor, $forbidden), 'successor contains forbidden primitive '.$forbidden);
}

fwrite(STDOUT, "Sprint248 Final Shift Close operator-kit successor wiring regression passed.\n");
