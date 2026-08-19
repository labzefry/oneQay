<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\PrivilegedTotpMfaRepository;
use App\Application\Identity\PrivilegedTotpMfaState;
use App\Application\Identity\PrivilegedTotpMfaViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPrivilegedTotpMfaRepository implements PrivilegedTotpMfaRepository
{
    private const TABLE = 'oneqay_identity_totp_factors';
    private const CONTROL_ROLE = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
    private const CONTROL_PERMISSION = AdministrationPermission::MANAGE;
    private const PAYLOAD_VERSION = 1;

    public function __construct(
        private Connection $connection,
        private Encrypter $encrypter,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function protectedControlRequired(TenantId $tenantId, PlatformIdentityId $identityId): bool
    {
        $this->assertOperational();

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();

            if (! $this->canonicalProtectedRoleState($tenant)) {
                return false;
            }

            return $this->connection->table('oneqay_tenant_role_assignments')
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('role_id', self::CONTROL_ROLE)
                ->exists();
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function factorState(TenantId $tenantId, PlatformIdentityId $identityId): PrivilegedTotpMfaState
    {
        $this->assertOperational();

        try {
            $row = $this->factorRow($tenantId->value(), $identityId->value(), false);
            if ($row === null) {
                return new PrivilegedTotpMfaState(PrivilegedTotpMfaState::ABSENT);
            }

            return new PrivilegedTotpMfaState($this->stateFromRow($row));
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function ensurePendingSecret(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] ?string $freshSecret,
        int $createdAtUnix,
    ): string {
        $this->assertOperational();
        $this->assertProtected($tenantId, $identityId);

        if ($createdAtUnix <= 0) {
            $this->stateInvalid();
        }

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $row = $this->factorRow($tenant, $identity, true);

            if ($row === null) {
                if (! is_string($freshSecret) || preg_match('/\A[A-Z2-7]{32}\z/D', $freshSecret) !== 1) {
                    $this->stateInvalid();
                }

                if (! $this->identityExists($tenant, $identity)) {
                    $this->authorizationDenied();
                }

                $inserted = $this->connection->table(self::TABLE)->insert([
                    'tenant_id' => $tenant,
                    'identity_id' => $identity,
                    'secret_ciphertext' => $this->encryptSecret($tenant, $identity, $freshSecret),
                    'created_at_unix' => $createdAtUnix,
                    'confirmed_at_unix' => null,
                    'last_accepted_time_step' => null,
                ]);

                if ($inserted !== true) {
                    $this->storageFailure();
                }

                $row = $this->factorRow($tenant, $identity, true);
                if ($row === null) {
                    $this->storageFailure();
                }
            }

            if (! hash_equals($this->stateFromRow($row), PrivilegedTotpMfaState::PENDING)) {
                throw new PrivilegedTotpMfaViolation(
                    PrivilegedTotpMfaViolation::ENROLLMENT_DENIED,
                    'Privileged TOTP enrollment is not available.',
                );
            }

            return $this->decryptSecret($tenant, $identity, $this->ciphertextFromRow($row));
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function pendingSecret(TenantId $tenantId, PlatformIdentityId $identityId): string
    {
        $this->assertOperational();

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $row = $this->factorRow($tenant, $identity, false);

            if ($row === null
                || ! hash_equals($this->stateFromRow($row), PrivilegedTotpMfaState::PENDING)) {
                $this->verificationFailed();
            }

            return $this->decryptSecret($tenant, $identity, $this->ciphertextFromRow($row));
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function confirmedSecret(TenantId $tenantId, PlatformIdentityId $identityId): string
    {
        $this->assertOperational();

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $row = $this->factorRow($tenant, $identity, false);

            if ($row === null
                || ! hash_equals($this->stateFromRow($row), PrivilegedTotpMfaState::CONFIRMED)) {
                $this->verificationFailed();
            }

            return $this->decryptSecret($tenant, $identity, $this->ciphertextFromRow($row));
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function confirmPendingStep(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $matchedTimeStep,
        int $confirmedAtUnix,
    ): void {
        $this->assertOperational();
        $this->assertProtected($tenantId, $identityId);

        if ($matchedTimeStep < 0 || $confirmedAtUnix <= 0) {
            $this->verificationFailed();
        }

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $row = $this->factorRow($tenant, $identity, true);

            if ($row === null
                || ! hash_equals($this->stateFromRow($row), PrivilegedTotpMfaState::PENDING)
                || ($row->last_accepted_time_step ?? null) !== null) {
                $this->verificationFailed();
            }

            // Context binding is revalidated immediately before mutation.
            $this->decryptSecret($tenant, $identity, $this->ciphertextFromRow($row));

            $updated = $this->connection->table(self::TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNull('confirmed_at_unix')
                ->whereNull('last_accepted_time_step')
                ->update([
                    'confirmed_at_unix' => $confirmedAtUnix,
                    'last_accepted_time_step' => $matchedTimeStep,
                ]);

            if ($updated !== 1) {
                $this->replayDenied();
            }
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function consumeConfirmedStep(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $matchedTimeStep,
    ): void {
        $this->assertOperational();
        $this->assertProtected($tenantId, $identityId);

        if ($matchedTimeStep < 0) {
            $this->verificationFailed();
        }

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $row = $this->factorRow($tenant, $identity, true);

            if ($row === null
                || ! hash_equals($this->stateFromRow($row), PrivilegedTotpMfaState::CONFIRMED)) {
                $this->verificationFailed();
            }

            // Context binding is revalidated immediately before mutation.
            $this->decryptSecret($tenant, $identity, $this->ciphertextFromRow($row));

            $last = $row->last_accepted_time_step ?? null;
            if (! is_numeric($last) || (int) $last < 0 || $matchedTimeStep <= (int) $last) {
                $this->replayDenied();
            }

            $updated = $this->connection->table(self::TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNotNull('confirmed_at_unix')
                ->where('last_accepted_time_step', '<', $matchedTimeStep)
                ->update(['last_accepted_time_step' => $matchedTimeStep]);

            if ($updated !== 1) {
                $this->replayDenied();
            }
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function assertProtected(TenantId $tenantId, PlatformIdentityId $identityId): void
    {
        if (! $this->protectedControlRequired($tenantId, $identityId)) {
            $this->authorizationDenied();
        }
    }

    private function canonicalProtectedRoleState(string $tenantId): bool
    {
        if (! $this->connection->table('oneqay_roles')
            ->where('tenant_id', $tenantId)
            ->where('id', self::CONTROL_ROLE)
            ->exists()) {
            return false;
        }

        $permissions = $this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenantId)
            ->where('role_id', self::CONTROL_ROLE)
            ->get();

        if ($permissions->count() !== 1
            || ! is_string($permissions->first()->permission_id ?? null)
            || ! hash_equals($permissions->first()->permission_id, self::CONTROL_PERMISSION)) {
            return false;
        }

        return ! $this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenantId)
            ->where('permission_id', self::CONTROL_PERMISSION)
            ->where('role_id', '!=', self::CONTROL_ROLE)
            ->exists();
    }

    private function identityExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table('oneqay_identities')
            ->where('tenant_id', $tenantId)
            ->where('id', $identityId)
            ->exists();
    }

    private function factorRow(string $tenantId, string $identityId, bool $forUpdate): ?object
    {
        $query = $this->connection->table(self::TABLE)
            ->where('tenant_id', $tenantId)
            ->where('identity_id', $identityId);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $row = $query->first();

        return is_object($row) ? $row : null;
    }

    private function stateFromRow(object $row): string
    {
        if (! is_numeric($row->created_at_unix ?? null)
            || (int) $row->created_at_unix <= 0
            || ! is_string($row->secret_ciphertext ?? null)
            || $row->secret_ciphertext === '') {
            $this->stateInvalid();
        }

        $confirmed = $row->confirmed_at_unix ?? null;
        $last = $row->last_accepted_time_step ?? null;

        if ($confirmed === null) {
            if ($last !== null) {
                $this->stateInvalid();
            }

            return PrivilegedTotpMfaState::PENDING;
        }

        if (! is_numeric($confirmed)
            || (int) $confirmed <= 0
            || ! is_numeric($last)
            || (int) $last < 0) {
            $this->stateInvalid();
        }

        return PrivilegedTotpMfaState::CONFIRMED;
    }

    private function ciphertextFromRow(object $row): string
    {
        $ciphertext = $row->secret_ciphertext ?? null;
        if (! is_string($ciphertext) || $ciphertext === '') {
            $this->stateInvalid();
        }

        return $ciphertext;
    }

    private function encryptSecret(
        string $tenantId,
        string $identityId,
        #[\SensitiveParameter] string $secret,
    ): string {
        if (preg_match('/\A[A-Z2-7]{32}\z/D', $secret) !== 1) {
            $this->stateInvalid();
        }

        try {
            $payload = json_encode([
                'v' => self::PAYLOAD_VERSION,
                'tenant_id' => $tenantId,
                'identity_id' => $identityId,
                'secret' => $secret,
            ], JSON_THROW_ON_ERROR);
            $ciphertext = $this->encrypter->encryptString($payload);
        } catch (Throwable) {
            $this->storageFailure();
        }

        if (! is_string($ciphertext) || $ciphertext === '') {
            $this->storageFailure();
        }

        return $ciphertext;
    }

    private function decryptSecret(
        string $tenantId,
        string $identityId,
        #[\SensitiveParameter] string $ciphertext,
    ): string {
        try {
            $payload = $this->encrypter->decryptString($ciphertext);
            $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $this->stateInvalid();
        }

        if (! is_array($decoded)
            || array_keys($decoded) !== ['v', 'tenant_id', 'identity_id', 'secret']
            || ($decoded['v'] ?? null) !== self::PAYLOAD_VERSION
            || ! is_string($decoded['tenant_id'] ?? null)
            || ! is_string($decoded['identity_id'] ?? null)
            || ! is_string($decoded['secret'] ?? null)
            || ! hash_equals($tenantId, $decoded['tenant_id'])
            || ! hash_equals($identityId, $decoded['identity_id'])
            || preg_match('/\A[A-Z2-7]{32}\z/D', $decoded['secret']) !== 1) {
            $this->stateInvalid();
        }

        return $decoded['secret'];
    }

    private function assertOperational(): void
    {
        if (! $this->featureEnabled) {
            throw new PrivilegedTotpMfaViolation(
                PrivilegedTotpMfaViolation::FEATURE_DISABLED,
                'Privileged TOTP MFA is disabled.',
            );
        }

        if (! $this->persistenceEnabled) {
            throw new PrivilegedTotpMfaViolation(
                PrivilegedTotpMfaViolation::PERSISTENCE_DISABLED,
                'Privileged TOTP MFA persistence is disabled.',
            );
        }

        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new PrivilegedTotpMfaViolation(
                PrivilegedTotpMfaViolation::RUNTIME_DENIED,
                'Privileged TOTP MFA runtime is not authorized.',
            );
        }
    }

    private function authorizationDenied(): never
    {
        throw new PrivilegedTotpMfaViolation(
            PrivilegedTotpMfaViolation::AUTHORIZATION_DENIED,
            'Privileged TOTP MFA authorization denied.',
        );
    }

    private function verificationFailed(): never
    {
        throw new PrivilegedTotpMfaViolation(
            PrivilegedTotpMfaViolation::VERIFICATION_FAILED,
            'Privileged TOTP MFA verification failed.',
        );
    }

    private function replayDenied(): never
    {
        throw new PrivilegedTotpMfaViolation(
            PrivilegedTotpMfaViolation::REPLAY_DENIED,
            'Privileged TOTP MFA verification failed.',
        );
    }

    private function stateInvalid(): never
    {
        throw new PrivilegedTotpMfaViolation(
            PrivilegedTotpMfaViolation::FACTOR_STATE_INVALID,
            'Privileged TOTP MFA factor state is invalid.',
        );
    }

    private function storageFailure(): never
    {
        throw new PrivilegedTotpMfaViolation(
            PrivilegedTotpMfaViolation::STORAGE_FAILURE,
            'Privileged TOTP MFA storage operation failed.',
        );
    }
}
