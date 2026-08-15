<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;
use Throwable;

// Author by Lab | zefry
final class PreviewDatabaseQualification
{
    private const TABLE = 'oneqay_preview_qualification';

    /**
     * @param array<string, mixed> $config
     * @return list<string>
     */
    public static function configurationViolations(array $config): array
    {
        $violations = [];

        if (($config['enabled'] ?? false) !== true) {
            $violations[] = 'enabled';
        }

        if (strtolower(trim((string) ($config['profile'] ?? ''))) !== 'mariadb') {
            $violations[] = 'profile';
        }

        foreach (['host', 'database', 'username', 'password'] as $key) {
            if (trim((string) ($config[$key] ?? '')) === '') {
                $violations[] = $key;
            }
        }

        $port = (int) ($config['port'] ?? 0);
        if ($port < 1 || $port > 65535) {
            $violations[] = 'port';
        }

        if (strtolower(trim((string) ($config['charset'] ?? ''))) !== 'utf8mb4') {
            $violations[] = 'charset';
        }

        return array_values(array_unique($violations));
    }

    /**
     * Execute a bounded, non-persistent Technical Preview relational qualification.
     *
     * The probe creates one connection-scoped TEMPORARY TABLE only. No migration,
     * permanent table, business record, or Production data is created.
     *
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function qualify(array $config): array
    {
        $violations = self::configurationViolations($config);
        if ($violations !== []) {
            return $this->blocked('configuration', [
                'configuration' => 'blocked',
                'pdo_mysql_driver' => 'not_checked',
                'connection' => 'not_checked',
                'engine_family' => 'not_checked',
                'temporary_table' => 'not_checked',
                'transaction_rollback' => 'not_checked',
                'tenant_scoped_query' => 'not_checked',
            ]);
        }

        if (! in_array('mysql', PDO::getAvailableDrivers(), true)) {
            return $this->blocked('pdo_mysql_driver', [
                'configuration' => 'verified',
                'pdo_mysql_driver' => 'blocked',
                'connection' => 'not_checked',
                'engine_family' => 'not_checked',
                'temporary_table' => 'not_checked',
                'transaction_rollback' => 'not_checked',
                'tenant_scoped_query' => 'not_checked',
            ]);
        }

        $checks = [
            'configuration' => 'verified',
            'pdo_mysql_driver' => 'verified',
            'connection' => 'not_checked',
            'engine_family' => 'not_checked',
            'temporary_table' => 'not_checked',
            'transaction_rollback' => 'not_checked',
            'tenant_scoped_query' => 'not_checked',
        ];

        $phase = 'connection';
        $pdo = null;

        try {
            $pdo = new PDO(
                sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    (string) $config['host'],
                    (int) $config['port'],
                    (string) $config['database'],
                    (string) $config['charset'],
                ),
                (string) $config['username'],
                (string) $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
            $checks['connection'] = 'verified';

            $phase = 'engine_family';
            $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
            if (! str_contains(strtolower($version), 'mariadb')) {
                return $this->blocked($phase, $checks);
            }
            $checks['engine_family'] = 'verified';

            $phase = 'temporary_table';
            $pdo->exec(sprintf(
                'CREATE TEMPORARY TABLE %s ('
                .'id INT NOT NULL PRIMARY KEY, '
                .'tenant_id VARCHAR(64) NOT NULL, '
                .'value_text VARCHAR(64) NOT NULL'
                .') ENGINE=InnoDB',
                self::TABLE,
            ));
            $checks['temporary_table'] = 'verified';

            $phase = 'transaction_rollback';
            $pdo->beginTransaction();
            $statement = $pdo->prepare(sprintf(
                'INSERT INTO %s (id, tenant_id, value_text) VALUES (?, ?, ?)',
                self::TABLE,
            ));
            $statement->execute([1, 'tenant-alpha', 'rollback-probe']);
            $pdo->rollBack();

            $countAfterRollback = (int) $pdo->query(sprintf(
                'SELECT COUNT(*) FROM %s',
                self::TABLE,
            ))->fetchColumn();
            if ($countAfterRollback !== 0) {
                return $this->blocked($phase, $checks);
            }
            $checks['transaction_rollback'] = 'verified';

            $phase = 'tenant_scoped_query';
            $pdo->beginTransaction();
            $statement->execute([11, 'tenant-alpha', 'alpha-synthetic']);
            $statement->execute([12, 'tenant-beta', 'beta-synthetic']);

            $tenantStatement = $pdo->prepare(sprintf(
                'SELECT COUNT(*) FROM %s WHERE tenant_id = ?',
                self::TABLE,
            ));
            $tenantStatement->execute(['tenant-alpha']);
            $alphaCount = (int) $tenantStatement->fetchColumn();
            $tenantStatement->execute(['tenant-gamma']);
            $unknownCount = (int) $tenantStatement->fetchColumn();
            $pdo->rollBack();

            if ($alphaCount !== 1 || $unknownCount !== 0) {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_scoped_query'] = 'verified';

            return [
                'status' => 'qualified',
                'scope' => 'technical-preview-relational-probe',
                'production_ready' => false,
                'persistent_schema_created' => false,
                'engine_profile' => 'mariadb',
                'engine_version' => $this->safeVersion($version),
                'checks' => $checks,
            ];
        } catch (Throwable) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                try {
                    $pdo->rollBack();
                } catch (Throwable) {
                    // Preserve the original fail-closed qualification result.
                }
            }

            return $this->blocked($phase, $checks);
        }
    }

    /** @param array<string, string> $checks */
    private function blocked(string $failedCheck, array $checks): array
    {
        if (array_key_exists($failedCheck, $checks)) {
            $checks[$failedCheck] = 'blocked';
        }

        return [
            'status' => 'blocked',
            'scope' => 'technical-preview-relational-probe',
            'production_ready' => false,
            'persistent_schema_created' => false,
            'failed_check' => $failedCheck,
            'checks' => $checks,
        ];
    }

    private function safeVersion(string $version): string
    {
        return preg_match('/\d+\.\d+\.\d+/', $version, $matches) === 1
            ? $matches[0]
            : 'detected';
    }
}
