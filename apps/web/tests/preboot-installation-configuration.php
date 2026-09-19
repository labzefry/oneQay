<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootInstallationConfiguration;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $ok, string $message): void {
    if (! $ok) {
        throw new RuntimeException('Sprint184 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s184-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$releaseId = 'm75-preview-0123456789ab';
$now = 1790000000;
$token = 's184.'.str_repeat('A', 58);
$verifiedDatabase = static function (array $configuration): array {
    return [
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
};

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
    $assert(mkdir($install, 0700, true), 'private install directory could not be created.');
    $authority = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'release_id' => $releaseId,
        'token_sha256' => hash('sha256', $token),
        'expires_at' => $now + 900,
        'activation_authorized' => false,
        'attribution' => 'Lab | zefry',
    ];
    $assert(file_put_contents(
        $install.DIRECTORY_SEPARATOR.'authority.json',
        json_encode($authority, JSON_THROW_ON_ERROR),
        LOCK_EX,
    ) !== false, 'authority fixture could not be written.');

    $subject = new PrebootInstallationConfiguration($shared, $releaseId, $now, $verifiedDatabase);
    $state = $subject->inspect();
    $assert(($state['state'] ?? null) === 'READY_FOR_CONFIGURATION', 'valid authority did not permit preparation.');
    $assert(($state['activation_authorized'] ?? true) === false, 'inspection must never create activation authority.');

    $password = ' synthetic-value-with-$-and-"quotes" ';
    $result = $subject->prepare([
        'installation_token' => $token,
        'app_url' => 'https://preview.example.test',
        'db_host' => 'db.internal.example',
        'db_port' => '3306',
        'db_database' => 'oneqay_preview',
        'db_username' => 'oneqay_runtime',
        'db_password' => $password,
    ]);

    $assert(($result['prepared'] ?? null) === true, 'valid preparation did not complete.');
    $assert(($result['activation_authorized'] ?? true) === false, 'preparation must remain pending-only.');

    $pending = $shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env.pending';
    $active = $shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env';
    $assert(is_file($pending), 'pending environment was not written.');
    $assert(! file_exists($active), 'active environment must not be created.');
    $sessionDirectory = $shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'sessions';
    $assert(is_dir($sessionDirectory), 'private persistent Preview session directory was not prepared.');
    $assert(! is_link($sessionDirectory), 'Preview session directory must not be a symlink.');
    $sessionPermissions = fileperms($sessionDirectory);
    $assert(is_int($sessionPermissions) && ($sessionPermissions & 0077) === 0, 'Preview session directory permissions are not private.');
    $assert(! file_exists($install.DIRECTORY_SEPARATOR.'authority.json'), 'single-use authority was not consumed.');

    $content = (string) file_get_contents($pending);
    foreach ([
        'APP_ENV="production"',
        'ONEQAY_RUNTIME_CLASS="preview"',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
        'ONEQAY_PREVIEW_INSTANCE_MODE="single"',
        'ONEQAY_PREVIEW_DATA_CLASS="synthetic"',
        'ONEQAY_PRODUCTION_DATA_ALLOWED="false"',
        'ONEQAY_PERSISTENCE_ENABLED="false"',
        'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="false"',
        'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"',
        'ONEQAY_INSTALLATION_DATABASE_ENGINE="mysql"',
        'ONEQAY_INSTALLATION_DATABASE_SERVER_VERSION="8.0.36"',
        'ONEQAY_INSTALLATION_DATABASE_CHARSET="utf8mb4"',
        'ONEQAY_INSTALLATION_DATABASE_TIMEZONE="+00:00"',
        'ONEQAY_INSTALLATION_DATABASE_SCHEMA_STATE="empty"',
        'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
        'SESSION_DRIVER="file"',
        'SESSION_FILES="'.$shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'sessions"',
        'SESSION_LIFETIME="60"',
        'SESSION_ENCRYPT="true"',
        'SESSION_SECURE_COOKIE="true"',
        'SESSION_COOKIE="oneqay-preview-session"',
        '# PREPARED ONLY — activation requires separate operational authority.',
    ] as $marker) {
        $assert(str_contains($content, $marker), 'pending environment missing '.$marker);
    }
    $assert(! str_contains($content, $token), 'one-time authority value must not enter runtime configuration.');
    $assert(str_contains($content, 'ONEQAY_DB_PASSWORD=" synthetic-value-with-\\$-and-\\"quotes\\" "'), 'configuration value bytes were not preserved safely.');
    $assert(preg_match('/^APP_KEY="base64:[A-Za-z0-9+\\/]{43}="/m', $content) === 1, 'fresh application key was not generated.');
    $postState = $subject->inspect();
    $assert(($postState['state'] ?? null) === 'PENDING_CONFIGURATION_VERIFIED', 'verified pending configuration state was not exposed.');

    try {
        $subject->prepare([
            'installation_token' => $token,
            'app_url' => 'https://preview.example.test',
            'db_host' => 'db.internal.example',
            'db_port' => '3306',
            'db_database' => 'oneqay_preview',
            'db_username' => 'oneqay_runtime',
            'db_password' => 'another-value',
        ]);
        $assert(false, 'pending configuration replay was accepted.');
    } catch (RuntimeException $exception) {
        $assert($exception->getMessage() === 'pending_configuration_already_present', 'unexpected replay denial.');
    }

    $invalidShared = $root.DIRECTORY_SEPARATOR.'invalid';
    $invalidInstall = $invalidShared.DIRECTORY_SEPARATOR.'install';
    $assert(mkdir($invalidInstall, 0700, true), 'invalid fixture directory could not be created.');
    $assert(file_put_contents(
        $invalidInstall.DIRECTORY_SEPARATOR.'authority.json',
        json_encode($authority, JSON_THROW_ON_ERROR),
        LOCK_EX,
    ) !== false, 'invalid authority fixture could not be written.');
    $invalid = new PrebootInstallationConfiguration($invalidShared, $releaseId, $now, $verifiedDatabase);
    try {
        $invalid->prepare([
            'installation_token' => 's184.'.str_repeat('B', 58),
            'app_url' => 'https://preview.example.test',
            'db_host' => 'db.internal.example',
            'db_port' => '3306',
            'db_database' => 'oneqay_preview',
            'db_username' => 'oneqay_runtime',
            'db_password' => 'synthetic-value',
        ]);
        $assert(false, 'incorrect one-time authority was accepted.');
    } catch (RuntimeException $exception) {
        $assert($exception->getMessage() === 'installation_authority_denied', 'incorrect authority failed with an unexpected code.');
    }
    $assert(! file_exists($invalidShared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env.pending'), 'denied request created pending state.');

    fwrite(STDOUT, "Sprint184 preboot installation configuration regression passed.\n");
} finally {
    $remove($root);
}
