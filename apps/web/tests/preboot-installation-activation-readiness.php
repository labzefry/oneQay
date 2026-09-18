<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationActivationReadiness;
use App\Infrastructure\Installation\PrebootInstallationConfiguration;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint186 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s186-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-186abc186abc';
$otherReleaseId = 'm75-preview-999999999999';
$now = 1790000200;
$token = 's186.'.str_repeat('D', 58);
$password = 'sprint186-db-secret';

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

    $verifiedFacts = [
        'connected' => true,
        'engine' => 'mysql',
        'server_version' => '8.0.36',
        'charset' => 'utf8mb4',
        'timezone' => '+00:00',
        'schema_state' => 'empty',
        'least_privilege' => true,
    ];

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

    $assert(($result['prepared'] ?? false) === true, 'verified configuration was not prepared.');
    $assert(($result['activation_handoff_ready'] ?? false) === true, 'activation-readiness handoff was not sealed.');
    $assert(($result['activation_authorized'] ?? true) === false, 'handoff must not grant activation authority.');

    $pendingPath = $runtime.DIRECTORY_SEPARATOR.'.env.pending';
    $activePath = $runtime.DIRECTORY_SEPARATOR.'.env';
    $attestationPath = $install.DIRECTORY_SEPARATOR.'activation-readiness.json';

    $assert(is_file($pendingPath), 'pending environment is missing.');
    $assert(is_file($attestationPath), 'activation-readiness attestation is missing.');
    $assert(! file_exists($activePath), 'active environment must not be created.');
    $assert(! file_exists($authorityPath), 'one-time preparation authority was not consumed after the complete handoff committed.');

    $pending = (string) file_get_contents($pendingPath);
    foreach ([
        'ONEQAY_INSTALLATION_RELEASE_ID="'.$releaseId.'"',
        'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"',
        'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
        'ONEQAY_PERSISTENCE_ENABLED="false"',
        'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="false"',
    ] as $marker) {
        $assert(str_contains($pending, $marker), 'pending release handoff is missing '.$marker.'.');
    }

    $pendingPermissions = fileperms($pendingPath);
    $attestationPermissions = fileperms($attestationPath);
    $assert(is_int($pendingPermissions) && ($pendingPermissions & 0077) === 0, 'pending environment is not private.');
    $assert(is_int($attestationPermissions) && ($attestationPermissions & 0077) === 0, 'activation-readiness attestation is not private.');

    $attestationRaw = (string) file_get_contents($attestationPath);
    $attestation = json_decode($attestationRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($attestation), 'activation-readiness attestation is not valid JSON.');
    $assert(($attestation['schema_version'] ?? null) === 1, 'unexpected attestation schema version.');
    $assert(($attestation['product'] ?? null) === 'oneQay', 'attestation product binding is invalid.');
    $assert(($attestation['release_id'] ?? null) === $releaseId, 'attestation is not exact-release bound.');
    $assert(($attestation['pending_environment_sha256'] ?? null) === hash('sha256', $pending), 'pending digest binding is invalid.');
    $assert(($attestation['pending_environment_bytes'] ?? null) === strlen($pending), 'pending byte-length binding is invalid.');
    $assert(($attestation['sealed_at_unix'] ?? null) === $now, 'attestation seal timestamp is invalid.');
    $assert(($attestation['database']['engine'] ?? null) === 'mysql', 'safe database engine evidence is missing.');
    $assert(($attestation['database']['schema_state'] ?? null) === 'empty', 'safe schema evidence is missing.');
    $assert(($attestation['database']['least_privilege'] ?? false) === true, 'least-privilege evidence is missing.');

    foreach ([
        'activation_authorized',
        'migration_execution_authorized',
        'technical_preview_authorized',
        'production_authorized',
        'updater_authorized',
    ] as $flag) {
        $assert(($attestation[$flag] ?? null) === false, 'attestation unexpectedly authorizes '.$flag.'.');
    }

    preg_match('/^APP_KEY="([^"]+)"/m', $pending, $appKeyMatch);
    $appKey = (string) ($appKeyMatch[1] ?? '');
    foreach ([$password, $token, 'oneqay_runtime', $appKey, 'ONEQAY_DB_PASSWORD'] as $secretMarker) {
        if ($secretMarker !== '') {
            $assert(! str_contains($attestationRaw, $secretMarker), 'secret-bearing value leaked into activation-readiness attestation.');
        }
    }

    $handoff = new PrebootInstallationActivationReadiness($shared, $releaseId, $now);
    $ready = $handoff->inspect();
    $assert(($ready['state'] ?? null) === 'ACTIVATION_HANDOFF_READY', 'sealed handoff state is not ready.');
    $assert(($ready['ready'] ?? false) === true, 'sealed handoff did not qualify.');
    $assert(($ready['activation_authorized'] ?? true) === false, 'readiness inspection must not grant activation authority.');

    $installerState = $installer->inspect();
    $assert(($installerState['state'] ?? null) === 'PENDING_CONFIGURATION_VERIFIED', 'Sprint185 pending state contract changed unexpectedly.');
    $assert(($installerState['activation_handoff_ready'] ?? false) === true, 'installer does not expose sealed handoff readiness.');

    $wrongRelease = new PrebootInstallationActivationReadiness($shared, $otherReleaseId, $now);
    $wrongReleaseState = $wrongRelease->inspect();
    $assert(($wrongReleaseState['ready'] ?? true) === false, 'different governed release accepted the pending handoff.');

    $assert(file_put_contents($pendingPath, $pending."# tampered\n", LOCK_EX) !== false, 'tamper fixture could not be written.');
    @chmod($pendingPath, 0600);
    $tampered = $handoff->inspect();
    $assert(($tampered['state'] ?? null) === 'ACTIVATION_HANDOFF_INVALID', 'pending tamper did not invalidate the handoff.');
    $assert(($tampered['ready'] ?? true) === false, 'tampered pending configuration remained activation-ready.');

    $assert(file_put_contents($pendingPath, $pending, LOCK_EX) !== false, 'pending fixture could not be restored.');
    @chmod($pendingPath, 0600);
    $assert(($handoff->inspect()['ready'] ?? false) === true, 'restored exact pending bytes did not restore handoff validity.');

    $assert(file_put_contents($activePath, "APP_NAME=\"oneQay\"\n", LOCK_EX) !== false, 'active environment denial fixture could not be written.');
    @chmod($activePath, 0600);
    $active = $handoff->inspect();
    $assert(($active['state'] ?? null) === 'ACTIVE_ENV_PRESENT', 'active environment did not supersede handoff readiness.');
    $assert(($active['ready'] ?? true) === false, 'active environment incorrectly remained pre-activation ready.');

    $classSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootInstallationActivationReadiness.php');
    $configurationSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootInstallationConfiguration.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');

    foreach ([
        'Artisan::call',
        'migrate',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"',
        'ONEQAY_PERSISTENCE_ENABLED="true"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="true"',
    ] as $forbidden) {
        $assert(! str_contains($classSource, $forbidden), 'handoff class contains forbidden activation primitive '.$forbidden.'.');
        $assert(! str_contains($publicInstaller, $forbidden), 'public installer contains forbidden activation primitive '.$forbidden.'.');
    }

    $assert(str_contains($configurationSource, 'ONEQAY_INSTALLATION_RELEASE_ID'), 'pending environment is not release-bound.');
    $assert(str_contains($publicInstaller, 'Activation-readiness handoff is sealed and tamper-evident.'), 'professional handoff status is missing from installer UI.');
    $assert(str_contains($publicInstaller, 'SEALED / NOT AUTHORIZED'), 'installer UI does not preserve the activation boundary.');

    fwrite(STDOUT, "Sprint186 governed activation-readiness handoff regression passed.\n");
} finally {
    $remove($root);
}
