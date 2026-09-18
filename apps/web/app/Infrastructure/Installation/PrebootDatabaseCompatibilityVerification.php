<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use PDO;
use Throwable;

// Author by Lab | zefry
final class PrebootDatabaseCompatibilityVerification
{
    private const CONNECTION_TIMEOUT_SECONDS = 5;

    /**
     * @param array{
     *   db_host: string,
     *   db_port: int,
     *   db_database: string,
     *   db_username: string,
     *   db_password: string
     * } $configuration
     * @return array{
     *   ready: bool,
     *   facts: array{
     *     connected: bool,
     *     engine: string,
     *     server_version: string,
     *     charset: string,
     *     timezone: string,
     *     schema_state: string,
     *     least_privilege: bool
     *   }
     * }
     */
    public function verify(array $configuration): array
    {
        if (! extension_loaded('pdo_mysql')) {
            return $this->unavailable();
        }

        try {
            $pdo = new PDO(
                $this->dsn($configuration),
                $configuration['db_username'],
                $configuration['db_password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => self::CONNECTION_TIMEOUT_SECONDS,
                ],
            );

            $facts = $this->observeFacts($pdo, $configuration['db_database']);

            return [
                'ready' => $this->factsAreCompatible($facts),
                'facts' => $facts,
            ];
        } catch (Throwable) {
            return $this->unavailable();
        }
    }

    /**
     * Deterministic compatibility evaluation for already-observed safe facts.
     *
     * @param array{
     *   connected: bool,
     *   engine: string,
     *   server_version: string,
     *   charset: string,
     *   timezone: string,
     *   schema_state: string,
     *   least_privilege: bool
     * } $facts
     */
    public function factsAreCompatible(array $facts): bool
    {
        $engine = strtolower(trim($facts['engine']));
        $version = trim($facts['server_version']);
        $charset = strtolower(trim($facts['charset']));
        $timezone = strtoupper(trim($facts['timezone']));
        $schemaState = strtolower(trim($facts['schema_state']));

        return $facts['connected'] === true
            && in_array($engine, ['mysql', 'mariadb'], true)
            && preg_match('/\A\d+\.\d+(?:\.\d+)?(?:[-+._A-Za-z0-9]*)?\z/', $version) === 1
            && $charset === 'utf8mb4'
            && in_array($timezone, ['UTC', '+00:00'], true)
            && in_array($schemaState, ['empty', 'recognized'], true)
            && $facts['least_privilege'] === true;
    }

    /**
     * @param array{
     *   db_host: string,
     *   db_port: int,
     *   db_database: string,
     *   db_username: string,
     *   db_password: string
     * } $configuration
     */
    private function dsn(array $configuration): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $configuration['db_host'],
            $configuration['db_port'],
            $configuration['db_database'],
        );
    }

    /**
     * @return array{
     *   connected: bool,
     *   engine: string,
     *   server_version: string,
     *   charset: string,
     *   timezone: string,
     *   schema_state: string,
     *   least_privilege: bool
     * }
     */
    private function observeFacts(PDO $pdo, string $configuredDatabase): array
    {
        $identity = $pdo->query(
            'SELECT VERSION() AS server_version, DATABASE() AS database_name, '
            .'@@character_set_database AS charset, @@session.time_zone AS session_timezone, '
            .'TIMEDIFF(NOW(), UTC_TIMESTAMP()) AS utc_offset'
        )?->fetch();

        if (! is_array($identity)) {
            return $this->unavailable()['facts'];
        }

        $observedDatabase = trim((string) ($identity['database_name'] ?? ''));
        if ($observedDatabase === '' || ! hash_equals($configuredDatabase, $observedDatabase)) {
            return $this->unavailable()['facts'];
        }

        $version = trim((string) ($identity['server_version'] ?? ''));
        $timezone = strtoupper(trim((string) ($identity['session_timezone'] ?? '')));
        if ($timezone === 'SYSTEM') {
            $offset = trim((string) ($identity['utc_offset'] ?? ''));
            $timezone = $offset === '00:00:00' ? '+00:00' : $offset;
        }

        $tableRows = $pdo->query(
            'SELECT TABLE_NAME AS table_name '
            .'FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'
        )?->fetchAll();

        if (! is_array($tableRows)) {
            return $this->unavailable()['facts'];
        }

        $tableNames = [];
        foreach ($tableRows as $row) {
            if (is_array($row)) {
                $tableNames[] = strtolower(trim((string) ($row['table_name'] ?? '')));
            }
        }
        $tableNames = array_values(array_filter($tableNames, static fn (string $name): bool => $name !== ''));

        $schemaState = $tableNames === []
            ? 'empty'
            : (in_array('migrations', $tableNames, true) || in_array('oneqay_tenants', $tableNames, true)
                ? 'recognized'
                : 'foreign');

        $grantRows = $pdo->query('SHOW GRANTS FOR CURRENT_USER()')?->fetchAll(PDO::FETCH_NUM);
        if (! is_array($grantRows)) {
            return $this->unavailable()['facts'];
        }

        $grants = [];
        foreach ($grantRows as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $grant) {
                if (is_string($grant) && $grant !== '') {
                    $grants[] = $grant;
                }
            }
        }

        return [
            'connected' => true,
            'engine' => str_contains(strtolower($version), 'mariadb') ? 'mariadb' : 'mysql',
            'server_version' => $version,
            'charset' => strtolower(trim((string) ($identity['charset'] ?? ''))),
            'timezone' => $timezone,
            'schema_state' => $schemaState,
            'least_privilege' => $this->grantsAreLeastPrivilege($grants, $configuredDatabase),
        ];
    }

    /** @param list<string> $grants */
    private function grantsAreLeastPrivilege(array $grants, string $databaseName): bool
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
            ] as $forbiddenPrivilege) {
                if (str_contains($normalized, $forbiddenPrivilege)) {
                    return false;
                }
            }

            if (str_contains($normalized, $scope)) {
                $hasScopedGrant = true;
            }
        }

        return $hasScopedGrant;
    }

    /**
     * @return array{
     *   ready: false,
     *   facts: array{
     *     connected: false,
     *     engine: string,
     *     server_version: string,
     *     charset: string,
     *     timezone: string,
     *     schema_state: string,
     *     least_privilege: false
     *   }
     * }
     */
    private function unavailable(): array
    {
        return [
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
        ];
    }
}
