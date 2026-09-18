<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionRequest;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint187 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s187-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-187abc187abc';
$otherReleaseId = 'm75-preview-aaaaaaaaaaaa';
$now = 1790000300;
$token = 's187.'.str_repeat('E', 58);
$password = 'sprint187-db-secret';

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

try {
    $assert(mkdir($install, 0700, true), 'private installation boundary could not be created.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'release_id' => $releaseId,
        'token_sha256' => hash('sha256', $token),
        'expires_at' => $now + 900,
        'activation_authorized' => false,
        'attribution' => 'Lab | zefry',
    ];
    $authorityPath = $install.DIRECTORY_SEPARATOR.'authority.json';
    $assert(file_put_contents(
        $authorityPath,
        json_encode($authority, JSON_THROW_ON_ERROR),
        LOCK_EX,
    ) !== false, 'authority fixture could not be written.');
    @chmod($authorityPath, 0600);

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

    $result = $installer->prepare([
        'installation_token' => $token,
        'app_url' => 'https://preview.example.test',
        'db_host' => 'db.internal.example',
        'db_port' => '3306',
        'db_database' => 'oneqay_preview',
        'db_username' => 'oneqay_runtime',
        'db_password' => $password,
    ]);

    $assert(($result['prepared'] ?? false) === true, 'verified pending configuration was not prepared.');
    $assert(($result['activation_handoff_ready'] ?? false) === true, 'activation handoff was not sealed.');
    $assert(($result['promotion_request_pending'] ?? false) === true, 'promotion request was not materialized.');
    $assert(($result['activation_authorized'] ?? true) === false, 'preboot flow unexpectedly granted activation authority.');

    $pendingPath = $runtime.DIRECTORY_SEPARATOR.'.env.pending';
    $activePath = $runtime.DIRECTORY_SEPARATOR.'.env';
    $handoffPath = $install.DIRECTORY_SEPARATOR.'activation-readiness.json';
    $requestPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-request.json';
    $promotionAuthorityPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-authority.json';

    foreach ([$pendingPath, $handoffPath, $requestPath] as $path) {
        $assert(is_file($path), 'expected private installation artifact missing: '.basename($path));
        $permissions = fileperms($path);
        $assert(is_int($permissions) && ($permissions & 0077) === 0, basename($path).' is not private.');
    }

    $assert(! file_exists($activePath), 'promotion request created active runtime configuration.');
    $assert(! file_exists($promotionAuthorityPath), 'promotion request fabricated approval authority.');
    $assert(! file_exists($authorityPath), 'preparation authority was not consumed after the full request chain committed.');

    $pending = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request is not valid JSON.');

    $expectedRequestId = 'promotion-request-'.substr(
        hash('sha256', $releaseId.'|'.hash('sha256', $pending).'|'.hash('sha256', $handoffRaw)),
        0,
        24,
    );

    $assert(($request['schema_version'] ?? null) === 1, 'unexpected promotion request schema version.');
    $assert(($request['product'] ?? null) === 'oneQay', 'promotion request product binding is invalid.');
    $assert(($request['request_id'] ?? null) === $expectedRequestId, 'promotion request identity is not deterministic/exact-bound.');
    $assert(($request['request_state'] ?? null) === 'PENDING_APPROVAL', 'promotion request must wait for approval.');
    $assert(($request['release_id'] ?? null) === $releaseId, 'promotion request is not exact-release bound.');
    $assert(
        ($request['requested_operation'] ?? null) === 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION',
        'unexpected requested operation.',
    );
    $assert(($request['pending_environment_sha256'] ?? null) === hash('sha256', $pending), 'pending digest binding is invalid.');
    $assert(($request['activation_readiness_sha256'] ?? null) === hash('sha256', $handoffRaw), 'handoff digest binding is invalid.');

    $effect = $request['requested_effect'] ?? null;
    $approval = $request['required_approval'] ?? null;
    $assert(is_array($effect), 'requested effect contract is missing.');
    $assert(is_array($approval), 'required approval contract is missing.');

    $assert(($effect['source_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env.pending', 'request source path is invalid.');
    $assert(($effect['target_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env', 'request target path is invalid.');
    $assert(($effect['preserve_configuration_bytes_exactly'] ?? false) === true, 'future promotion must preserve pending bytes exactly.');
    foreach ([
        'technical_preview_flag_change_requested',
        'persistence_flag_change_requested',
        'updater_flag_change_requested',
        'migration_execution_requested',
    ] as $flag) {
        $assert(($effect[$flag] ?? null) === false, 'promotion request unexpectedly requests '.$flag.'.');
    }

    $assert(($approval['authority_state'] ?? null) === 'NOT_GRANTED', 'promotion authority must remain not granted.');
    $assert(
        ($approval['authority_relative_path'] ?? null) === 'oneqay-preview/shared/install/runtime-configuration-promotion-authority.json',
        'promotion authority path contract is invalid.',
    );
    foreach ([
        'exact_release_required',
        'exact_pending_environment_sha256_required',
        'exact_activation_readiness_sha256_required',
        'single_use_required',
        'separate_operational_authority_required',
    ] as $requirement) {
        $assert(($approval[$requirement] ?? false) === true, 'approval requirement missing '.$requirement.'.');
    }

    foreach ([
        'promotion_authorized',
        'migration_execution_authorized',
        'technical_preview_authorized',
        'production_authorized',
        'updater_authorized',
        'deployment_authorized',
    ] as $flag) {
        $assert(($request[$flag] ?? null) === false, 'promotion request unexpectedly grants '.$flag.'.');
    }

    preg_match('/^APP_KEY="([^"]+)"/m', $pending, $appKeyMatch);
    $appKey = (string) ($appKeyMatch[1] ?? '');
    foreach ([$password, $token, 'oneqay_runtime', $appKey, 'ONEQAY_DB_PASSWORD'] as $secretMarker) {
        if ($secretMarker !== '') {
            $assert(! str_contains($requestRaw, $secretMarker), 'secret-bearing value leaked into promotion request.');
        }
    }

    $promotion = new PrebootRuntimeConfigurationPromotionRequest($shared, $releaseId, $now);
    $inspection = $promotion->inspect();
    $assert(($inspection['state'] ?? null) === 'PROMOTION_REQUEST_PENDING_APPROVAL', 'promotion request inspection state is invalid.');
    $assert(($inspection['request_ready'] ?? false) === true, 'valid promotion request is not review-ready.');
    $assert(($inspection['request_id'] ?? null) === $expectedRequestId, 'inspection request identity drifted.');
    $assert(($inspection['promotion_authorized'] ?? true) === false, 'inspection must not grant promotion authority.');

    $installerState = $installer->inspect();
    $assert(($installerState['state'] ?? null) === 'PENDING_CONFIGURATION_VERIFIED', 'existing pending configuration state contract changed.');
    $assert(($installerState['activation_handoff_ready'] ?? false) === true, 'activation handoff readiness was lost.');
    $assert(($installerState['promotion_request_pending'] ?? false) === true, 'installer does not expose pending approval state.');

    $wrongRelease = new PrebootRuntimeConfigurationPromotionRequest($shared, $otherReleaseId, $now);
    $assert(($wrongRelease->inspect()['request_ready'] ?? true) === false, 'different release accepted promotion request.');

    $assert(file_put_contents($pendingPath, $pending."# tampered\n", LOCK_EX) !== false, 'pending tamper fixture failed.');
    @chmod($pendingPath, 0600);
    $tamperedPending = $promotion->inspect();
    $assert(($tamperedPending['state'] ?? null) === 'PROMOTION_REQUEST_INVALID', 'pending tamper did not invalidate request.');
    $assert(($tamperedPending['request_ready'] ?? true) === false, 'tampered request remained ready.');

    $assert(file_put_contents($pendingPath, $pending, LOCK_EX) !== false, 'pending restore failed.');
    @chmod($pendingPath, 0600);

    $tamperedHandoff = json_decode($handoffRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedHandoff['sealed_at_unix'] = $now + 1;
    $assert(file_put_contents(
        $handoffPath,
        json_encode($tamperedHandoff, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
        LOCK_EX,
    ) !== false, 'handoff tamper fixture failed.');
    @chmod($handoffPath, 0600);
    $assert(($promotion->inspect()['request_ready'] ?? true) === false, 'handoff tamper did not invalidate request.');

    $assert(file_put_contents($handoffPath, $handoffRaw, LOCK_EX) !== false, 'handoff restore failed.');
    @chmod($handoffPath, 0600);
    $assert(($promotion->inspect()['request_ready'] ?? false) === true, 'restored exact handoff did not restore request validity.');

    $assert(file_put_contents($activePath, "APP_NAME=\"oneQay\"\n", LOCK_EX) !== false, 'active env fixture failed.');
    @chmod($activePath, 0600);
    $activeState = $promotion->inspect();
    $assert(($activeState['state'] ?? null) === 'ACTIVE_ENV_PRESENT', 'active environment was not fail-closed.');
    $assert(($activeState['request_ready'] ?? true) === false, 'active environment left request review-ready.');

    $requestSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionRequest.php');
    $configurationSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootInstallationConfiguration.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach ([
        'Artisan::call',
        'migrate',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"',
        'ONEQAY_PERSISTENCE_ENABLED="true"',
        'promotion_authorized'." => true",
    ] as $forbidden) {
        $assert(! str_contains($requestSource, $forbidden), 'request source contains forbidden execution primitive '.$forbidden.'.');
        $assert(! str_contains($publicInstaller, $forbidden), 'public installer contains forbidden execution primitive '.$forbidden.'.');
    }

    $assert(str_contains($configurationSource, 'promotion_request_pending'), 'configuration state does not expose request pending status.');
    $assert(str_contains($publicInstaller, 'PENDING APPROVAL'), 'operator UI does not expose promotion approval state.');

    fwrite(STDOUT, "Sprint187 runtime configuration promotion request regression passed.\n");
} finally {
    $remove($root);
}
