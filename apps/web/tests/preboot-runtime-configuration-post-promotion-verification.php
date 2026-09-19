<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPostPromotionVerification;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionExecution;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionReadiness;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint192 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s192-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-192abc192abc';
$now = 1790002000;
$installationToken = 's192-install.'.str_repeat('H', 48);
$approvalToken = 's192-approve.'.str_repeat('J', 48);
$password = 'sprint192-db-secret';

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
        throw new RuntimeException('Unable to write Sprint192 JSON fixture.');
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
    $verificationPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-post-promotion-verification.json';

    $pendingRaw = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request fixture is invalid.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'promotion-authority-'.str_repeat('5', 24),
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
    $attested = $readiness->attest($approvalToken);
    $assert(($attested['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'readiness fixture failed.');

    $executor = new PrebootRuntimeConfigurationPromotionExecution($shared, $releaseId, $now);
    $executed = $executor->execute($approvalToken);
    $assert(($executed['state'] ?? null) === 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED', 'promotion execution fixture failed.');
    $assert(is_file($activePath), 'active environment fixture missing.');
    $assert(! file_exists($pendingPath), 'pending environment survived promotion.');
    $assert(is_file($receiptPath), 'execution receipt fixture missing.');

    $activeRaw = (string) file_get_contents($activePath);
    $receiptRaw = (string) file_get_contents($receiptPath);

    $verification = new PrebootRuntimeConfigurationPostPromotionVerification($shared, $releaseId, $now);
    $before = $verification->inspect();
    $assert(($before['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFICATION_REQUIRED', 'missing verification was not reported.');
    $assert(($before['verified'] ?? true) === false, 'missing verification reported verified.');

    $verified = $verification->verify();
    $assert(($verified['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED', 'verification state invalid.');
    $assert(($verified['verified'] ?? false) === true, 'verification did not report success.');
    $assert(($verified['activation_authorized'] ?? true) === false, 'verification granted activation authority.');
    $assert(($verified['active_environment_sha256'] ?? null) === hash('sha256', $activeRaw), 'verified active SHA-256 invalid.');
    $assert(is_file($verificationPath), 'verification evidence was not written.');

    $permissions = fileperms($verificationPath);
    $assert(is_int($permissions) && ($permissions & 0077) === 0, 'verification evidence is not private.');

    $verificationRaw = (string) file_get_contents($verificationPath);
    $payload = json_decode($verificationRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($payload), 'verification evidence is invalid JSON.');
    $assert(($payload['release_id'] ?? null) === $releaseId, 'verification release binding invalid.');
    $assert(($payload['request_id'] ?? null) === $request['request_id'], 'verification request binding invalid.');
    $assert(($payload['authority_id'] ?? null) === $authority['authority_id'], 'verification authority binding invalid.');
    $assert(($payload['execution_receipt_sha256'] ?? null) === hash('sha256', $receiptRaw), 'verification receipt binding invalid.');
    $assert(($payload['active_environment_sha256'] ?? null) === hash('sha256', $activeRaw), 'verification active binding invalid.');
    $assert(($payload['pending_environment_absent'] ?? null) === true, 'verification does not prove pending absence.');
    $assert(($payload['runtime_configuration_active'] ?? null) === true, 'verification does not prove active configuration.');

    foreach ([
        'migration_execution_authorized',
        'technical_preview_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($payload[$flag] ?? null) === false, 'verification crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($verificationRaw, $secret), 'plaintext secret leaked into verification evidence.');
    }

    $assert((string) file_get_contents($activePath) === $activeRaw, 'verification mutated active environment.');
    $assert((string) file_get_contents($receiptPath) === $receiptRaw, 'verification mutated execution receipt.');

    $inspection = $verification->inspect();
    $assert(($inspection['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED', 'valid verification not recognized.');
    $assert(($inspection['verified'] ?? false) === true, 'valid verification not reported verified.');

    $replayed = $verification->verify();
    $assert(($replayed['verification_sha256'] ?? null) === hash('sha256', $verificationRaw), 'verification replay changed evidence.');

    if (file_put_contents($activePath, $activeRaw."\n# tampered\n", LOCK_EX) === false) {
        throw new RuntimeException('Unable to tamper active environment fixture.');
    }
    @chmod($activePath, 0600);
    $assert(($verification->inspect()['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFICATION_INVALID', 'active environment tamper did not invalidate verification.');

    if (file_put_contents($activePath, $activeRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore active environment fixture.');
    }
    @chmod($activePath, 0600);

    $tamperedReceipt = json_decode($receiptRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedReceipt['authority_id'] = 'promotion-authority-'.str_repeat('6', 24);
    $writeJson($receiptPath, $tamperedReceipt);
    $assert(($verification->inspect()['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFICATION_INVALID', 'receipt tamper did not invalidate verification.');

    if (file_put_contents($receiptPath, $receiptRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore receipt fixture.');
    }
    @chmod($receiptPath, 0600);

    $wrongRelease = new PrebootRuntimeConfigurationPostPromotionVerification($shared, 'm75-preview-ffffffffffff', $now);
    $assert(($wrongRelease->inspect()['state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFICATION_INVALID', 'wrong release did not fail closed.');

    $schemaPath = __DIR__.'/../../../tools/installation/runtime-configuration-post-promotion-verification.schema.json';
    $schema = json_decode((string) file_get_contents($schemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($schema), 'verification schema invalid.');
    $assert(($schema['properties']['verification_state']['const'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED', 'schema verification state invalid.');
    $assert(($schema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'schema crossed Technical Preview boundary.');
    $assert(($schema['properties']['production_authorized']['const'] ?? null) === false, 'schema crossed Production boundary.');

    $source = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootRuntimeConfigurationPostPromotionVerification.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach (['Artisan::call', 'requestInstall(', 'checkAvailability('] as $forbidden) {
        $assert(! str_contains($source, $forbidden), 'verification source contains forbidden primitive '.$forbidden.'.');
        $assert(! str_contains($publicInstaller, $forbidden), 'installer contains forbidden primitive '.$forbidden.'.');
    }

    $assert(str_contains($publicInstaller, 'PrebootRuntimeConfigurationPostPromotionVerification.php'), 'installer does not register verifier.');
    $assert(str_contains($publicInstaller, '$postPromotionVerificationResult = $postPromotionVerification->verify();'), 'installer does not seal verification after promotion.');
    $assert(str_contains($publicInstaller, 'verify_promoted_runtime_configuration'), 'installer lacks verification retry action.');
    $assert(str_contains($publicInstaller, 'VERIFY_RUNTIME_CONFIGURATION'), 'installer lacks exact verification confirmation.');
    $assert(str_contains($publicInstaller, 'VERIFIED / NOT ACTIVATED'), 'installer lacks verified-not-activated state.');

    fwrite(STDOUT, "Sprint192 runtime configuration post-promotion verification regression passed.\n");
} finally {
    $remove($root);
}
