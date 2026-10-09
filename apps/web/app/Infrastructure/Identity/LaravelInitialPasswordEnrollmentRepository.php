<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\InitialPasswordEnrollmentId;
use App\Application\Identity\InitialPasswordEnrollmentRepository;
use App\Application\Identity\InitialPasswordEnrollmentViolation;
use App\Application\Identity\IssuedInitialPasswordEnrollment;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelInitialPasswordEnrollmentRepository implements InitialPasswordEnrollmentRepository
{
    private const CONTROL_ROLE = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
    private const CONTROL_PERMISSION = AdministrationPermission::MANAGE;
    private const ENROLLMENT_TABLE = 'oneqay_initial_password_enrollments';
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';

    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {}

    public function issueFresh(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        InitialPasswordEnrollmentId $enrollmentId,
        int $issuedAtUnix,
        int $expiresAtUnix,
    ): IssuedInitialPasswordEnrollment {
        $this->assertRuntimeAllowedForPersistence();

        if ($issuedAtUnix <= 0 || $expiresAtUnix !== $issuedAtUnix + 900) {
            $this->relationshipConflict();
        }

        try {
            $tenant = $actor->tenantId()->value();
            $actorIdentity = $actor->identityId()->value();
            $target = $targetIdentityId->value();

            if (hash_equals($actorIdentity, $target)) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::SELF_ENROLLMENT_DENIED,
                    'Initial password enrollment self-issuance is denied.',
                );
            }

            if (! $this->tenantControlAuthorityExists($tenant, $actorIdentity)
                || ! $this->protectedRoleStateCompatible($tenant)) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::AUTHORIZATION_DENIED,
                    'Initial password enrollment issuance authorization denied.',
                );
            }

            if (! $this->identityExists($tenant, $target)) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::TARGET_INELIGIBLE,
                    'Initial password enrollment target is not eligible.',
                );
            }

            if ($this->credentialExists($tenant, $target)) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::CREDENTIAL_ALREADY_EXISTS,
                    'Initial password enrollment target already has a credential.',
                );
            }

            if ($this->enrollmentIdExists($tenant, $enrollmentId->value())) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::ENROLLMENT_CONFLICT,
                    'Initial password enrollment identifier is already bound.',
                );
            }

            $this->connection->table(self::ENROLLMENT_TABLE)
                ->where('tenant_id', $tenant)
                ->where('target_identity_id', $target)
                ->where('active_marker', 1)
                ->whereNull('consumed_at_unix')
                ->where('expires_at_unix', '<', $issuedAtUnix)
                ->update(['active_marker' => null]);

            if ($this->activeEnrollmentExists($tenant, $target)) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::ACTIVE_ENROLLMENT_EXISTS,
                    'An active initial password enrollment already exists for the target.',
                );
            }

            $token = $this->generateEnrollmentToken();
            $digest = hash('sha256', $token);

            $inserted = $this->connection->table(self::ENROLLMENT_TABLE)->insert([
                'tenant_id' => $tenant,
                'enrollment_id' => $enrollmentId->value(),
                'actor_identity_id' => $actorIdentity,
                'target_identity_id' => $target,
                'token_digest' => $digest,
                'issued_at_unix' => $issuedAtUnix,
                'expires_at_unix' => $expiresAtUnix,
                'consumed_at_unix' => null,
                'active_marker' => 1,
            ]);

            if ($inserted !== true) {
                $this->relationshipConflict();
            }

            $persistedDigest = $this->connection->table(self::ENROLLMENT_TABLE)
                ->where('tenant_id', $tenant)
                ->where('enrollment_id', $enrollmentId->value())
                ->value('token_digest');

            if (! is_string($persistedDigest) || ! hash_equals($persistedDigest, $digest)) {
                $this->relationshipConflict();
            }

            return new IssuedInitialPasswordEnrollment(
                $enrollmentId,
                $targetIdentityId,
                $token,
                $expiresAtUnix,
            );
        } catch (InitialPasswordEnrollmentViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    public function redeem(
        TenantId $tenantId,
        PlatformIdentityId $targetIdentityId,
        InitialPasswordEnrollmentId $enrollmentId,
        #[\SensitiveParameter] string $enrollmentToken,
        #[\SensitiveParameter] string $password,
        int $occurredAtUnix,
    ): string {
        $this->assertRuntimeAllowedForPersistence();

        if ($occurredAtUnix <= 0) {
            $this->relationshipConflict();
        }

        try {
            $tenant = $tenantId->value();
            $target = $targetIdentityId->value();
            $row = $this->connection->table(self::ENROLLMENT_TABLE)
                ->where('tenant_id', $tenant)
                ->where('enrollment_id', $enrollmentId->value())
                ->first();

            if ($row === null
                || ! is_string($row->target_identity_id ?? null)
                || ! hash_equals($row->target_identity_id, $target)
                || ! is_string($row->token_digest ?? null)
                || preg_match('/\A[0-9a-f]{64}\z/', $row->token_digest) !== 1) {
                $this->invalidEnrollment();
            }

            $suppliedDigest = hash('sha256', $enrollmentToken);
            if (! hash_equals($row->token_digest, $suppliedDigest)) {
                $this->invalidEnrollment();
            }

            $consumedAt = $row->consumed_at_unix ?? null;
            if ($consumedAt !== null) {
                if (is_numeric($consumedAt) && (int) $consumedAt > 0 && $this->credentialExists($tenant, $target)) {
                    return self::OUTCOME_APPLIED;
                }

                $this->relationshipConflict();
            }

            if ((int) ($row->active_marker ?? 0) !== 1
                || ! is_numeric($row->issued_at_unix ?? null)
                || ! is_numeric($row->expires_at_unix ?? null)
                || (int) $row->issued_at_unix <= 0
                || (int) $row->expires_at_unix <= (int) $row->issued_at_unix
                || $occurredAtUnix > (int) $row->expires_at_unix) {
                $this->invalidEnrollment();
            }

            if (! $this->identityExists($tenant, $target)) {
                $this->invalidEnrollment();
            }

            if ($this->credentialExists($tenant, $target)) {
                $this->fail(
                    InitialPasswordEnrollmentViolation::CREDENTIAL_ALREADY_EXISTS,
                    'Initial password enrollment target already has a credential.',
                );
            }

            $passwordHash = $this->hashPassword($password);

            $insertedCredential = $this->connection->table(self::CREDENTIAL_TABLE)->insert([
                'tenant_id' => $tenant,
                'identity_id' => $target,
                'password_hash' => $passwordHash,
            ]);

            if ($insertedCredential !== true) {
                $this->relationshipConflict();
            }

            $consumed = $this->connection->table(self::ENROLLMENT_TABLE)
                ->where('tenant_id', $tenant)
                ->where('enrollment_id', $enrollmentId->value())
                ->where('target_identity_id', $target)
                ->where('active_marker', 1)
                ->whereNull('consumed_at_unix')
                ->update([
                    'consumed_at_unix' => $occurredAtUnix,
                    'active_marker' => null,
                ]);

            if ($consumed !== 1) {
                $this->relationshipConflict();
            }

            $storedHash = $this->connection->table(self::CREDENTIAL_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $target)
                ->value('password_hash');

            if (! is_string($storedHash) || ! hash_equals($storedHash, $passwordHash)) {
                $this->relationshipConflict();
            }

            return self::OUTCOME_APPLIED;
        } catch (InitialPasswordEnrollmentViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    private function tenantControlAuthorityExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table('oneqay_tenant_role_assignments as a')
            ->join('oneqay_role_permissions as p', function ($join): void {
                $join->on('p.tenant_id', '=', 'a.tenant_id')->on('p.role_id', '=', 'a.role_id');
            })
            ->where('a.tenant_id', $tenantId)
            ->where('a.identity_id', $identityId)
            ->where('p.permission_id', self::CONTROL_PERMISSION)
            ->exists();
    }

    private function protectedRoleStateCompatible(string $tenantId): bool
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

    private function credentialExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table(self::CREDENTIAL_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('identity_id', $identityId)
            ->exists();
    }

    private function enrollmentIdExists(string $tenantId, string $enrollmentId): bool
    {
        return $this->connection->table(self::ENROLLMENT_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('enrollment_id', $enrollmentId)
            ->exists();
    }

    private function activeEnrollmentExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table(self::ENROLLMENT_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('target_identity_id', $identityId)
            ->where('active_marker', 1)
            ->whereNull('consumed_at_unix')
            ->exists();
    }

    private function generateEnrollmentToken(): string
    {
        try {
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        } catch (Throwable) {
            $this->fail(
                InitialPasswordEnrollmentViolation::STORAGE_FAILURE,
                'Initial password enrollment token generation failed.',
            );
        }

        if (preg_match('/\A[A-Za-z0-9_-]{43}\z/', $token) !== 1) {
            $this->fail(
                InitialPasswordEnrollmentViolation::STORAGE_FAILURE,
                'Initial password enrollment token generation failed.',
            );
        }

        return $token;
    }

    private function hashPassword(#[\SensitiveParameter] string $password): string
    {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
        } catch (Throwable) {
            $this->fail(
                InitialPasswordEnrollmentViolation::INVALID_PASSWORD,
                'Initial password enrollment password could not be hashed.',
            );
        }

        if (! is_string($hash) || $hash === '' || strlen($hash) > 255) {
            $this->fail(
                InitialPasswordEnrollmentViolation::INVALID_PASSWORD,
                'Initial password enrollment password could not be hashed.',
            );
        }

        return $hash;
    }

    private function assertRuntimeAllowedForPersistence(): void
    {
        if (! $this->enabled) {
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

    private function invalidEnrollment(): never
    {
        $this->fail(
            InitialPasswordEnrollmentViolation::INVALID_ENROLLMENT,
            'Initial password enrollment could not be completed.',
        );
    }

    private function relationshipConflict(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
            'Initial password enrollment durable relationship conflict.',
        );
    }

    private function persistenceStorageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Initial password enrollment storage operation failed.',
        );
    }

    private function fail(string $code, string $message): never
    {
        throw new InitialPasswordEnrollmentViolation($code, $message);
    }
}
