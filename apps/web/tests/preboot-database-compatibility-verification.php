<?php

declare(strict_types=1);

use App\Infrastructure\Installation\PrebootDatabaseCompatibilityVerification;
use App\Infrastructure\Installation\PrebootInstallationConfiguration;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint185 regression failed: '.$message);
    }
};

$verifier = new PrebootDatabaseCompatibilityVerification();

$compatibleFacts = [
    'connected' => true,
    'engine' => 'mysql',
    'server_version' => '8.0.36',
    'charset' => 'utf8mb4',
    'timezone' => '+00:00',
    'schema_state' => 'empty',
    'least_privilege' => true,
];
$assert($verifier->factsAreCompatible($compatibleFacts), 'compatible MySQL facts were rejected.');

$mariaFacts = $compatibleFacts;
$mariaFacts['engine'] = 'mariadb';
$mariaFacts['server_version'] = '10.11.8-MariaDB';
$mariaFacts['timezone'] = 'UTC';
$mariaFacts['schema_state'] = 'recognized';
$assert($verifier->factsAreCompatible($mariaFacts), 'compatible MariaDB facts were rejected.');

foreach ([
    ['engine', 'postgresql'],
    ['server_version', 'unknown'],
    ['charset', 'latin1'],
    ['timezone', '+07:00'],
    ['schema_state', 'foreign'],
    ['least_privilege', false],
    ['connected', false],
] as [$key, $value]) {
    $facts = $compatibleFacts;
    $facts[$key] = $value;
    $assert(! $verifier->factsAreCompatible($facts), 'unsafe database fact '.$key.' was accepted.');
}

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s185-'.bin2hex(random_bytes(8));
$shared = $root.DIRECTORY_SEPARATOR.'shared';
$install = $shared.DIRECTORY_SEPARATOR.'install';
$releaseId = 'm75-preview-abcdef012345';
$now = 1790000100;
$token = 's185.'.str_repeat('C', 58);

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

$authority = [
    'schema_version' => 1,
    'product' => 'oneQay',
    'release_id' => $releaseId,
    'token_sha256' => hash('sha256', $token),
    'expires_at' => $now + 900,
    'activation_authorized' => false,
    'attribution' => 'Lab | zefry',
];

$input = [
    'installation_token' => $token,
    'app_url' => 'https://preview.example.test',
    'db_host' => 'db.internal.example',
    'db_port' => '3306',
    'db_database' => 'oneqay_preview',
    'db_username' => 'oneqay_runtime',
    'db_password' => 'sprint185-secret',
];

try {
    $assert(mkdir($install, 0700, true), 'fixture installation directory was not created.');
    $authorityPath = $install.DIRECTORY_SEPARATOR.'authority.json';
    $assert(file_put_contents(
        $authorityPath,
        json_encode($authority, JSON_THROW_ON_ERROR),
        LOCK_EX,
    ) !== false, 'fixture authority could not be written.');

    $denied = new PrebootInstallationConfiguration(
        $shared,
        $releaseId,
        $now,
        static fn (array $configuration): array => [
            'ready' => false,
            'facts' => [
                'connected' => false,
                'engine' => '',
                'server_version' => '',
                'charset' => '',
                'timezone' => '',
                'schema_state' => '',
                'least_privilege' => false,
            ],
        ],
    );

    try {
        $denied->prepare($input);
        $assert(false, 'incompatible database verification was allowed to create pending configuration.');
    } catch (RuntimeException $exception) {
        $assert($exception->getMessage() === 'database_compatibility_failed', 'unexpected database denial code.');
    }

    $assert(is_file($authorityPath), 'failed database verification must preserve the unexpired one-time authority for correction/retry.');
    $assert(
        ! file_exists($shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env.pending'),
        'failed database verification must not create pending configuration.',
    );

    $verified = new PrebootInstallationConfiguration(
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

    $result = $verified->prepare($input);
    $assert(($result['prepared'] ?? false) === true, 'verified database did not allow pending preparation.');
    $assert(($result['activation_authorized'] ?? true) === false, 'verification must not grant activation authority.');
    $assert(($verified->inspect()['state'] ?? null) === 'PENDING_CONFIGURATION_VERIFIED', 'verified pending state was not retained.');

    $pending = $shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env.pending';
    $pendingContent = (string) file_get_contents($pending);
    foreach ([
        'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"',
        'ONEQAY_INSTALLATION_DATABASE_ENGINE="mysql"',
        'ONEQAY_INSTALLATION_DATABASE_SERVER_VERSION="8.0.36"',
        'ONEQAY_INSTALLATION_DATABASE_CHARSET="utf8mb4"',
        'ONEQAY_INSTALLATION_DATABASE_TIMEZONE="+00:00"',
        'ONEQAY_INSTALLATION_DATABASE_SCHEMA_STATE="empty"',
        'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"',
        'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
        'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
        'ONEQAY_PERSISTENCE_ENABLED="false"',
    ] as $marker) {
        $assert(str_contains($pendingContent, $marker), 'verified pending evidence missing '.$marker.'.');
    }

    $assert(! str_contains($pendingContent, $token), 'installation token leaked into verified pending configuration.');
    $assert(! file_exists($shared.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'.env'), 'database verification created active runtime configuration.');

    $publicInstaller = (string) file_get_contents(__DIR__.'/../../tools/installation/public-installer.php');
    foreach ([
        'PrebootDatabaseCompatibilityVerification.php',
        'PENDING_CONFIGURATION_VERIFIED',
        'Verify &amp; prepare configuration',
        'read-only database compatibility verification',
    ] as $marker) {
        $assert(str_contains($publicInstaller, $marker), 'public installer is missing Sprint185 contract '.$marker.'.');
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

    $liveHost = trim((string) getenv('ONEQAY_SPRINT185_DB_HOST'));
    if ($liveHost !== '') {
        $livePort = (int) getenv('ONEQAY_SPRINT185_DB_PORT');
        $liveDatabase = trim((string) getenv('ONEQAY_SPRINT185_DB_DATABASE'));
        $liveUsername = trim((string) getenv('ONEQAY_SPRINT185_DB_USERNAME'));
        $livePassword = (string) getenv('ONEQAY_SPRINT185_DB_PASSWORD');

        $liveConfiguration = [
            'db_host' => $liveHost,
            'db_port' => $livePort,
            'db_database' => $liveDatabase,
            'db_username' => $liveUsername,
            'db_password' => $livePassword,
        ];

        $live = $verifier->verify($liveConfiguration);
        $assert(($live['ready'] ?? false) === true, 'live MySQL compatibility verification did not pass.');
        $facts = $live['facts'] ?? [];
        $assert(($facts['connected'] ?? false) === true, 'live MySQL connection was not observed.');
        $assert(($facts['charset'] ?? null) === 'utf8mb4', 'live database charset was not verified.');
        $assert(in_array(($facts['timezone'] ?? null), ['UTC', '+00:00'], true), 'live database timezone was not verified.');
        $assert(($facts['schema_state'] ?? null) === 'empty', 'live database must start from an empty installation schema.');
        $assert(($facts['least_privilege'] ?? false) === true, 'live database account was not recognized as least privilege.');

        $wrongPassword = $liveConfiguration;
        $wrongPassword['db_password'] = $livePassword.'-wrong';
        $deniedLive = $verifier->verify($wrongPassword);
        $assert(($deniedLive['ready'] ?? true) === false, 'invalid live database credentials were accepted.');

        $encodedLive = json_encode([$live, $deniedLive], JSON_THROW_ON_ERROR);
        $assert(! str_contains($encodedLive, $livePassword), 'database password leaked into verification evidence.');
        $assert(! str_contains($encodedLive, 'PDOException'), 'database exception detail leaked into verification evidence.');
    }

    fwrite(STDOUT, "Sprint185 preboot database compatibility verification regression passed.\n");
} finally {
    $remove($root);
}
