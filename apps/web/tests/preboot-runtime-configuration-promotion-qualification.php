<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationConfiguration;
use App\Infrastructure\Installation\PrebootRuntimeConfigurationPromotionQualification;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint188 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s188-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$runtime = $shared.DIRECTORY_SEPARATOR.'runtime';
$releaseId = 'm75-preview-188abc188abc';
$now = 1790000400;
$installationToken = 's188-install.'.str_repeat('F', 48);
$approvalToken = 's188-approve.'.str_repeat('G', 48);
$password = 'sprint188-db-secret';

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
        throw new RuntimeException('Unable to write Sprint188 JSON fixture.');
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

    $assert(($prepared['promotion_request_pending'] ?? false) === true, 'Sprint187 request fixture was not created.');

    $pendingPath = $runtime.DIRECTORY_SEPARATOR.'.env.pending';
    $activePath = $runtime.DIRECTORY_SEPARATOR.'.env';
    $handoffPath = $install.DIRECTORY_SEPARATOR.'activation-readiness.json';
    $requestPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-request.json';
    $authorityPath = $install.DIRECTORY_SEPARATOR.'runtime-configuration-promotion-authority.json';

    $pending = (string) file_get_contents($pendingPath);
    $handoffRaw = (string) file_get_contents($handoffPath);
    $requestRaw = (string) file_get_contents($requestPath);
    $request = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($request), 'promotion request fixture is invalid.');

    $qualification = new PrebootRuntimeConfigurationPromotionQualification($shared, $releaseId, $now);

    $missing = $qualification->inspect();
    $assert(($missing['state'] ?? null) === 'PROMOTION_AUTHORITY_MISSING', 'missing authority did not fail closed.');
    $assert(($missing['promotion_qualified'] ?? true) === false, 'missing authority qualified promotion.');

    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'authority_id' => 'promotion-authority-'.str_repeat('1', 24),
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

    $inspect = $qualification->inspect();
    $assert(($inspect['state'] ?? null) === 'PROMOTION_AUTHORITY_TOKEN_REQUIRED', 'valid authority did not request out-of-band token.');
    $assert(($inspect['authority_present'] ?? false) === true, 'valid authority was not detected.');
    $assert(($inspect['token_required'] ?? false) === true, 'token qualification was not required.');
    $assert(($inspect['promotion_qualified'] ?? true) === false, 'inspect alone granted qualification.');

    $wrongDenied = false;
    try {
        $qualification->qualify('wrong-token-'.str_repeat('Z', 40));
    } catch (RuntimeException $exception) {
        $wrongDenied = $exception->getMessage() === 'promotion_authority_denied';
    }
    $assert($wrongDenied, 'wrong approval token did not fail closed.');

    $qualified = $qualification->qualify($approvalToken);
    $assert(($qualified['state'] ?? null) === 'PROMOTION_QUALIFIED_NOT_EXECUTED', 'authority did not reach qualified-not-executed state.');
    $assert(($qualified['promotion_qualified'] ?? false) === true, 'valid authority was not qualified.');
    $assert(($qualified['promotion_executed'] ?? true) === false, 'qualification executed promotion.');
    $assert(($qualified['technical_preview_authorized'] ?? true) === false, 'qualification granted Technical Preview authority.');
    $assert(($qualified['production_authorized'] ?? true) === false, 'qualification granted Production authority.');
    $assert(
        preg_match('/\A[0-9a-f]{64}\z/', (string) ($qualified['qualification_fingerprint'] ?? '')) === 1,
        'qualification fingerprint is invalid.',
    );

    $assert(! file_exists($activePath), 'qualification created active .env.');
    $assert((string) file_get_contents($pendingPath) === $pending, 'qualification mutated pending configuration.');
    $assert((string) file_get_contents($handoffPath) === $handoffRaw, 'qualification mutated handoff.');
    $assert((string) file_get_contents($requestPath) === $requestRaw, 'qualification mutated request.');

    $authorityRaw = (string) file_get_contents($authorityPath);
    foreach ([$password, $installationToken, $approvalToken] as $secret) {
        $assert(! str_contains($authorityRaw, $secret), 'plaintext secret leaked into authority artifact.');
    }

    $expired = $authority;
    $expired['authorized_at_unix'] = $now - 901;
    $expired['expires_at_unix'] = $now - 1;
    $writeJson($authorityPath, $expired);
    $assert(
        ($qualification->inspect()['state'] ?? null) === 'PROMOTION_AUTHORITY_EXPIRED',
        'expired authority did not fail closed.',
    );

    $mismatch = $authority;
    $mismatch['pending_environment_sha256'] = str_repeat('0', 64);
    $writeJson($authorityPath, $mismatch);
    $assert(
        ($qualification->inspect()['state'] ?? null) === 'PROMOTION_AUTHORITY_INVALID',
        'digest-mismatched authority did not fail closed.',
    );

    $writeJson($authorityPath, $authority);
    $tamperedRequest = json_decode($requestRaw, true, 32, JSON_THROW_ON_ERROR);
    $tamperedRequest['created_at_unix'] = $now + 1;
    $writeJson($requestPath, $tamperedRequest);
    $assert(
        ($qualification->inspect()['state'] ?? null) === 'PROMOTION_REQUEST_NOT_READY',
        'tampered promotion request remained authority-qualifiable.',
    );

    if (file_put_contents($requestPath, $requestRaw, LOCK_EX) === false) {
        throw new RuntimeException('Unable to restore request fixture.');
    }
    @chmod($requestPath, 0600);

    $schemaPath = __DIR__.'/../../../tools/installation/runtime-configuration-promotion-authority.schema.json';
    $schema = json_decode((string) file_get_contents($schemaPath), true, 32, JSON_THROW_ON_ERROR);
    $assert(is_array($schema), 'authority schema is invalid JSON.');
    $assert(($schema['properties']['authority_state']['const'] ?? null) === 'GRANTED', 'schema does not require granted authority state.');
    $assert(($schema['properties']['single_use']['const'] ?? null) === true, 'schema does not require single-use authority.');
    $assert(($schema['properties']['promotion_authorized']['const'] ?? null) === true, 'schema does not represent promotion authority.');
    $assert(($schema['properties']['technical_preview_authorized']['const'] ?? null) === false, 'schema crossed Technical Preview boundary.');
    $assert(($schema['properties']['production_authorized']['const'] ?? null) === false, 'schema crossed Production boundary.');

    $source = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/PrebootRuntimeConfigurationPromotionQualification.php');
    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');
    foreach ([
        'rename(',
        'copy(',
        'file_put_contents(',
        'Artisan::call',
        'migrate',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"',
    ] as $forbidden) {
        $assert(! str_contains($source, $forbidden), 'qualification source contains forbidden execution primitive '.$forbidden.'.');
    }

    $assert(str_contains($publicInstaller, 'QUALIFIED / NOT EXECUTED'), 'installer UI does not expose qualified-not-executed state.');
    $assert(str_contains($publicInstaller, 'qualify_promotion_authority'), 'installer UI does not expose separate qualification action.');

    fwrite(STDOUT, "Sprint188 runtime promotion qualification regression passed.\n");
} finally {
    $remove($root);
}
