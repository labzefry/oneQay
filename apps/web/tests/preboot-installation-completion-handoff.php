<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationCompletionHandoff;
use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPostPromotionVerification;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionExecution;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionReadiness;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint193 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s193-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-193abc193abc';
$now = 1790003000;
$installationToken = 's193-install.'.str_repeat('H', 48);
$approvalToken = 's193-approve.'.str_repeat('J', 48);
$password = 'sprint193-db-secret';

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
        throw new RuntimeException('Unable to write Sprint193 JSON fixture.');
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
    $receiptPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-execution.json';
    $verificationPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-post-promotion-verification.json';
    $completionPath = $install.DIRECTORY_SEPARATOR.'installation-completion.json';

    $pendingRaw = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request fixture is invalid.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'promotion-authority-'.str_repeat('7', 24),
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

    $readiness = new PrebootRuntimeConfigurationPromotionReadiness($shared, $releaseId, $now);
    $assert(($readiness->attest($approvalToken)['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'readiness fixture failed.');

    $executor = new PrebootRuntimeConfigurationPromotionExecution($shared, $releaseId, $now);
    $assert(($executor->execute($approvalToken)['state'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED', 'promotion execution fixture failed.');

    $verification = new PrebootRuntimeConfigurationPostPromotionVerification($shared, $releaseId, $now);
    $assert(($verification->verify()['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED', 'post-promotion verification fixture failed.');

    $activeRaw = (string) file_get_contents($activePath);
    $receiptRaw = (string) file_get_contents($receiptPath);
    $verificationRaw = (string) file_get_contents($verificationPath);

    $completion = new PrebootInstallationCompletionHandoff($shared, $releaseId, $now);
    $before = $completion->inspect();
    $assert(($before['state'] ?? null) === 'INSTALLATION_COMPLETION_READY_TO_SEAL', 'completion was not ready to seal.');
    $assert(($before['complete'] ?? true) === false, 'unsealed completion reported complete.');

    $sealed = $completion->seal();
    $assert(($sealed['state'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED', 'completion state invalid.');
    $assert(($sealed['complete'] ?? false) === true, 'completion did not report complete.');
    $assert(($sealed['activation_authorized'] ?? true) === false, 'completion granted activation authority.');
    $assert(is_file($completionPath), 'completion evidence was not written.');

    $permissions = fileperms($completionPath);
    $assert(is_int($permissions) && ($permissions & 0077) === 0, 'completion evidence is not private.');

    $completionRaw = (string) file_get_contents($completionPath);
    $payload = json_decode($completionRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($payload), 'completion evidence is invalid JSON.');
    $assert(($payload['release_id'] ?? null) === $releaseId, 'completion release binding invalid.');
    $assert(($payload['request_id'] ?? null) === $request['request_id'], 'completion request binding invalid.');
    $assert(($payload['authority_id'] ?? null) === $authority['authority_id'], 'completion authority binding invalid.');
    $assert(($payload['execution_receipt_sha256'] ?? null) === hash('sha256', $receiptRaw), 'completion receipt binding invalid.');
    $assert(($payload['post_promotion_verification_sha256'] ?? null) === hash('sha256', $verificationRaw), 'completion verification binding invalid.');
    $assert(($payload['active_environment_sha256'] ?? null) === hash('sha256', $activeRaw), 'completion active binding invalid.');
    $assert(($payload['installer_reentry_state'] ?? null) === 'READ_ONLY_COMPLETION', 'installer re-entry state invalid.');

    foreach ([
        'migration_execution_authorized',
        'technical_preview_authorized',
        'persistence_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($payload[$flag] ?? null) === false, 'completion crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($completionRaw, $secret), 'plaintext secret leaked into completion evidence.');
    }

    $assert((string) file_get_contents($activePath) === $activeRaw, 'completion mutated active environment.');
    $assert((string) file_get_contents($receiptPath) === $receiptRaw, 'completion mutated execution receipt.');
    $assert((string) file_get_contents($verificationPath) === $verificationRaw, 'completion mutated verification evidence.');

    $inspection = $completion->inspect();
    $assert(($inspection['state'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED', 'valid completion not recognized.');
    $assert(($inspection['complete'] ?? false) === true, 'valid completion not reported complete.');

    $replayed = $completion->seal();
    $assert(($replayed['completion_sha256'] ?? null) === hash('sha256', $completionRaw), 'completion replay changed evidence.');

    if (file_put_contents($activePath, $activeRaw."\n# tampered\n", LOCK_EX) === false) {
        throw new RuntimeException('Unable to tamper active environment fixture.');
    }
    @chmod($activePath, 0600);
    $assert(($completion->inspect()['state'] ?? null) === 'INSTALLATION_COMPLETION_NOT_READY', 'active tamper did not invalidate completion context.');

    if (file_put_contents($activePath, $activeRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore active environment fixture.');
    }
    @chmod($activePath, 0600);

    $tamperedVerification = json_decode($verificationRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedVerification['authority_id'] = 'promotion-authority-'.str_repeat('8', 24);
    $writeJson($verificationPath, $tamperedVerification);
    $assert(($completion->inspect()['state'] ?? null) === 'INSTALLATION_COMPLETION_NOT_READY', 'verification tamper did not invalidate completion context.');

    if (file_put_contents($verificationPath, $verificationRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore verification fixture.');
    }
    @chmod($verificationPath, 0600);

    $wrongRelease = new PrebootInstallationCompletionHandoff($shared, 'm75-preview-ffffffffffff', $now);
    $assert(($wrongRelease->inspect()['state'] ?? null) === 'INSTALLATION_COMPLETION_NOT_READY', 'wrong release did not fail closed.');

    $schemaPath = __DIR__.'/../../../tools/installation/installation-completion-handoff.schema.json';
    $schema = json_decode((string) file_get_contents($schemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($schema), 'completion schema invalid.');
    $assert(($schema['properties']['completion_state']['const'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED', 'schema completion state invalid.');
    $assert(($schema['properties']['installer_reentry_state']['const'] ?? null) === 'READ_ONLY_COMPLETION', 'schema re-entry state invalid.');
    $assert(($schema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'schema crossed Technical Preview boundary.');
    $assert(($schema['properties']['production_authorized']['const'] ?? null) === false, 'schema crossed Production boundary.');

    $source = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootInstallationCompletionHandoff.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach (['Artisan::call', 'requestInstall(', 'checkAvailability('] as $forbidden) {
        $assert(! str_contains($source, $forbidden), 'completion source contains forbidden primitive '.$forbidden.'.');
        $assert(! str_contains($publicInstaller, $forbidden), 'installer contains forbidden primitive '.$forbidden.'.');
    }

    $assert(str_contains($publicInstaller, 'PrebootInstallationCompletionHandoff.php'), 'installer does not register completion handoff.');
    $assert(str_contains($publicInstaller, '$completionResult = $completionHandoff->seal();'), 'installer does not seal completion.');
    $assert(str_contains($publicInstaller, 'seal_installation_completion'), 'installer lacks completion retry action.');
    $assert(str_contains($publicInstaller, 'SEAL_INSTALLATION_COMPLETION'), 'installer lacks exact completion confirmation.');
    $assert(str_contains($publicInstaller, 'COMPLETE / NOT ACTIVATED'), 'installer lacks complete-not-activated state.');

    fwrite(STDOUT, "Sprint193 installation completion handoff regression passed.\n");
} finally {
    $remove($root);
}
