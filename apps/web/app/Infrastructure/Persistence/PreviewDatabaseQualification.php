<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Tenancy\TenantId;
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
     * permanent table, business record, or Production data is created. Every
     * tenant-scoped application operation derives its tenant predicate from the
     * server-verified context supplied by the Preview journey.
     *
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function qualify(array $config, ?VerifiedTenantContext $tenantContext = null): array
    {
        $checks = $this->initialChecks();

        $violations = self::configurationViolations($config);
        if ($violations !== []) {
            return $this->blocked('configuration', $checks);
        }
        $checks['configuration'] = 'verified';

        try {
            $verifiedTenant = (new RequireVerifiedTenantContext())->require($tenantContext);
            $tenantId = TenantId::fromString($verifiedTenant->tenantId())->value();
        } catch (MissingTenantContext) {
            return $this->blocked('tenant_context', $checks);
        }

        $foreignTenantId = match ($tenantId) {
            'tenant-alpha' => 'tenant-beta',
            'tenant-beta' => 'tenant-alpha',
            default => null,
        };
        if ($foreignTenantId === null) {
            return $this->blocked('tenant_context', $checks);
        }
        $checks['tenant_context'] = 'verified';

        if (! in_array('mysql', PDO::getAvailableDrivers(), true)) {
            return $this->blocked('pdo_mysql_driver', $checks);
        }
        $checks['pdo_mysql_driver'] = 'verified';

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
                .'business_id VARCHAR(64) NOT NULL, '
                .'value_text VARCHAR(64) NOT NULL, '
                .'UNIQUE KEY uq_preview_tenant_business (tenant_id, business_id)'
                .') ENGINE=InnoDB',
                self::TABLE,
            ));
            $checks['temporary_table'] = 'verified';

            $phase = 'transaction_rollback';
            $pdo->beginTransaction();
            $insert = $pdo->prepare(sprintf(
                'INSERT INTO %s (id, tenant_id, business_id, value_text) VALUES (?, ?, ?, ?)',
                self::TABLE,
            ));
            $insert->execute([1, $tenantId, 'rollback-probe', 'rollback-probe']);
            $pdo->rollBack();

            $countAfterRollback = (int) $pdo->query(sprintf(
                'SELECT COUNT(*) FROM %s',
                self::TABLE,
            ))->fetchColumn();
            if ($countAfterRollback !== 0) {
                return $this->blocked($phase, $checks);
            }
            $checks['transaction_rollback'] = 'verified';

            $pdo->beginTransaction();

            $phase = 'tenant_owned_insert';
            $insert->execute([11, $tenantId, 'shared-resource', 'current-synthetic']);
            $insert->execute([12, $foreignTenantId, 'shared-resource', 'foreign-synthetic']);
            $insert->execute([13, $tenantId, 'current-only', 'current-only-synthetic']);
            $insert->execute([14, $tenantId, 'delete-control', 'delete-control-synthetic']);

            $ownedCountStatement = $pdo->prepare(sprintf(
                'SELECT COUNT(*) FROM %s WHERE tenant_id = ? AND id IN (11, 13, 14)',
                self::TABLE,
            ));
            $ownedCountStatement->execute([$tenantId]);
            if ((int) $ownedCountStatement->fetchColumn() !== 3) {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_owned_insert'] = 'verified';

            $phase = 'tenant_scoped_query';
            $tenantCountStatement = $pdo->prepare(sprintf(
                'SELECT COUNT(*) FROM %s WHERE tenant_id = ?',
                self::TABLE,
            ));
            $tenantCountStatement->execute([$tenantId]);
            if ((int) $tenantCountStatement->fetchColumn() !== 3) {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_scoped_query'] = 'verified';

            $phase = 'tenant_isolation_read';
            $readStatement = $pdo->prepare(sprintf(
                'SELECT value_text FROM %s WHERE tenant_id = ? AND id = ?',
                self::TABLE,
            ));
            $readStatement->execute([$tenantId, 11]);
            $currentValue = $readStatement->fetchColumn();
            $readStatement->execute([$tenantId, 12]);
            $foreignValueThroughCurrentScope = $readStatement->fetchColumn();
            if ($currentValue !== 'current-synthetic' || $foreignValueThroughCurrentScope !== false) {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_isolation_read'] = 'verified';

            $phase = 'tenant_identity_collision';
            $collisionStatement = $pdo->prepare(sprintf(
                'SELECT value_text FROM %s WHERE tenant_id = ? AND business_id = ?',
                self::TABLE,
            ));
            $collisionStatement->execute([$tenantId, 'shared-resource']);
            if ($collisionStatement->fetchColumn() !== 'current-synthetic') {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_identity_collision'] = 'verified';

            $phase = 'tenant_isolation_enumeration';
            $enumerationStatement = $pdo->prepare(sprintf(
                'SELECT tenant_id, business_id FROM %s WHERE tenant_id = ? ORDER BY id',
                self::TABLE,
            ));
            $enumerationStatement->execute([$tenantId]);
            $rows = $enumerationStatement->fetchAll();
            if (count($rows) !== 3) {
                return $this->blocked($phase, $checks);
            }
            foreach ($rows as $row) {
                if (($row['tenant_id'] ?? null) !== $tenantId) {
                    return $this->blocked($phase, $checks);
                }
            }
            $checks['tenant_isolation_enumeration'] = 'verified';

            $phase = 'tenant_isolation_update';
            $updateStatement = $pdo->prepare(sprintf(
                'UPDATE %s SET value_text = ? WHERE tenant_id = ? AND id = ?',
                self::TABLE,
            ));
            $updateStatement->execute(['current-updated', $tenantId, 13]);
            $currentUpdated = $updateStatement->rowCount();
            $updateStatement->execute(['foreign-blocked', $tenantId, 12]);
            $foreignUpdated = $updateStatement->rowCount();
            if ($currentUpdated !== 1 || $foreignUpdated !== 0) {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_isolation_update'] = 'verified';

            $phase = 'tenant_isolation_delete';
            $deleteStatement = $pdo->prepare(sprintf(
                'DELETE FROM %s WHERE tenant_id = ? AND id = ?',
                self::TABLE,
            ));
            $deleteStatement->execute([$tenantId, 14]);
            $currentDeleted = $deleteStatement->rowCount();
            $deleteStatement->execute([$tenantId, 12]);
            $foreignDeleted = $deleteStatement->rowCount();
            if ($currentDeleted !== 1 || $foreignDeleted !== 0) {
                return $this->blocked($phase, $checks);
            }
            $checks['tenant_isolation_delete'] = 'verified';

            $phase = 'transaction_rollback';
            $pdo->rollBack();
            $countAfterIsolationRollback = (int) $pdo->query(sprintf(
                'SELECT COUNT(*) FROM %s',
                self::TABLE,
            ))->fetchColumn();
            if ($countAfterIsolationRollback !== 0) {
                return $this->blocked($phase, $checks);
            }

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

    /** @return array<string, string> */
    private function initialChecks(): array
    {
        return [
            'configuration' => 'not_checked',
            'tenant_context' => 'not_checked',
            'pdo_mysql_driver' => 'not_checked',
            'connection' => 'not_checked',
            'engine_family' => 'not_checked',
            'temporary_table' => 'not_checked',
            'transaction_rollback' => 'not_checked',
            'tenant_owned_insert' => 'not_checked',
            'tenant_scoped_query' => 'not_checked',
            'tenant_isolation_read' => 'not_checked',
            'tenant_identity_collision' => 'not_checked',
            'tenant_isolation_enumeration' => 'not_checked',
            'tenant_isolation_update' => 'not_checked',
            'tenant_isolation_delete' => 'not_checked',
        ];
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
