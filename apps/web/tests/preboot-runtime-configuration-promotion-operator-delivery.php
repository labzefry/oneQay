<?php

declare(strict_types=1);

// Author by Lab | zefry

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint191 regression failed: '.$message);
    }
};

$root = dirname(__DIR__, 3);
$publicInstallerPath = $root.'/tools/installation/public-installer.php';
$buildScriptPath = $root.'/tools/build-m7-5-preview-release.sh';
$executorPath = __DIR__.'/../app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionExecution.php';

$publicInstaller = (string) file_get_contents($publicInstallerPath);
$buildScript = (string) file_get_contents($buildScriptPath);
$executor = (string) file_get_contents($executorPath);

$assert($publicInstaller !== '', 'public installer source is unavailable.');
$assert($buildScript !== '', 'M7.5 build source is unavailable.');
$assert($executor !== '', 'Sprint190 executor source is unavailable.');

$assert(
    str_contains($publicInstaller, "PrebootRuntimeConfigurationPromotionExecution.php"),
    'public installer does not require the governed executor.',
);
$assert(
    str_contains($publicInstaller, 'new \\App\\Infrastructure\\Installation\\PrebootRuntimeConfigurationPromotionExecution'),
    'public installer does not instantiate the governed executor.',
);
$assert(
    substr_count($publicInstaller, '$execution->execute($approvalToken)') === 1,
    'public installer must register exactly one executor invocation.',
);

$actionNeedle = "} elseif (\$action === 'execute_runtime_promotion') {";
$confirmationNeedle = "hash_equals('PROMOTE_RUNTIME_CONFIGURATION', \$confirmation)";
$executeNeedle = '$executionResult = $execution->execute($approvalToken);';

$actionPosition = strpos($publicInstaller, $actionNeedle);
$confirmationPosition = strpos($publicInstaller, $confirmationNeedle);
$executePosition = strpos($publicInstaller, $executeNeedle);

$assert(is_int($actionPosition), 'guarded execution action branch is missing.');
$assert(is_int($confirmationPosition), 'exact confirmation guard is missing.');
$assert(is_int($executePosition), 'executor invocation is missing.');
$assert(
    $actionPosition < $confirmationPosition && $confirmationPosition < $executePosition,
    'executor invocation is not ordered behind action and confirmation guards.',
);

$assert(
    str_contains($publicInstaller, '<?php if ($promotionExecutionReady && ! $runtimePromoted): ?>'),
    'execution form is not gated by durable readiness.',
);
$assert(
    str_contains($publicInstaller, 'name="action" value="execute_runtime_promotion"'),
    'operator action field is missing.',
);
$assert(
    str_contains($publicInstaller, 'id="execution_approval_token" name="approval_token" type="password"'),
    'operator token is not a password field.',
);
$assert(
    str_contains($publicInstaller, 'id="promotion_confirmation" name="promotion_confirmation" type="text"'),
    'operator confirmation field is missing.',
);
$assert(
    str_contains($publicInstaller, 'placeholder="PROMOTE_RUNTIME_CONFIGURATION"'),
    'operator confirmation phrase is not visible.',
);
$assert(
    str_contains($publicInstaller, 'Promote runtime configuration'),
    'operator promotion action label is missing.',
);
$assert(
    str_contains($publicInstaller, 'PROMOTED / NOT ACTIVATED'),
    'post-promotion operator state is missing.',
);
$assert(
    str_contains($publicInstaller, 'ACTIVE / NOT ACTIVATED'),
    'runtime configuration boundary state is missing.',
);
$assert(
    str_contains($publicInstaller, 'Migration</span><strong>NOT EXECUTED'),
    'migration NO-GO is missing from the operator surface.',
);
$assert(
    str_contains($publicInstaller, 'Technical Preview</span><strong>NOT AUTHORIZED'),
    'Technical Preview NO-GO is missing from the operator surface.',
);
$assert(
    str_contains($publicInstaller, 'Production</span><strong>NOT AUTHORIZED'),
    'Production NO-GO is missing from the operator surface.',
);
$assert(
    ! str_contains($publicInstaller, '<?= e($approvalToken)'),
    'approval token can be echoed into the operator UI.',
);
$assert(
    str_contains($publicInstaller, "form-action 'self'"),
    'installer form-action CSP boundary is missing.',
);

$assert(
    str_contains($buildScript, '"promotion_executor_registration_state": "REGISTERED_GUARDED_OPERATOR_ACTION"'),
    'release metadata does not declare guarded operator registration.',
);
$assert(
    str_contains($buildScript, '"promotion_operator_action": "execute_runtime_promotion"'),
    'release metadata does not bind the operator action.',
);
$assert(
    str_contains($buildScript, '"promotion_operator_confirmation_required": true'),
    'release metadata does not require operator confirmation.',
);
$assert(
    str_contains($buildScript, '"promotion_execution_state": "NOT_EXECUTED"'),
    'build-time promotion execution state crossed the NO-GO boundary.',
);
$assert(
    str_contains($buildScript, '"activation_authorized": false'),
    'build metadata crossed activation authority boundary.',
);

foreach ([
    "'technical_preview_authorized' => false",
    "'production_authorized' => false",
    "'deployment_authorized' => false",
    "'updater_authorized' => false",
    "'migration_execution_authorized' => false",
] as $boundary) {
    $assert(str_contains($executor, $boundary), 'executor lost boundary '.$boundary.'.');
}

foreach (['Artisan::call', 'requestInstall(', 'checkAvailability('] as $forbidden) {
    $assert(! str_contains($publicInstaller, $forbidden), 'public installer contains forbidden primitive '.$forbidden.'.');
    $assert(! str_contains($executor, $forbidden), 'executor contains forbidden primitive '.$forbidden.'.');
}

$assert(
    ! str_contains($publicInstaller, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"'),
    'public installer enables Technical Preview.',
);
$assert(
    ! str_contains($publicInstaller, 'ONEQAY_PERSISTENCE_ENABLED="true"'),
    'public installer enables persistence.',
);

fwrite(STDOUT, "Sprint191 runtime configuration promotion operator delivery regression passed.\n");
