<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionReadiness;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint189 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s189-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-189abc189abc';
$now = 1790000500;
$installationToken = 's189-install.'.str_repeat('H', 48);
$approvalToken = 's189-approve.'.str_repeat('J', 48);
$password = 'sprint189-db-secret';

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
        throw new RuntimeException('Unable to write Sprint189 JSON fixture.');
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

    $pending = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request fixture is invalid.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'promotion-authority-'.str_repeat('2', 24),
        'authority_state' => 'GRANTED',
        'scope' => 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION',
        'request_id' => (string) $request['request_id'],
        'release_id' => $releaseId,
        'pending_environment_sha256' => hash('sha256', $pending),
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

    $missing = $readiness->inspect();
    $assert(($missing['state'] ?? null) === 'PROMOTION_READINESS_MISSING', 'missing readiness did not fail closed.');

    $wrongDenied = false;
    try {
        $readiness->attest('wrong-token-'.str_repeat('Z', 40));
    } catch (RuntimeException $exception) {
        $wrongDenied = $exception->getMessage() === 'promotion_authority_denied';
    }
    $assert($wrongDenied, 'wrong approval token created readiness.');

    $attested = $readiness->attest($approvalToken);
    $assert(($attested['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'readiness state is invalid.');
    $assert(($attested['promotion_executed'] ?? true) === false, 'readiness executed promotion.');
    $assert(preg_match('/\A[0-9a-f]{64}\z/', (string) ($attested['readiness_sha256'] ?? '')) === 1, 'readiness SHA-256 is invalid.');

    $assert(is_file($readinessPath), 'durable readiness artifact was not written.');
    $permissions = fileperms($readinessPath);
    $assert(is_int($permissions) && ($permissions & 0077) === 0, 'readiness artifact is not private.');
    $assert(! file_exists($activePath), 'readiness created active .env.');
    $assert((string) file_get_contents($pendingPath) === $pending, 'readiness mutated pending configuration.');
    $assert((string) file_get_contents($handoffPath) === $handoffRaw, 'readiness mutated handoff.');
    $assert((string) file_get_contents($requestPath) === $requestRaw, 'readiness mutated request.');
    $assert((string) file_get_contents($authorityPath) === $authorityRaw, 'readiness consumed or mutated authority.');

    $readinessRaw = (string) file_get_contents($readinessPath);
    $payload = json_decode($readinessRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($payload), 'readiness artifact is invalid JSON.');

    $assert(($payload['release_id'] ?? null) === $releaseId, 'readiness release binding is invalid.');
    $assert(($payload['request_id'] ?? null) === $request['request_id'], 'readiness request binding is invalid.');
    $assert(($payload['authority_id'] ?? null) === $authority['authority_id'], 'readiness authority binding is invalid.');
    $assert(($payload['pending_environment_sha256'] ?? null) === hash('sha256', $pending), 'pending digest binding is invalid.');
    $assert(($payload['activation_readiness_sha256'] ?? null) === hash('sha256', $handoffRaw), 'handoff digest binding is invalid.');
    $assert(($payload['promotion_request_sha256'] ?? null) === hash('sha256', $requestRaw), 'request digest binding is invalid.');
    $assert(($payload['promotion_authority_sha256'] ?? null) === hash('sha256', $authorityRaw), 'authority digest binding is invalid.');
    $assert(($payload['authority_expires_at_unix'] ?? null) === $authority['expires_at_unix'], 'authority expiry binding is invalid.');
    $assert(($payload['promotion_executed'] ?? true) === false, 'readiness artifact reports executed promotion.');

    $contract = $payload['execution_contract'] ?? null;
    $assert(is_array($contract), 'execution readiness contract missing.');
    $assert(($contract['source_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env.pending', 'source path contract invalid.');
    $assert(($contract['target_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env', 'target path contract invalid.');
    foreach ([
        'preserve_configuration_bytes_exactly',
        'active_environment_must_be_absent',
        'fresh_authority_required',
        'consume_authority_on_success',
        'consume_readiness_on_success',
        'rollback_on_post_promotion_validation_failure',
    ] as $flag) {
        $assert(($contract[$flag] ?? false) === true, 'execution contract missing '.$flag.'.');
    }

    foreach ([
        'migration_execution_authorized',
        'technical_preview_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($payload[$flag] ?? null) === false, 'readiness artifact crossed '.$flag.' boundary.');
    }

    foreach ([$password, $installationToken, $approvalToken, 'oneqay_runtime'] as $secret) {
        $assert(! str_contains($readinessRaw, $secret), 'plaintext secret leaked into readiness artifact.');
    }

    $inspection = $readiness->inspect();
    $assert(($inspection['state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'valid readiness is not execution-ready.');
    $assert(($inspection['execution_ready'] ?? false) === true, 'valid readiness was not recognized.');

    $idempotent = $readiness->attest($approvalToken);
    $assert(($idempotent['readiness_sha256'] ?? null) === hash('sha256', $readinessRaw), 'exact replay changed readiness evidence.');

    $tamperedRequest = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedRequest['created_at_unix'] = $now + 1;
    $writeJson($requestPath, $tamperedRequest);
    $assert(($readiness->inspect()['state'] ?? null) === 'PROMOTION_READINESS_INVALID', 'request tamper did not invalidate readiness.');

    if (file_put_contents($requestPath, $requestRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore request fixture.');
    }
    @chmod($requestPath, 0600);

    $tamperedAuthority = $authority;
    $tamperedAuthority['authority_id'] = 'promotion-authority-'.str_repeat('3', 24);
    $writeJson($authorityPath, $tamperedAuthority);
    $assert(($readiness->inspect()['state'] ?? null) === 'PROMOTION_READINESS_INVALID', 'authority tamper did not invalidate readiness.');

    if (file_put_contents($authorityPath, $authorityRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore authority fixture.');
    }
    @chmod($authorityPath, 0600);

    $expiredInspection = (new PrebootRuntimeConfigurationPromotionReadiness(
        $shared,
        $releaseId,
        (int) $authority['expires_at_unix'],
    ))->inspect();
    $assert(($expiredInspection['state'] ?? null) === 'PROMOTION_READINESS_INVALID', 'expired authority/readiness did not fail closed.');

    $schemaPath = __DIR__.'/../../../tools/installation/runtime-configuration-promotion-readiness.schema.json';
    $schema = json_decode((string) file_get_contents($schemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($schema), 'readiness schema is invalid JSON.');
    $assert(($schema['properties']['readiness_state']['const'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED', 'schema readiness state invalid.');
    $assert(($schema['properties']['promotion_executed']['const'] ?? null) === false, 'schema crossed execution boundary.');
    $assert(($schema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'schema crossed Technical Preview boundary.');
    $assert(($schema['properties']['production_authorized']['const'] ?? null) === false, 'schema crossed Production boundary.');

    $source = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionReadiness.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach ([
        'Artisan::call',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"',
        'ONEQAY_PERSISTENCE_ENABLED="true"',
    ] as $forbidden) {
        $assert(! str_contains($source, $forbidden), 'readiness source contains forbidden primitive '.$forbidden.'.');
        $assert(! str_contains($publicInstaller, $forbidden), 'installer contains forbidden primitive '.$forbidden.'.');
    }

    $assert(! str_contains($source, 'rename($this->pendingEnvironmentPath()'), 'readiness source promotes pending environment.');
    $assert(! str_contains($source, 'copy($this->pendingEnvironmentPath()'), 'readiness source copies pending environment to active.');
    $assert(str_contains($publicInstaller, 'EXECUTION READY / NOT EXECUTED'), 'installer UI does not expose durable readiness.');

    fwrite(STDOUT, "Sprint189 runtime promotion execution readiness regression passed.\n");
} finally {
    $remove($root);
}
