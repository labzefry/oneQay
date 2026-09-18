<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationActivationHandoff;
use App\Infrastructure\Installation\PrebootInstallationConfiguration;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint186 regression failed: '.$message);
    }
};

$removeTree = null;
$removeTree = static function (string $path) use (&$removeTree): void {
    if (is_link($path) || is_file($path)) {
        @chmod($path, 0600);
        @unlink($path);

        return;
    }

    if (! is_dir($path)) {
        return;
    }

    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        $removeTree($item->getPathname());
    }

    @rmdir($path);
};

$verifiedFacts = [
    'connected' => true,
    'engine' => 'mysql',
    'server_version' => '8.0.36',
    'charset' => 'utf8mb4',
    'timezone' => '+00:00',
    'schema_state' => 'empty',
    'least_privilege' => true,
];

$verifiedDatabase = static fn (array $configuration): array => [
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
];

$releaseId = 'm75-preview-abcdef012345';
$now = 1_790_000_200;
$token = 's186.'.str_repeat('D', 58);
$secretPassword = 's186-db-password-must-never-enter-handoff';
$appUrl = 'https://preview.s186.example.test';
$dbHost = 'db.s186.internal.example';

$pendingFixture = implode(PHP_EOL, [
    '# oneQay prepared runtime configuration',
    'APP_KEY="base64:'.base64_encode(str_repeat('a', 32)).'"',
    'APP_URL="'.$appUrl.'"',
    'ONEQAY_DB_HOST="'.$dbHost.'"',
    'ONEQAY_DB_PASSWORD="'.$secretPassword.'"',
    'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"',
    'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"',
    'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
    'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
    'ONEQAY_PERSISTENCE_ENABLED="false"',
]).PHP_EOL;

$unitRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s186-unit-'.bin2hex(random_bytes(8));
$integrationRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s186-integration-'.bin2hex(random_bytes(8));
$staleRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s186-stale-'.bin2hex(random_bytes(8));

try {
    $assert(mkdir($unitRoot.DIRECTORY_SEPARATOR.'install', 0700, true), 'unit install boundary could not be created.');

    $handoff = new PrebootInstallationActivationHandoff($unitRoot, $releaseId, $now);
    $request = $handoff->buildRequest($pendingFixture, $verifiedFacts);
    $decoded = json_decode($request, true, 32, JSON_THROW_ON_ERROR);

    $assert(($decoded['schema_version'] ?? null) === 1, 'handoff schema version mismatch.');
    $assert(($decoded['product'] ?? null) === 'oneQay', 'handoff product mismatch.');
    $assert(($decoded['release_id'] ?? null) === $releaseId, 'handoff release binding mismatch.');
    $assert(
        ($decoded['pending_environment_sha256'] ?? null) === hash('sha256', $pendingFixture),
        'handoff pending digest mismatch.',
    );
    $assert(
        preg_match('/\A[0-9a-f]{64}\z/', (string) ($decoded['database_verification_fingerprint'] ?? '')) === 1,
        'handoff database verification fingerprint missing.',
    );
    $assert(($decoded['activation_authorized'] ?? true) === false, 'handoff granted activation authority.');
    $assert(($decoded['activation_authority_required'] ?? false) === true, 'handoff did not require separate activation authority.');
    $assert(($decoded['migration_execution_authorized'] ?? true) === false, 'handoff authorized migration execution.');
    $assert(($decoded['technical_preview_authorized'] ?? true) === false, 'handoff authorized Technical Preview.');
    $assert(($decoded['production_authorized'] ?? true) === false, 'handoff authorized Production.');

    $serializedRequest = strtolower($request);
    foreach ([
        strtolower($secretPassword),
        strtolower($appUrl),
        strtolower($dbHost),
        'oneqay_db_password',
        'db_password',
        'app_key',
        'installation_token',
        'authority.json',
    ] as $forbidden) {
        $assert(! str_contains($serializedRequest, $forbidden), 'handoff leaked sensitive marker '.$forbidden.'.');
    }

    $requestPath = $handoff->requestPath();
    $assert(file_put_contents($requestPath, $request, LOCK_EX) !== false, 'unit handoff could not be written.');
    @chmod($requestPath, 0600);
    $assert($handoff->requestMatchesPending($pendingFixture), 'valid handoff did not match pending configuration.');

    $tampered = $decoded;
    $tampered['pending_environment_sha256'] = str_repeat('0', 64);
    $assert(file_put_contents(
        $requestPath,
        json_encode($tampered, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
        LOCK_EX,
    ) !== false, 'tampered handoff fixture could not be written.');
    $assert(! $handoff->requestMatchesPending($pendingFixture), 'tampered pending digest was accepted.');

    $shared = $integrationRoot.DIRECTORY_SEPARATOR.'shared';
    $install = $shared.DIRECTORY_SEPARATOR.'install';
    $assert(mkdir($install, 0700, true), 'integration install boundary could not be created.');
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
    ) !== false, 'integration authority fixture could not be written.');
    @chmod($authorityPath, 0600);

    $subject = new PrebootInstallationConfiguration($shared, $releaseId, $now, $verifiedDatabase);
    $result = $subject->prepare([
        'installation_token' => $token,
        'app_url' => $appUrl,
        'db_host' => $dbHost,
        'db_port' => '3306',
        'db_database' => 'oneqay_s186',
        'db_username' => 'oneqay_s186',
        'db_password' => $secretPassword,
    ]);

    $assert(($result['prepared'] ?? false) === true, 'verified handoff preparation did not complete.');
    $assert(($result['state'] ?? null) === 'CONFIGURATION_PREPARED_HANDOFF_READY', 'handoff result state mismatch.');
    $assert(($result['activation_authorized'] ?? true) === false, 'preparation granted activation authority.');

    $pendingPath = $shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env.pending';
    $activePath = $shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env';
    $activationRequestPath = $install.DIRECTORY_SEPARATOR.'activation-request.json';

    $assert(is_file($pendingPath), 'pending environment was not created.');
    $assert(is_file($activationRequestPath), 'activation handoff request was not created.');
    $assert(! file_exists($activePath), 'active environment was created.');
    $assert(! file_exists($authorityPath), 'single-use authority was not consumed after complete handoff.');

    clearstatcache(true, $pendingPath);
    clearstatcache(true, $activationRequestPath);
    $assert(((int) fileperms($pendingPath) & 0777) === 0600, 'pending environment mode is not 0600.');
    $assert(((int) fileperms($activationRequestPath) & 0777) === 0600, 'activation handoff mode is not 0600.');

    $pending = (string) file_get_contents($pendingPath);
    $activationRequest = (string) file_get_contents($activationRequestPath);
    $activationPayload = json_decode($activationRequest, true, 32, JSON_THROW_ON_ERROR);

    $assert(
        ($activationPayload['pending_environment_sha256'] ?? null) === hash('sha256', $pending),
        'persisted handoff is not bound to exact pending bytes.',
    );
    $assert(($activationPayload['release_id'] ?? null) === $releaseId, 'persisted handoff release binding mismatch.');
    $assert(($activationPayload['activation_authorized'] ?? true) === false, 'persisted handoff granted activation.');
    $assert(($activationPayload['activation_authority_required'] ?? false) === true, 'persisted handoff omitted authority requirement.');
    $assert(($subject->inspect()['state'] ?? null) === 'PENDING_CONFIGURATION_VERIFIED_HANDOFF_READY', 'installer did not expose handoff-ready state.');

    $lowerActivationRequest = strtolower($activationRequest);
    foreach ([strtolower($secretPassword), strtolower($appUrl), strtolower($dbHost), strtolower($token), 'app_key'] as $forbidden) {
        $assert(! str_contains($lowerActivationRequest, $forbidden), 'persisted handoff leaked '.$forbidden.'.');
    }

    $activationPayload['pending_environment_sha256'] = str_repeat('f', 64);
    $assert(file_put_contents(
        $activationRequestPath,
        json_encode($activationPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
        LOCK_EX,
    ) !== false, 'persisted handoff tamper fixture could not be written.');
    $assert(
        ($subject->inspect()['state'] ?? null) === 'PENDING_CONFIGURATION_VERIFIED',
        'tampered handoff did not fail closed to verified-pending state.',
    );

    $staleShared = $staleRoot.DIRECTORY_SEPARATOR.'shared';
    $staleInstall = $staleShared.DIRECTORY_SEPARATOR.'install';
    $assert(mkdir($staleInstall, 0700, true), 'stale install boundary could not be created.');
    $staleAuthority = $authority;
    $assert(file_put_contents(
        $staleInstall.DIRECTORY_SEPARATOR.'authority.json',
        json_encode($staleAuthority, JSON_THROW_ON_ERROR),
        LOCK_EX,
    ) !== false, 'stale authority fixture could not be written.');
    $assert(file_put_contents(
        $staleInstall.DIRECTORY_SEPARATOR.'activation-request.json',
        "{}\n",
        LOCK_EX,
    ) !== false, 'stale handoff fixture could not be written.');

    $staleSubject = new PrebootInstallationConfiguration($staleShared, $releaseId, $now, $verifiedDatabase);
    try {
        $staleSubject->prepare([
            'installation_token' => $token,
            'app_url' => $appUrl,
            'db_host' => $dbHost,
            'db_port' => '3306',
            'db_database' => 'oneqay_s186',
            'db_username' => 'oneqay_s186',
            'db_password' => $secretPassword,
        ]);
        $assert(false, 'preexisting handoff request did not block preparation.');
    } catch (RuntimeException $exception) {
        $assert($exception->getMessage() === 'activation_handoff_already_present', 'unexpected stale handoff denial code.');
    }
    $assert(
        is_file($staleInstall.DIRECTORY_SEPARATOR.'authority.json'),
        'stale handoff denial consumed installation authority.',
    );
    $assert(
        ! file_exists($staleShared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env.pending'),
        'stale handoff denial created pending configuration.',
    );

    $publicInstaller = (string) file_get_contents(__DIR__.'/../../../tools/installation/public-installer.php');
    foreach ([
        'PrebootInstallationActivationHandoff.php',
        'PENDING_CONFIGURATION_VERIFIED_HANDOFF_READY',
        'activation handoff',
        'no activation authority is granted',
    ] as $marker) {
        $assert(str_contains(strtolower($publicInstaller), strtolower($marker)), 'public installer missing Sprint186 marker '.$marker.'.');
    }

    foreach ([
        'Artisan::call',
        'migrate',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"',
        'ONEQAY_PERSISTENCE_ENABLED="true"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="true"',
    ] as $forbidden) {
        $assert(! str_contains($publicInstaller, $forbidden), 'public installer contains forbidden activation primitive '.$forbidden.'.');
    }

    fwrite(STDOUT, "Sprint186 verified pending activation handoff regression passed.\n");
} finally {
    $removeTree($unitRoot);
    $removeTree($integrationRoot);
    $removeTree($staleRoot);
}
