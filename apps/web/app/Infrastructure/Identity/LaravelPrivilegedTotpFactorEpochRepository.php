<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\PrivilegedTotpFactorEpochRepository;
use App\Application\Identity\PrivilegedTotpRecoveryViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPrivilegedTotpFactorEpochRepository implements PrivilegedTotpFactorEpochRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function currentEpoch(TenantId $tenantId, PlatformIdentityId $identityId): int
    {
        $this->assertOperational();
        try {
            $row = $this->connection->table('oneqay_identity_totp_factors')
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->whereNotNull('confirmed_at_unix')
                ->first();
            if (! is_object($row) || ! is_numeric($row->factor_epoch ?? null)) {
                $this->invalid();
            }
            $epoch = (int) $row->factor_epoch;
            if ($epoch < 0 || (string) $epoch !== (string) (int) $epoch) {
                $this->invalid();
            }
            return $epoch;
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePersistenceViolation(DurablePersistenceViolation::STORAGE_FAILURE, 'Privileged TOTP factor epoch storage failed.');
        }
    }

    private function assertOperational(): void
    {
        if (! $this->featureEnabled) {
            throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::FEATURE_DISABLED, 'Privileged TOTP recovery request failed.');
        }
        if (! $this->persistenceEnabled) {
            throw new DurablePersistenceViolation(DurablePersistenceViolation::PERSISTENCE_DISABLED, 'Durable persistence is disabled.');
        }
        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(DurablePersistenceViolation::RUNTIME_DENIED, 'Durable persistence runtime is not authorized.');
        }
    }

    private function invalid(): never
    {
        throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::EPOCH_INVALID, 'Privileged TOTP recovery request failed.');
    }
}
