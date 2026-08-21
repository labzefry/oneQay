<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\PrivilegedTotpRecoveryRepository;
use App\Application\Identity\PrivilegedTotpRecoveryViolation;
use App\Application\Identity\VerifiedPrivilegedTotpRecoveryProof;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPrivilegedTotpRecoveryRepository implements PrivilegedTotpRecoveryRepository
{
    private const FACTOR_TABLE = 'oneqay_identity_totp_factors';
    private const CODE_TABLE = 'oneqay_identity_totp_recovery_codes';
    private const AUDIT_TABLE = 'oneqay_identity_totp_recovery_audit';
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';
    private const CODE_COUNT = 8;
    private const CODE_PATTERN = '/\Amq1\.([A-Za-z0-9_-]{22})\.([A-Za-z0-9_-]{43})\z/D';
    private const SECRET_PATTERN = '/\A[A-Z2-7]{32}\z/D';
    private const FACTOR_PAYLOAD_VERSION = 1;
    private const REPLACEMENT_PAYLOAD_VERSION = 1;

    public function __construct(
        private Connection $connection,
        private Encrypter $encrypter,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $factorEpoch,
        int $occurredAtUnix,
        string $correlationId,
    ): array {
        $this->assertOperational();
        if ($factorEpoch < 0 || $occurredAtUnix <= 0 || ! $this->validCorrelation($correlationId)) {
            $this->rotationFailed();
        }

        try {
            $tenant = $tenantId->value();
            $identity = $identityId->value();
            $factor = $this->lockConfirmedFactor($tenant, $identity);
            if ((int) $factor->factor_epoch !== $factorEpoch || ! $this->credentialExists($tenant, $identity)) {
                $this->rotationFailed();
            }

            $this->revokeUnused($tenant, $identity, $occurredAtUnix);
            $codes = [];
            for ($i = 0; $i < self::CODE_COUNT; $i++) {
                $material = $this->freshCodeMaterial();
                $inserted = $this->connection->table(self::CODE_TABLE)->insert([
                    'tenant_id' => $tenant,
                    'code_id' => $material['code_id'],
                    'identity_id' => $identity,
                    'factor_epoch' => $factorEpoch,
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

            $this->audit($tenant, $identity, 'codes_rotated', null, $factorEpoch, $correlationId, $occurredAtUnix);
            return $codes;
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation $exception) {
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
        if ($occurredAtUnix <= 0 || ! $this->validCorrelation($correlationId)) {
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
                || ! is_numeric($row->factor_epoch ?? null)
                || (int) $row->factor_epoch < 0
                || ! is_string($row->secret_digest ?? null)
                || preg_match('/\A[0-9a-f]{64}\z/D', $row->secret_digest) !== 1
                || ($row->consumed_at_unix ?? null) !== null
                || ($row->revoked_at_unix ?? null) !== null
                || ! hash_equals($row->secret_digest, hash('sha256', $secret))) {
                $this->verificationFailed();
            }

            $tenant = TenantId::fromString($row->tenant_id)->value();
            $identity = PlatformIdentityId::fromString($row->identity_id)->value();
            $factor = $this->lockConfirmedFactor($tenant, $identity);
            $factorEpoch = (int) $row->factor_epoch;
            if ((int) $factor->factor_epoch !== $factorEpoch || ! $this->credentialExists($tenant, $identity)) {
                $this->verificationFailed();
            }

            $updated = $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('code_id', $row->code_id)
                ->where('factor_epoch', $factorEpoch)
                ->whereNull('consumed_at_unix')
                ->whereNull('revoked_at_unix')
                ->update(['consumed_at_unix' => $occurredAtUnix]);
            if ($updated !== 1) {
                $this->verificationFailed();
            }

            $this->audit($tenant, $identity, 'proof_succeeded', $row->code_id, $factorEpoch, $correlationId, $occurredAtUnix);

            return [
                'tenant_id' => $tenant,
                'identity_id' => $identity,
                'code_id' => $row->code_id,
                'factor_epoch' => $factorEpoch,
                'proved_at_unix' => $occurredAtUnix,
            ];
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->verificationFailed();
        }
    }

    public function assertProofCurrent(VerifiedPrivilegedTotpRecoveryProof $proof): void
    {
        $this->assertOperational();
        try {
            $tenant = $proof->tenantId()->value();
            $identity = $proof->identityId()->value();
            $factor = $this->connection->table(self::FACTOR_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNotNull('confirmed_at_unix')
                ->first();
            $code = $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('code_id', $proof->codeId())
                ->first();
            if (! is_object($factor)
                || ! is_numeric($factor->factor_epoch ?? null)
                || (int) $factor->factor_epoch !== $proof->factorEpoch()
                || ! is_object($code)
                || ! is_numeric($code->factor_epoch ?? null)
                || (int) $code->factor_epoch !== $proof->factorEpoch()
                || ! is_numeric($code->consumed_at_unix ?? null)
                || ($code->revoked_at_unix ?? null) !== null) {
                $this->stateInvalid();
            }
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->stateInvalid();
        }
    }

    public function sealReplacementSecret(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $secret,
        int $issuedAtUnix,
    ): string {
        $this->assertOperational();
        if (preg_match(self::SECRET_PATTERN, $secret) !== 1 || $issuedAtUnix <= 0) {
            $this->stateInvalid();
        }
        $this->assertProofCurrent($proof);
        try {
            $payload = json_encode([
                'v' => self::REPLACEMENT_PAYLOAD_VERSION,
                'tenant_id' => $proof->tenantId()->value(),
                'identity_id' => $proof->identityId()->value(),
                'code_id' => $proof->codeId(),
                'factor_epoch' => $proof->factorEpoch(),
                'issued_at_unix' => $issuedAtUnix,
                'secret' => $secret,
            ], JSON_THROW_ON_ERROR);
            $sealed = $this->encrypter->encryptString($payload);
            if (! is_string($sealed) || $sealed === '') {
                $this->storageFailure();
            }
            return $sealed;
        } catch (PrivilegedTotpRecoveryViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function openReplacementSecret(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $sealedReplacement,
    ): string {
        $this->assertOperational();
        $this->assertProofCurrent($proof);
        try {
            $decoded = json_decode($this->encrypter->decryptString($sealedReplacement), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($decoded)
                || array_keys($decoded) !== ['v', 'tenant_id', 'identity_id', 'code_id', 'factor_epoch', 'issued_at_unix', 'secret']
                || ($decoded['v'] ?? null) !== self::REPLACEMENT_PAYLOAD_VERSION
                || ! is_string($decoded['tenant_id'] ?? null)
                || ! hash_equals($proof->tenantId()->value(), $decoded['tenant_id'])
                || ! is_string($decoded['identity_id'] ?? null)
                || ! hash_equals($proof->identityId()->value(), $decoded['identity_id'])
                || ! is_string($decoded['code_id'] ?? null)
                || ! hash_equals($proof->codeId(), $decoded['code_id'])
                || ! is_int($decoded['factor_epoch'] ?? null)
                || $decoded['factor_epoch'] !== $proof->factorEpoch()
                || ! is_int($decoded['issued_at_unix'] ?? null)
                || $decoded['issued_at_unix'] <= 0
                || ! is_string($decoded['secret'] ?? null)
                || preg_match(self::SECRET_PATTERN, $decoded['secret']) !== 1) {
                $this->stateInvalid();
            }
            return $decoded['secret'];
        } catch (PrivilegedTotpRecoveryViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->stateInvalid();
        }
    }

    public function replaceFactor(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $newSecret,
        int $matchedTimeStep,
        int $occurredAtUnix,
        string $correlationId,
    ): int {
        $this->assertOperational();
        if (preg_match(self::SECRET_PATTERN, $newSecret) !== 1
            || $matchedTimeStep < 0
            || $occurredAtUnix <= 0
            || ! $this->validCorrelation($correlationId)) {
            $this->replacementFailed();
        }

        try {
            $tenant = $proof->tenantId()->value();
            $identity = $proof->identityId()->value();
            $factor = $this->lockConfirmedFactor($tenant, $identity);
            $oldEpoch = $proof->factorEpoch();
            if ((int) $factor->factor_epoch !== $oldEpoch) {
                $this->replacementFailed();
            }

            $code = $this->connection->table(self::CODE_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('code_id', $proof->codeId())
                ->where('factor_epoch', $oldEpoch)
                ->whereNotNull('consumed_at_unix')
                ->whereNull('revoked_at_unix')
                ->lockForUpdate()
                ->first();
            if (! is_object($code)) {
                $this->replacementFailed();
            }

            $newEpoch = $oldEpoch + 1;
            $updated = $this->connection->table(self::FACTOR_TABLE)
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->whereNotNull('confirmed_at_unix')
                ->where('factor_epoch', $oldEpoch)
                ->update([
                    'secret_ciphertext' => $this->encryptFactorSecret($tenant, $identity, $newSecret),
                    'confirmed_at_unix' => $occurredAtUnix,
                    'last_accepted_time_step' => $matchedTimeStep,
                    'factor_epoch' => $newEpoch,
                ]);
            if ($updated !== 1) {
                $this->replacementFailed();
            }

            $this->revokeUnused($tenant, $identity, $occurredAtUnix);
            $this->audit($tenant, $identity, 'factor_replaced', $proof->codeId(), $newEpoch, $correlationId, $occurredAtUnix);
            return $newEpoch;
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->replacementFailed();
        }
    }

    private function lockConfirmedFactor(string $tenant, string $identity): object
    {
        $factor = $this->connection->table(self::FACTOR_TABLE)
            ->where('tenant_id', $tenant)
            ->where('identity_id', $identity)
            ->whereNotNull('confirmed_at_unix')
            ->lockForUpdate()
            ->first();
        if (! is_object($factor) || ! is_numeric($factor->factor_epoch ?? null) || (int) $factor->factor_epoch < 0) {
            $this->stateInvalid();
        }
        return $factor;
    }

    private function credentialExists(string $tenant, string $identity): bool
    {
        return $this->connection->table(self::CREDENTIAL_TABLE)
            ->where('tenant_id', $tenant)
            ->where('identity_id', $identity)
            ->exists();
    }

    private function revokeUnused(string $tenant, string $identity, int $occurredAtUnix): void
    {
        $this->connection->table(self::CODE_TABLE)
            ->where('tenant_id', $tenant)
            ->where('identity_id', $identity)
            ->whereNull('consumed_at_unix')
            ->whereNull('revoked_at_unix')
            ->update(['revoked_at_unix' => $occurredAtUnix]);
    }

    /** @return array{code_id:string,selector:string,digest:string,code:string} */
    private function freshCodeMaterial(): array
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $codeId = bin2hex(random_bytes(16));
            $selector = $this->base64Url(random_bytes(16));
            $secret = $this->base64Url(random_bytes(32));
            if ($this->connection->table(self::CODE_TABLE)->where('code_selector', $selector)->exists()) {
                continue;
            }
            $code = 'mq1.'.$selector.'.'.$secret;
            if (preg_match(self::CODE_PATTERN, $code) !== 1) {
                $this->storageFailure();
            }
            return ['code_id' => $codeId, 'selector' => $selector, 'digest' => hash('sha256', $secret), 'code' => $code];
        }
        $this->storageFailure();
    }

    private function encryptFactorSecret(string $tenant, string $identity, #[\SensitiveParameter] string $secret): string
    {
        try {
            $payload = json_encode([
                'v' => self::FACTOR_PAYLOAD_VERSION,
                'tenant_id' => $tenant,
                'identity_id' => $identity,
                'secret' => $secret,
            ], JSON_THROW_ON_ERROR);
            return $this->encrypter->encryptString($payload);
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function audit(string $tenant, string $identity, string $event, ?string $codeId, int $factorEpoch, string $correlationId, int $occurredAtUnix): void
    {
        if (! in_array($event, ['codes_rotated', 'proof_succeeded', 'factor_replaced'], true)) {
            $this->storageFailure();
        }
        $inserted = $this->connection->table(self::AUDIT_TABLE)->insert([
            'tenant_id' => $tenant,
            'audit_id' => bin2hex(random_bytes(16)),
            'identity_id' => $identity,
            'event_type' => $event,
            'code_id' => $codeId,
            'factor_epoch' => $factorEpoch,
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

    private function validCorrelation(string $correlationId): bool
    {
        return $correlationId !== '' && strlen($correlationId) <= 128;
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

    private function rotationFailed(): never { throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::ROTATION_FAILED, 'Privileged TOTP recovery request failed.'); }
    private function verificationFailed(): never { throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::VERIFICATION_FAILED, 'Privileged TOTP recovery request failed.'); }
    private function stateInvalid(): never { throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::RECOVERY_STATE_INVALID, 'Privileged TOTP recovery request failed.'); }
    private function replacementFailed(): never { throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::REPLACEMENT_FAILED, 'Privileged TOTP recovery request failed.'); }
    private function storageFailure(): never { throw new DurablePersistenceViolation(DurablePersistenceViolation::STORAGE_FAILURE, 'Privileged TOTP recovery storage operation failed.'); }
}
