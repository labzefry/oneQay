<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionExecution;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionReadiness;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint190 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s190-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-190abc190abc';
$now = 1790001000;
$installationToken = 's190-install.'.str_repeat('H', 48);
$approvalToken = 's190-approve.'.str_repeat('J', 48);
$password = 'sprint190-db-secret';

$remove = null;
$remove = static function (string $path) use (&$remove): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        $remove($item->getPathname());
    }
    @rmdir($path);
};

$writeJson = static function (string $path, array $payload): void {
    $encoded = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    if (file_put_contents($path, $encoded, LOCK_EX) === false) {
        throw new RuntimeException('Unable to write Sprint190 JSON fixture.');
    }
    @chmod($path, 0600);
};

try {
    $assert(mkdir($install, 0700, true), 'private install boundary could not be created.');

    $writeJson($install.DIRECTORY_SEPARATOR.'authority.json', [
        'schema_version' => 1,
        'product' => 'oneQay',
        'release_id' => $releaseId,
        'token_sha256' => hash('sha256', $installationToken),
        'expires_at' => $now + 900,
        'activation_authorized' => false,
        'attribution' => 'Lab | zefry',
    ]);

    $installer = new PrebootInstallationConfiguration(
        $shared,
        $releaseId,
        $now,
        static fn (array $configuration): array => [
            'ready' => true,
            'facts' => [
                'connected' => true,
                'engine' => 'mysql',
                'server_version' => '8.0.36',
                'charset' => 'utf8mb4',
                'timezone' => '+00:00',
                'schema_state' => 'empty',
                'least_privilege' => true,
            ],
        ],
    );

    $prepared = $installer->prepare([
        'installation_token' => $installationToken,
        'app_url' => 'https://preview.example.test',
        'db_host' => 'db.internal.example',
        'db_port' => '3306',
        'db_database' => 'oneqay_preview',
        'db_username' => 'oneqay_runtime',
        'db_password' => $password,
    ]);
    $assert(($prepared['promotion_request_pending'] ?? false) === true, 'promotion request fixture was not created.');

    $pendingPath = $runtime.DIRECTORY_SEPARATOR.'.env.pending';
    $activePath = $runtime.DIRECTORY_SEPARATOR.'.env';
    $handoffPath = $install.DIRECTORY_SEPARATOR.'activation-readiness.json';
    $requestPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-request.json';
    $authorityPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-authority.json';
    $readinessPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-readiness.json';
    $receiptPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-execution.json';

    $pendingRaw = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request fixture is invalid.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'promotion-authority-'.str_repeat('4', 24),
        'authority_state' => 'GRANTED',
        'scope' => 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION',
        'request_id' => (string) $request['request_id'],
        'release_id' => $releaseId,
        'pending_environment_sha256' => hash('sha256', $pendingRaw),
        'activation_readiness_sha256' => hash('sha256', $handoffRaw),
        'promotion_request_sha256' => hash('sha256', $requestRaw),
        'approval_token_sha256' => hash('sha256', $approvalToken),
        'authorized_at_unix' => $now - 10,
        'expires_at_unix' => $now + 300,
        'single_use' => true,
        'promotion_authorized' => true,
        'migration_execution_authorized' => false,
        'technical_preview_authorized' => false,
        'production_authorized' => false,
        'updater_authorized' => false,
        'deployment_authorized' => false,
        'attribution' => 'Lab | zefry',
    ];
    $writeJson($authorityPath, $authority);
    $authorityRaw = (string) file_get_contents($authorityPath);

    $readiness = new PrebootRuntimeConfigurationPromotionReadiness($shared, $releaseId, $now);
    $attested = $readiness->attest($approvalToken);
    $assert(($attested['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'Sprint189 readiness fixture failed.');

    $readinessRaw = (string) file_get_contents($readinessPath);
    $requestBefore = (string) file_get_contents($requestPath);
    $handoffBefore = (string) file_get_contents($handoffPath);
    $authorityBefore = (string) file_get_contents($authorityPath);

    $executor = new PrebootRuntimeConfigurationPromotionExecution($shared, $releaseId, $now);

    $wrongDenied = false;
    try {
        $executor->execute('wrong-token-'.str_repeat('Z', 40));
    } catch (RuntimeException $exception) {
        $wrongDenied = $exception->getMessage() === 'promotion_authority_denied';
    }
    $assert($wrongDenied, 'wrong approval token did not fail closed.');
    $assert(is_file($pendingPath), 'wrong token consumed pending configuration.');
    $assert(! file_exists($activePath), 'wrong token created active environment.');
    $assert(! file_exists($receiptPath), 'wrong token created execution receipt.');

    $tamperedReadiness = json_decode($readinessRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedReadiness['qualification_fingerprint'] = str_repeat('0', 64);
    $writeJson($readinessPath, $tamperedReadiness);

    $tamperDenied = false;
    try {
        $executor->execute($approvalToken);
    } catch (RuntimeException $exception) {
        $tamperDenied = in_array($exception->getMessage(), [
            'promotion_execution_binding_mismatch',
            'promotion_execution_readiness_required',
        ], true);
    }
    $assert($tamperDenied, 'tampered readiness did not fail closed.');
    $assert(! file_exists($activePath), 'tampered readiness created active environment.');
    $assert(is_file($pendingPath), 'tampered readiness consumed pending environment.');

    if (file_put_contents($readinessPath, $readinessRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore readiness fixture.');
    }
    @chmod($readinessPath, 0600);

    $executed = $executor->execute($approvalToken);
    $assert(($executed['state'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED', 'execution state is invalid.');
    $assert(($executed['technical_preview_authorized'] ?? true) === false, 'execution authorized Technical Preview.');
    $assert(($executed['production_authorized'] ?? true) === false, 'execution authorized Production.');

    $assert(is_file($activePath), 'active runtime configuration was not materialized.');
    $assert(! file_exists($pendingPath), 'pending runtime configuration was not consumed.');
    $assert(is_file($receiptPath), 'execution receipt was not written.');

    $activeRaw = (string) file_get_contents($activePath);
    $assert($activeRaw === $pendingRaw, 'active configuration bytes differ from verified pending bytes.');
    $assert(($executed['active_environment_sha256'] ?? null) === hash('sha256', $pendingRaw), 'active environment digest is invalid.');

    $activePermissions = fileperms($activePath);
    $receiptPermissions = fileperms($receiptPath);
    $assert(is_int($activePermissions) && ($activePermissions & 0077) === 0, 'active environment is not private.');
    $assert(is_int($receiptPermissions) && ($receiptPermissions & 0077) === 0, 'execution receipt is not private.');

    foreach ([
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
        'ONEQAY_PERSISTENCE_ENABLED="false"',
        'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="false"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
        'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"',
    ] as $required) {
        $assert(str_contains($activeRaw, $required), 'active environment lost safety marker '.$required.'.');
    }

    foreach ([
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"',
        'ONEQAY_PERSISTENCE_ENABLED="true"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="true"',
    ] as $forbidden) {
        $assert(! str_contains($activeRaw, $forbidden), 'active environment crossed activation boundary '.$forbidden.'.');
    }

    $assert((string) file_get_contents($requestPath) === $requestBefore, 'execution mutated promotion request.');
    $assert((string) file_get_contents($handoffPath) === $handoffBefore, 'execution mutated activation handoff.');
    $assert((string) file_get_contents($authorityPath) === $authorityBefore, 'execution mutated authority evidence.');
    $assert((string) file_get_contents($readinessPath) === $readinessRaw, 'execution mutated readiness evidence.');

    $receiptRaw = (string) file_get_contents($receiptPath);
    $receipt = json_decode($receiptRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($receipt), 'execution receipt is invalid JSON.');
    $assert(($receipt['execution_state'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED', 'receipt state invalid.');
    $assert(($receipt['pending_environment_sha256'] ?? null) === hash('sha256', $pendingRaw), 'receipt pending digest invalid.');
    $assert(($receipt['active_environment_sha256'] ?? null) === hash('sha256', $activeRaw), 'receipt active digest invalid.');
    $assert(($receipt['activation_readiness_sha256'] ?? null) === hash('sha256', $handoffBefore), 'receipt handoff digest invalid.');
    $assert(($receipt['promotion_request_sha256'] ?? null) === hash('sha256', $requestBefore), 'receipt request digest invalid.');
    $assert(($receipt['promotion_authority_sha256'] ?? null) === hash('sha256', $authorityBefore), 'receipt authority digest invalid.');
    $assert(($receipt['promotion_readiness_sha256'] ?? null) === hash('sha256', $readinessRaw), 'receipt readiness digest invalid.');
    $assert(($receipt['authority_consumption_state'] ?? null) === 'CONSUMED_BY_EXECUTION_RECEIPT', 'authority consumption state invalid.');
    $assert(($receipt['readiness_consumption_state'] ?? null) === 'CONSUMED_BY_EXECUTION_RECEIPT', 'readiness consumption state invalid.');
    $assert(($receipt['pending_environment_state'] ?? null) === 'REMOVED_AFTER_VERIFIED_PROMOTION', 'pending consumption state invalid.');

    foreach ([
        'migration_execution_authorized',
        'technical_preview_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($receipt[$flag] ?? null) === false, 'execution receipt crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($receiptRaw, $secret), 'plaintext secret leaked into execution receipt.');
    }

    $replayDenied = false;
    try {
        $executor->execute($approvalToken);
    } catch (RuntimeException $exception) {
        $replayDenied = $exception->getMessage() === 'active_environment_already_present';
    }
    $assert($replayDenied, 'execution replay was not denied.');

    $schemaPath = __DIR__.'/../../../tools/installation/runtime-configuration-promotion-execution.schema.json';
    $schema = json_decode((string) file_get_contents($schemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($schema), 'execution schema is invalid JSON.');
    $assert(($schema['properties']['execution_state']['const'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED', 'schema execution state invalid.');
    $assert(($schema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'schema crossed Technical Preview boundary.');
    $assert(($schema['properties']['production_authorized']['const'] ?? null) === false, 'schema crossed Production boundary.');

    $source = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionExecution.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach (['Artisan::call', 'migrate'] as $forbidden) {
        $assert(! str_contains($source, $forbidden), 'execution source contains forbidden primitive '.$forbidden.'.');
    }

    $assert(! str_contains($publicInstaller, 'PrebootRuntimeConfigurationPromotionExecution.php'), 'public installer registered Sprint190 executor.');
    $assert(! str_contains($publicInstaller, 'execute_promotion'), 'public installer exposes Sprint190 execution action.');

    fwrite(STDOUT, "Sprint190 runtime configuration atomic promotion executor regression passed.\n");
} finally {
    $remove($root);
}
