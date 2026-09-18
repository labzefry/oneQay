<?php

declare(strict_types=1);

namespace App\Delivery\Http\SystemUpdate;

use App\Application\SystemUpdate\SystemUpdateControlPlane;
use App\Infrastructure\Installation\SecureInstallationReadiness;
use Illuminate\Database\ConnectionInterface;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

// Author by Lab | zefry
final class SystemUpdatePageController
{
    public function __construct(
        private readonly SystemUpdateControlPlane $controlPlane,
        private readonly SecureInstallationReadiness $installationReadiness,
    ) {
    }

    public function __invoke(): Response
    {
        $manifest = $this->loadGovernedReleaseManifest();
        $artifact = $this->observeReleaseArtifact($manifest);
        $database = $this->observeDatabaseState();
        $host = $this->observeHostPlatformState();

        $preflight = $this->installationReadiness->assess(
            $this->environmentSnapshot(),
            get_loaded_extensions(),
            PHP_VERSION,
            null,
            $manifest,
            $artifact,
            $database,
            $host,
        );

        return Inertia::render('System/UpdateDeployment', [
            'status' => $this->controlPlane->status()->toSafeArray(),
            'ui' => [
                'mode' => 'READ_ONLY',
                'install_action_exposed' => false,
                'check_action_exposed' => false,
                'production_ready' => false,
            ],
            'installation_preflight' => [
                'mode' => 'READ_ONLY',
                'ready' => $preflight['ready'],
                'actions_exposed' => false,
                'checks' => $preflight['checks'],
                'evidence' => [
                    'release_manifest' => $manifest === null ? 'MISSING' : 'OBSERVED',
                    'release_artifact' => $artifact === null ? 'MISSING' : 'OBSERVED',
                    'database' => $database === null
                        ? 'NOT_CONFIGURED'
                        : (($database['connected'] ?? false) === true ? 'OBSERVED' : 'UNAVAILABLE'),
                    'host_platform' => 'SERVER_OBSERVED_PARTIAL',
                ],
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function environmentSnapshot(): array
    {
        return [
            'APP_ENV' => (string) config('app.env', ''),
            'APP_KEY' => (string) config('app.key', ''),
            'APP_URL' => (string) config('app.url', ''),
            'APP_DEBUG' => (bool) config('app.debug', false),
            'ONEQAY_DB_DRIVER' => (string) config('database.connections.oneqay.driver', ''),
            'ONEQAY_DB_HOST' => (string) config('database.connections.oneqay.host', ''),
            'ONEQAY_DB_DATABASE' => (string) config('database.connections.oneqay.database', ''),
            'ONEQAY_DB_USERNAME' => (string) config('database.connections.oneqay.username', ''),
        ];
    }

    /** @return array<string, mixed>|null */
    private function loadGovernedReleaseManifest(): ?array
    {
        $path = $this->releaseDirectory().DIRECTORY_SEPARATOR.'manifest.json';
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        try {
            $raw = file_get_contents($path);
            if (! is_string($raw) || $raw === '') {
                return null;
            }

            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed>|null $manifest
     * @return array{filename: string, size: int, sha256: string}|null
     */
    private function observeReleaseArtifact(?array $manifest): ?array
    {
        $filename = $manifest['artifact_filename'] ?? null;
        if (! is_string($filename)
            || $filename === ''
            || basename($filename) !== $filename
            || str_contains($filename, chr(0))) {
            return null;
        }

        $path = $this->releaseDirectory().DIRECTORY_SEPARATOR.$filename;
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        $sha256 = hash_file('sha256', $path);
        if (! is_int($size) || $size <= 0 || ! is_string($sha256)) {
            return null;
        }

        return [
            'filename' => $filename,
            'size' => $size,
            'sha256' => $sha256,
        ];
    }

    /** @return array<string, mixed>|null */
    private function observeDatabaseState(): ?array
    {
        $databaseName = trim((string) config('database.connections.oneqay.database', ''));
        $username = trim((string) config('database.connections.oneqay.username', ''));
        if ($databaseName === '' || $username === '') {
            return null;
        }

        try {
            /** @var ConnectionInterface $connection */
            $connection = app('db')->connection('oneqay');
            $connection->getPdo();

            $identity = $connection->selectOne(
                'SELECT VERSION() AS server_version, @@character_set_database AS charset, '
                .'@@session.time_zone AS session_timezone, TIMEDIFF(NOW(), UTC_TIMESTAMP()) AS utc_offset'
            );
            if (! is_object($identity)) {
                return $this->unavailableDatabaseState();
            }

            $version = trim((string) ($identity->server_version ?? ''));
            $timezone = strtoupper(trim((string) ($identity->session_timezone ?? '')));
            if ($timezone === 'SYSTEM') {
                $offset = trim((string) ($identity->utc_offset ?? ''));
                $timezone = $offset === '00:00:00' ? '+00:00' : $offset;
            }

            $tables = $connection->select(
                'SELECT TABLE_NAME AS table_name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'
            );
            $tableNames = array_map(
                static fn (object $row): string => strtolower((string) ($row->table_name ?? '')),
                $tables,
            );
            $schemaState = $tableNames === []
                ? 'empty'
                : (in_array('migrations', $tableNames, true) || in_array('oneqay_tenants', $tableNames, true)
                    ? 'recognized'
                    : 'foreign');

            $grantRows = $connection->select('SHOW GRANTS FOR CURRENT_USER()');
            $grants = [];
            foreach ($grantRows as $row) {
                foreach ((array) $row as $grant) {
                    if (is_string($grant)) {
                        $grants[] = $grant;
                    }
                }
            }

            return [
                'connected' => true,
                'engine' => str_contains(strtolower($version), 'mariadb') ? 'mariadb' : 'mysql',
                'server_version' => $version,
                'charset' => strtolower(trim((string) ($identity->charset ?? ''))),
                'timezone' => $timezone,
                'schema_state' => $schemaState,
                'least_privilege' => $this->databaseGrantsAreLeastPrivilege($grants, $databaseName),
            ];
        } catch (Throwable) {
            return $this->unavailableDatabaseState();
        }
    }

    /** @return array{connected: bool, engine: string, server_version: string, charset: string, timezone: string, schema_state: string, least_privilege: bool} */
    private function unavailableDatabaseState(): array
    {
        return [
            'connected' => false,
            'engine' => '',
            'server_version' => '',
            'charset' => '',
            'timezone' => '',
            'schema_state' => '',
            'least_privilege' => false,
        ];
    }

    /** @param list<string> $grants */
    private function databaseGrantsAreLeastPrivilege(array $grants, string $databaseName): bool
    {
        if ($grants === []) {
            return false;
        }

        $scope = ' ON '.strtoupper($databaseName).'.*';
        $hasScopedGrant = false;

        foreach ($grants as $grant) {
            $normalized = strtoupper(str_replace(chr(96), '', trim($grant)));

            if (str_contains($normalized, ' WITH GRANT OPTION')) {
                return false;
            }

            if (str_contains($normalized, ' ON *.*')
                && ! str_starts_with($normalized, 'GRANT USAGE ON *.*')) {
                return false;
            }

            foreach ([
                ' FILE',
                ' PROCESS',
                ' RELOAD',
                ' SHUTDOWN',
                ' SUPER',
                ' CREATE USER',
                ' SYSTEM_USER',
                ' SYSTEM_VARIABLES_ADMIN',
                ' SESSION_VARIABLES_ADMIN',
                ' BINLOG_ADMIN',
                ' CONNECTION_ADMIN',
                ' REPLICATION ',
            ] as $forbidden) {
                if (str_contains($normalized, $forbidden)) {
                    return false;
                }
            }

            if (str_contains($normalized, $scope)) {
                $hasScopedGrant = true;
            }
        }

        return $hasScopedGrant;
    }

    /** @return array<string, mixed> */
    private function observeHostPlatformState(): array
    {
        $disk = disk_free_space(base_path());
        $temp = sys_get_temp_dir();

        return [
            'os_family' => strtolower(PHP_OS_FAMILY),
            'web_server_interface' => strtolower(PHP_SAPI),
            'memory_bytes' => $this->iniBytes((string) ini_get('memory_limit')),
            'execution_time_seconds' => max(0, (int) ini_get('max_execution_time')),
            'disk_free_bytes' => is_float($disk) || is_int($disk) ? max(0, (int) $disk) : 0,
            'capabilities' => [
                'https' => request()->isSecure(),
                'dns' => function_exists('dns_get_record'),
                // These capabilities cannot be truthfully proven from PHP process state alone.
                'time_sync' => false,
                'outbound_allowlist' => false,
                'scheduler' => false,
                'archive' => extension_loaded('zip') || extension_loaded('phar'),
                'temp_directory' => is_dir($temp) && is_writable($temp),
                'required_tools' => false,
            ],
        ];
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        if (preg_match('/\A(\d+)([KMG]?)\z/i', $value, $matches) !== 1) {
            return 0;
        }

        $bytes = (int) $matches[1];
        $multiplier = match (strtoupper($matches[2])) {
            'K' => 1024,
            'M' => 1024 ** 2,
            'G' => 1024 ** 3,
            default => 1,
        };

        return $bytes > intdiv(PHP_INT_MAX, $multiplier)
            ? PHP_INT_MAX
            : $bytes * $multiplier;
    }

    private function releaseDirectory(): string
    {
        return dirname(base_path(), 2).DIRECTORY_SEPARATOR.'release';
    }
}
