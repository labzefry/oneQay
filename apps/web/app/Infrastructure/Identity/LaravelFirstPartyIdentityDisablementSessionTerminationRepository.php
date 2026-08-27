<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\FirstPartyIdentityDisablementSessionTerminationRepository;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstPartyIdentityDisablementSessionTerminationRepository implements FirstPartyIdentityDisablementSessionTerminationRepository
{
    private const SESSION_TABLE = 'oneqay_identity_first_party_sessions';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $sessionControlEnabled,
    ) {}

    public function revokeActiveForIdentityDisablement(
        TenantId $tenantId,
        PlatformIdentityId $targetIdentityId,
        int $revokedAtUnix,
    ): int {
        $this->assertOperational($revokedAtUnix);

        try {
            $updated = $this->connection->table(self::SESSION_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $targetIdentityId->value())
                ->whereNull('revoked_at_unix')
                ->where('expires_at_unix', '>=', $revokedAtUnix)
                ->update(['revoked_at_unix' => $revokedAtUnix]);

            if (! is_int($updated) || $updated < 0) {
                $this->storageFailure();
            }

            return $updated;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function assertOperational(int $revokedAtUnix): void
    {
        if ($revokedAtUnix <= 0) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
                'Identity disablement session termination timestamp is invalid.',
            );
        }

        if (! $this->persistenceEnabled) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::PERSISTENCE_DISABLED,
                'Durable persistence is disabled.',
            );
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RUNTIME_DENIED,
                'Identity disablement session termination runtime is not authorized.',
            );
        }

        if (! $this->sessionControlEnabled) {
            $this->storageFailure();
        }
    }

    private function storageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Identity disablement session termination storage operation failed.',
        );
    }
}
