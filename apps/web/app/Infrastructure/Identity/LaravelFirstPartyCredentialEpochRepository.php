<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\FirstPartyCredentialEpochRepository;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstPartyCredentialEpochRepository implements FirstPartyCredentialEpochRepository
{
    private const AUDIT_TABLE = 'oneqay_identity_recovery_audit';
    private const COMPLETION_EVENT = 'password_reset_completed';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
    ) {}

    public function current(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
    ): int {
        $this->assertOperational();

        try {
            if (! $this->connection->getSchemaBuilder()->hasTable(self::AUDIT_TABLE)) {
                return 0;
            }

            $count = $this->connection->table(self::AUDIT_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->where('event_type', self::COMPLETION_EVENT)
                ->count();

            if (! is_int($count) || $count < 0) {
                $this->storageFailure();
            }

            return $count;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::PERSISTENCE_DISABLED,
                'Durable persistence is disabled.',
            );
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RUNTIME_DENIED,
                'Durable persistence runtime is not authorized.',
            );
        }
    }

    private function storageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Credential epoch storage operation failed.',
        );
    }
}
