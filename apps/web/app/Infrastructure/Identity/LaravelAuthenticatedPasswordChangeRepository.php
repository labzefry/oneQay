<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\AuthenticatedPasswordChangeRepository;
use App\Application\Identity\AuthenticatedPasswordChangeViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelAuthenticatedPasswordChangeRepository implements AuthenticatedPasswordChangeRepository
{
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';
    private const RECOVERY_CODE_TABLE = 'oneqay_identity_recovery_codes';
    private const MIN_NEW_PASSWORD_BYTES = 12;
    private const MAX_PASSWORD_BYTES = 4096;

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
    ) {}

    public function change(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $expectedCredentialEpoch,
        #[\SensitiveParameter] string $currentPassword,
        #[\SensitiveParameter] string $newPassword,
        int $occurredAtUnix,
    ): void {
        $this->assertOperational();

        if ($expectedCredentialEpoch < 0
            || $currentPassword === ''
            || strlen($currentPassword) > self::MAX_PASSWORD_BYTES
            || strlen($newPassword) < self::MIN_NEW_PASSWORD_BYTES
            || strlen($newPassword) > self::MAX_PASSWORD_BYTES
            || $occurredAtUnix <= 0) {
            $this->changeFailed();
        }

        $tenant = $tenantId->value();
        $identity = $identityId->value();

        try {
            $credential = $this->connection->table(self::CREDENTIAL_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->lockForUpdate()
                ->first();

            if (! is_object($credential)
                || ! is_string($credential->password_hash ?? null)
                || $credential->password_hash === ''
                || ! is_int($credential->credential_epoch ?? null)
                || $credential->credential_epoch < 0
                || $credential->credential_epoch !== $expectedCredentialEpoch) {
                $this->changeFailed();
            }

            $currentHash = $credential->password_hash;
            $currentEpoch = $credential->credential_epoch;

            if (! password_verify($currentPassword, $currentHash)
                || password_verify($newPassword, $currentHash)
                || $currentEpoch === PHP_INT_MAX) {
                $this->changeFailed();
            }

            $replacementHash = password_hash($newPassword, PASSWORD_DEFAULT);
            if (! is_string($replacementHash) || $replacementHash === '') {
                $this->storageFailure();
            }

            $updated = $this->connection->table(self::CREDENTIAL_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('credential_epoch', $currentEpoch)
                ->update([
                    'password_hash' => $replacementHash,
                    'credential_epoch' => $currentEpoch + 1,
                ]);
            if ($updated !== 1) {
                $this->changeFailed();
            }

            $this->connection->table(self::RECOVERY_CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNull('consumed_at_unix')
                ->whereNull('revoked_at_unix')
                ->update(['revoked_at_unix' => $occurredAtUnix]);
        } catch (AuthenticatedPasswordChangeViolation|DurablePersistenceViolation $exception) {
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

    private function changeFailed(): never
    {
        throw new AuthenticatedPasswordChangeViolation(
            AuthenticatedPasswordChangeViolation::CHANGE_FAILED,
            'Password change request failed.',
        );
    }

    private function storageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Password change storage operation failed.',
        );
    }
}
