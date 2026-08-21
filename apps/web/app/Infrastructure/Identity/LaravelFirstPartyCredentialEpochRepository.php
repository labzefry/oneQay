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
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';
    private const CANONICAL_EPOCH_PATTERN = '/\A(?:0|[1-9][0-9]*)\z/D';

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
            $row = $this->connection->table(self::CREDENTIAL_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->first(['credential_epoch']);

            if (! is_object($row)) {
                $this->storageFailure();
            }

            return $this->canonicalEpoch($row->credential_epoch ?? null);
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function canonicalEpoch(mixed $value): int
    {
        if (is_int($value)) {
            if ($value < 0) {
                $this->storageFailure();
            }

            return $value;
        }

        if (! is_string($value)
            || preg_match(self::CANONICAL_EPOCH_PATTERN, $value) !== 1
            || strlen($value) > strlen((string) PHP_INT_MAX)
            || (strlen($value) === strlen((string) PHP_INT_MAX) && strcmp($value, (string) PHP_INT_MAX) > 0)) {
            $this->storageFailure();
        }

        return (int) $value;
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
