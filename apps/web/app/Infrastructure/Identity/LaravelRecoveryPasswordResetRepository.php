<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\RecoveryPasswordResetRepository;
use App\Application\Identity\RecoveryPasswordResetViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelRecoveryPasswordResetRepository implements RecoveryPasswordResetRepository
{
    private const CODE_TABLE = 'oneqay_identity_recovery_codes';
    private const AUDIT_TABLE = 'oneqay_identity_recovery_audit';
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';
    private const TOTP_TABLE = 'oneqay_identity_totp_factors';
    private const CONTROL_ROLE = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
    private const CODE_ID_PATTERN = '/\A[0-9a-f]{32}\z/D';
    private const MIN_PASSWORD_BYTES = 12;
    private const MAX_PASSWORD_BYTES = 4096;

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function complete(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $codeId,
        #[\SensitiveParameter] string $password,
        int $occurredAtUnix,
        string $correlationId,
    ): void {
        $this->assertOperational();

        if (preg_match(self::CODE_ID_PATTERN, $codeId) !== 1
            || $occurredAtUnix <= 0
            || $correlationId === ''
            || strlen($correlationId) > 128
            || strlen($password) < self::MIN_PASSWORD_BYTES
            || strlen($password) > self::MAX_PASSWORD_BYTES) {
            $this->resetFailed();
        }

        $tenant = $tenantId->value();
        $identity = $identityId->value();

        try {
            $code = $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('code_id', $codeId)
                ->lockForUpdate()
                ->first();

            if (! is_object($code)
                || ! is_string($code->identity_id ?? null)
                || ! hash_equals($identity, $code->identity_id)
                || ! is_int($code->consumed_at_unix ?? null)
                || $code->consumed_at_unix <= 0
                || ($code->revoked_at_unix ?? null) !== null) {
                $this->resetFailed();
            }

            $proofExists = $this->connection->table(self::AUDIT_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('code_id', $codeId)
                ->where('event_type', 'proof_succeeded')
                ->exists();
            if (! $proofExists) {
                $this->resetFailed();
            }

            $alreadyCompleted = $this->connection->table(self::AUDIT_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('code_id', $codeId)
                ->where('event_type', 'password_reset_completed')
                ->exists();
            if ($alreadyCompleted) {
                $this->resetFailed();
            }

            $identityRow = $this->connection->table('oneqay_identities')
                ->where('tenant_id', $tenant)
                ->where('id', $identity)
                ->lockForUpdate()
                ->first();
            if (! is_object($identityRow)) {
                $this->resetFailed();
            }

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
                || $credential->credential_epoch === PHP_INT_MAX) {
                $this->resetFailed();
            }
            $credentialEpoch = $credential->credential_epoch;

            $protectedControl = $this->connection->table('oneqay_tenant_role_assignments')
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('role_id', self::CONTROL_ROLE)
                ->exists();
            if ($protectedControl) {
                $this->resetFailed();
            }

            $confirmedTotp = $this->connection->table(self::TOTP_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNotNull('confirmed_at_unix')
                ->exists();
            if ($confirmedTotp) {
                $this->resetFailed();
            }

            $replacementHash = password_hash($password, PASSWORD_DEFAULT);
            if (! is_string($replacementHash) || $replacementHash === '') {
                $this->storageFailure();
            }

            $updated = $this->connection->table(self::CREDENTIAL_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('credential_epoch', $credentialEpoch)
                ->update([
                    'password_hash' => $replacementHash,
                    'credential_epoch' => $credentialEpoch + 1,
                ]);
            if ($updated !== 1) {
                $this->resetFailed();
            }

            $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('code_id', '<>', $codeId)
                ->whereNull('consumed_at_unix')
                ->whereNull('revoked_at_unix')
                ->update(['revoked_at_unix' => $occurredAtUnix]);

            $inserted = $this->connection->table(self::AUDIT_TABLE)->insert([
                'tenant_id' => $tenant,
                'audit_id' => bin2hex(random_bytes(16)),
                'identity_id' => $identity,
                'event_type' => 'password_reset_completed',
                'code_id' => $codeId,
                'correlation_id' => $correlationId,
                'occurred_at_unix' => $occurredAtUnix,
            ]);
            if ($inserted !== true) {
                $this->storageFailure();
            }
        } catch (RecoveryPasswordResetViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->featureEnabled) {
            $this->resetFailed();
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
                'Durable persistence runtime is not authorized.',
            );
        }
    }

    private function resetFailed(): never
    {
        throw new RecoveryPasswordResetViolation(
            RecoveryPasswordResetViolation::RESET_FAILED,
            'Authentication recovery request failed.',
        );
    }

    private function storageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Authentication recovery storage operation failed.',
        );
    }
}
