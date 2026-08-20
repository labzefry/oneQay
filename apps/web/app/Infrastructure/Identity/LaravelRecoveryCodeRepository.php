<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\RecoveryCodeRepository;
use App\Application\Identity\RecoveryCodeViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelRecoveryCodeRepository implements RecoveryCodeRepository
{
    private const CODE_TABLE = 'oneqay_identity_recovery_codes';
    private const AUDIT_TABLE = 'oneqay_identity_recovery_audit';
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';
    private const TOTP_TABLE = 'oneqay_identity_totp_factors';
    private const CONTROL_ROLE = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
    private const CODE_COUNT = 8;
    private const CODE_PATTERN = '/\Arq1\.([A-Za-z0-9_-]{22})\.([A-Za-z0-9_-]{43})\z/D';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $occurredAtUnix,
        string $correlationId,
    ): array {
        $this->assertOperational();
        if ($occurredAtUnix <= 0 || ! $this->validCorrelationId($correlationId)) {
            $this->rotationFailed();
        }

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $this->lockAndAssertEligible($tenant, $identity, true);

            $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNull('consumed_at_unix')
                ->whereNull('revoked_at_unix')
                ->update(['revoked_at_unix' => $occurredAtUnix]);

            $codes = [];
            for ($index = 0; $index < self::CODE_COUNT; $index++) {
                $material = $this->freshCodeMaterial();
                $inserted = $this->connection->table(self::CODE_TABLE)->insert([
                    'tenant_id' => $tenant,
                    'code_id' => $material['code_id'],
                    'identity_id' => $identity,
                    'code_selector' => $material['selector'],
                    'secret_digest' => $material['digest'],
                    'issued_at_unix' => $occurredAtUnix,
                    'consumed_at_unix' => null,
                    'revoked_at_unix' => null,
                ]);

                if ($inserted !== true) {
                    $this->storageFailure();
                }
                $codes[] = $material['code'];
            }

            $this->insertAudit(
                $tenant,
                $identity,
                'codes_rotated',
                null,
                $correlationId,
                $occurredAtUnix,
            );

            if (count($codes) !== self::CODE_COUNT || count(array_unique($codes)) !== self::CODE_COUNT) {
                $this->storageFailure();
            }

            return $codes;
        } catch (RecoveryCodeViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function consume(
        #[\SensitiveParameter] string $recoveryCode,
        int $occurredAtUnix,
        string $correlationId,
    ): array {
        $this->assertOperational();
        if ($occurredAtUnix <= 0 || ! $this->validCorrelationId($correlationId)) {
            $this->verificationFailed();
        }

        $matches = [];
        if (preg_match(self::CODE_PATTERN, $recoveryCode, $matches) !== 1) {
            $this->verificationFailed();
        }

        try {
            $selector = $matches[1];
            $secret = $matches[2];
            $row = $this->connection->table(self::CODE_TABLE)
                ->where('code_selector', $selector)
                ->lockForUpdate()
                ->first();

            if (! is_object($row)
                || ! is_string($row->tenant_id ?? null)
                || ! is_string($row->identity_id ?? null)
                || ! is_string($row->code_id ?? null)
                || preg_match('/\A[0-9a-f]{32}\z/D', $row->code_id) !== 1
                || ! is_string($row->code_selector ?? null)
                || ! hash_equals($selector, $row->code_selector)
                || ! is_string($row->secret_digest ?? null)
                || preg_match('/\A[0-9a-f]{64}\z/D', $row->secret_digest) !== 1
                || ($row->consumed_at_unix ?? null) !== null
                || ($row->revoked_at_unix ?? null) !== null) {
                $this->verificationFailed();
            }

            $suppliedDigest = hash('sha256', $secret);
            if (! hash_equals($row->secret_digest, $suppliedDigest)) {
                $this->verificationFailed();
            }

            $tenant = TenantId::fromString($row->tenant_id)->value();
            $identity = PlatformIdentityId::fromString($row->identity_id)->value();
            $this->lockAndAssertEligible($tenant, $identity, false);

            $updated = $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('code_id', $row->code_id)
                ->where('code_selector', $selector)
                ->whereNull('consumed_at_unix')
                ->whereNull('revoked_at_unix')
                ->update(['consumed_at_unix' => $occurredAtUnix]);

            if ($updated !== 1) {
                $this->verificationFailed();
            }

            $this->insertAudit(
                $tenant,
                $identity,
                'proof_succeeded',
                $row->code_id,
                $correlationId,
                $occurredAtUnix,
            );

            return [
                'tenant_id' => $tenant,
                'identity_id' => $identity,
                'code_id' => $row->code_id,
                'proved_at_unix' => $occurredAtUnix,
            ];
        } catch (RecoveryCodeViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->verificationFailed();
        }
    }

    private function lockAndAssertEligible(string $tenantId, string $identityId, bool $rotation): void
    {
        $identity = $this->connection->table('oneqay_identities')
            ->where('tenant_id', $tenantId)
            ->where('id', $identityId)
            ->lockForUpdate()
            ->first();

        $eligible = is_object($identity)
            && $this->connection->table(self::CREDENTIAL_TABLE)
                ->where('tenant_id', $tenantId)
                ->where('identity_id', $identityId)
                ->exists()
            && ! $this->connection->table('oneqay_tenant_role_assignments')
                ->where('tenant_id', $tenantId)
                ->where('identity_id', $identityId)
                ->where('role_id', self::CONTROL_ROLE)
                ->exists()
            && ! $this->connection->table(self::TOTP_TABLE)
                ->where('tenant_id', $tenantId)
                ->where('identity_id', $identityId)
                ->whereNotNull('confirmed_at_unix')
                ->exists();

        if (! $eligible) {
            $rotation ? $this->rotationFailed() : $this->verificationFailed();
        }
    }

    /** @return array{code_id:string,selector:string,digest:string,code:string} */
    private function freshCodeMaterial(): array
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $codeId = bin2hex(random_bytes(16));
            $selector = $this->base64Url(random_bytes(16));
            $secret = $this->base64Url(random_bytes(32));

            if (strlen($selector) !== 22 || strlen($secret) !== 43) {
                $this->storageFailure();
            }

            if ($this->connection->table(self::CODE_TABLE)->where('code_selector', $selector)->exists()) {
                continue;
            }

            $code = 'rq1.'.$selector.'.'.$secret;
            if (preg_match(self::CODE_PATTERN, $code) !== 1) {
                $this->storageFailure();
            }

            return [
                'code_id' => $codeId,
                'selector' => $selector,
                'digest' => hash('sha256', $secret),
                'code' => $code,
            ];
        }

        $this->storageFailure();
    }

    private function insertAudit(
        string $tenantId,
        string $identityId,
        string $eventType,
        ?string $codeId,
        string $correlationId,
        int $occurredAtUnix,
    ): void {
        if (! in_array($eventType, ['codes_rotated', 'proof_succeeded'], true)) {
            $this->storageFailure();
        }

        $inserted = $this->connection->table(self::AUDIT_TABLE)->insert([
            'tenant_id' => $tenantId,
            'audit_id' => bin2hex(random_bytes(16)),
            'identity_id' => $identityId,
            'event_type' => $eventType,
            'code_id' => $codeId,
            'correlation_id' => $correlationId,
            'occurred_at_unix' => $occurredAtUnix,
        ]);

        if ($inserted !== true) {
            $this->storageFailure();
        }
    }

    private function base64Url(#[\SensitiveParameter] string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private function validCorrelationId(string $correlationId): bool
    {
        return $correlationId !== '' && strlen($correlationId) <= 128;
    }

    private function assertOperational(): void
    {
        if (! $this->featureEnabled) {
            throw new RecoveryCodeViolation(
                RecoveryCodeViolation::FEATURE_DISABLED,
                'Authentication recovery request failed.',
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
                'Durable persistence runtime is not authorized.',
            );
        }
    }

    private function rotationFailed(): never
    {
        throw new RecoveryCodeViolation(
            RecoveryCodeViolation::ROTATION_FAILED,
            'Authentication recovery request failed.',
        );
    }

    private function verificationFailed(): never
    {
        throw new RecoveryCodeViolation(
            RecoveryCodeViolation::VERIFICATION_FAILED,
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
