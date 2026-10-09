<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\FirstPartySessionAuthorityRepository;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Application\Identity\IssuedFirstPartySessionAuthority;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstPartySessionAuthorityRepository implements FirstPartySessionAuthorityRepository
{
    private const SESSION_TABLE = 'oneqay_identity_first_party_sessions';
    private const AUDIT_TABLE = 'oneqay_identity_first_party_session_audit';
    private const AUTHORITY_PATTERN = '/\A[0-9a-f]{32}\z/D';
    private const HANDLE_PATTERN = '/\A[A-Za-z0-9_-]{43}\z/D';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function issue(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        int $credentialEpoch,
        ?int $factorEpoch,
        string $authorityId,
        string $publicHandle,
        int $issuedAtUnix,
        int $expiresAtUnix,
        string $correlationId,
    ): IssuedFirstPartySessionAuthority {
        $this->assertOperational();
        $this->assertAuthorityId($authorityId);
        $this->assertHandle($publicHandle);
        $this->assertTimestampPair($issuedAtUnix, $expiresAtUnix);
        $this->assertCorrelationId($correlationId);
        if ($credentialEpoch < 0 || ($factorEpoch !== null && $factorEpoch < 0)) {
            $this->invalidState();
        }

        try {
            $this->connection->transaction(function () use (
                $tenantId,
                $identityId,
                $organizationId,
                $outletId,
                $deviceId,
                $credentialEpoch,
                $factorEpoch,
                $authorityId,
                $publicHandle,
                $issuedAtUnix,
                $expiresAtUnix,
                $correlationId,
            ): void {
                $inserted = $this->connection->table(self::SESSION_TABLE)->insert([
                    'tenant_id' => $tenantId->value(),
                    'authority_id' => $authorityId,
                    'public_handle' => $publicHandle,
                    'identity_id' => $identityId->value(),
                    'organization_id' => $organizationId,
                    'outlet_id' => $outletId,
                    'device_id' => $deviceId,
                    'credential_epoch' => $credentialEpoch,
                    'factor_epoch' => $factorEpoch,
                    'issued_at_unix' => $issuedAtUnix,
                    'last_seen_at_unix' => $issuedAtUnix,
                    'expires_at_unix' => $expiresAtUnix,
                    'revoked_at_unix' => null,
                ]);
                if ($inserted !== true) {
                    $this->storageFailure();
                }

                $this->insertAudit(
                    $tenantId->value(),
                    $identityId->value(),
                    null,
                    $authorityId,
                    'session_issued',
                    $correlationId,
                    $issuedAtUnix,
                );
            });
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }

        return new IssuedFirstPartySessionAuthority($authorityId, $publicHandle);
    }

    public function ownedByAuthorityId(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
    ): ?array {
        $this->assertOperational();
        $this->assertAuthorityId($authorityId);

        try {
            $row = $this->connection->table(self::SESSION_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->where('authority_id', $authorityId)
                ->first();
            return is_object($row) ? $this->rowArray($row) : null;
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function ownedByPublicHandle(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $publicHandle,
    ): ?array {
        $this->assertOperational();
        if (preg_match(self::HANDLE_PATTERN, $publicHandle) !== 1) {
            return null;
        }

        try {
            $row = $this->connection->table(self::SESSION_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->where('public_handle', $publicHandle)
                ->first();
            return is_object($row) ? $this->rowArray($row) : null;
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function activeOwned(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $nowUnix,
    ): array {
        $this->assertOperational();
        if ($nowUnix <= 0) {
            $this->invalidState();
        }

        try {
            $rows = $this->connection->table(self::SESSION_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->whereNull('revoked_at_unix')
                ->where('expires_at_unix', '>=', $nowUnix)
                ->orderByDesc('issued_at_unix')
                ->get();

            $result = [];
            foreach ($rows as $row) {
                if (! is_object($row)) {
                    $this->storageFailure();
                }
                $result[] = $this->rowArray($row);
            }
            return $result;
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function touch(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        int $nowUnix,
        int $expiresAtUnix,
    ): void {
        $this->assertOperational();
        $this->assertAuthorityId($authorityId);
        $this->assertTimestampPair($nowUnix, $expiresAtUnix);

        try {
            $this->connection->table(self::SESSION_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->where('authority_id', $authorityId)
                ->whereNull('revoked_at_unix')
                ->where('expires_at_unix', '>=', $nowUnix)
                ->where('last_seen_at_unix', '<=', $nowUnix - 60)
                ->update([
                    'last_seen_at_unix' => $nowUnix,
                    'expires_at_unix' => $expiresAtUnix,
                ]);
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function revokeOne(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $actorAuthorityId,
        string $targetAuthorityId,
        int $nowUnix,
        string $correlationId,
    ): bool {
        $this->assertOperational();
        $this->assertAuthorityId($actorAuthorityId);
        $this->assertAuthorityId($targetAuthorityId);
        $this->assertEventInputs($nowUnix, $correlationId);
        if (hash_equals($actorAuthorityId, $targetAuthorityId)) {
            $this->invalidState();
        }

        try {
            return $this->connection->transaction(function () use (
                $tenantId,
                $identityId,
                $actorAuthorityId,
                $targetAuthorityId,
                $nowUnix,
                $correlationId,
            ): bool {
                $updated = $this->connection->table(self::SESSION_TABLE)
                    ->where('tenant_id', $tenantId->value())
                    ->where('identity_id', $identityId->value())
                    ->where('authority_id', $targetAuthorityId)
                    ->whereNull('revoked_at_unix')
                    ->update(['revoked_at_unix' => $nowUnix]);

                if ($updated === 1) {
                    $this->insertAudit(
                        $tenantId->value(),
                        $identityId->value(),
                        $actorAuthorityId,
                        $targetAuthorityId,
                        'session_revoked',
                        $correlationId,
                        $nowUnix,
                    );
                    return true;
                }
                if ($updated !== 0) {
                    $this->storageFailure();
                }
                return false;
            });
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function revokeOthers(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $actorAuthorityId,
        int $nowUnix,
        string $correlationId,
    ): int {
        $this->assertOperational();
        $this->assertAuthorityId($actorAuthorityId);
        $this->assertEventInputs($nowUnix, $correlationId);

        try {
            return $this->connection->transaction(function () use (
                $tenantId,
                $identityId,
                $actorAuthorityId,
                $nowUnix,
                $correlationId,
            ): int {
                $updated = $this->connection->table(self::SESSION_TABLE)
                    ->where('tenant_id', $tenantId->value())
                    ->where('identity_id', $identityId->value())
                    ->where('authority_id', '!=', $actorAuthorityId)
                    ->whereNull('revoked_at_unix')
                    ->where('expires_at_unix', '>=', $nowUnix)
                    ->update(['revoked_at_unix' => $nowUnix]);

                if (! is_int($updated) || $updated < 0) {
                    $this->storageFailure();
                }

                $this->insertAudit(
                    $tenantId->value(),
                    $identityId->value(),
                    $actorAuthorityId,
                    $actorAuthorityId,
                    'other_sessions_revoked',
                    $correlationId,
                    $nowUnix,
                );
                return $updated;
            });
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function revokeAll(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $actorAuthorityId,
        int $nowUnix,
        string $correlationId,
    ): int {
        $this->assertOperational();
        $this->assertAuthorityId($actorAuthorityId);
        $this->assertEventInputs($nowUnix, $correlationId);

        try {
            return $this->connection->transaction(function () use (
                $tenantId,
                $identityId,
                $actorAuthorityId,
                $nowUnix,
                $correlationId,
            ): int {
                $updated = $this->connection->table(self::SESSION_TABLE)
                    ->where('tenant_id', $tenantId->value())
                    ->where('identity_id', $identityId->value())
                    ->whereNull('revoked_at_unix')
                    ->where('expires_at_unix', '>=', $nowUnix)
                    ->update(['revoked_at_unix' => $nowUnix]);

                if (! is_int($updated) || $updated < 0) {
                    $this->storageFailure();
                }

                if ($updated > 0) {
                    $this->insertAudit(
                        $tenantId->value(),
                        $identityId->value(),
                        $actorAuthorityId,
                        $actorAuthorityId,
                        'all_sessions_revoked',
                        $correlationId,
                        $nowUnix,
                    );
                }

                return $updated;
            });
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    public function revokeCurrent(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        int $nowUnix,
        string $correlationId,
    ): bool {
        $this->assertOperational();
        $this->assertAuthorityId($authorityId);
        $this->assertEventInputs($nowUnix, $correlationId);

        try {
            return $this->connection->transaction(function () use (
                $tenantId,
                $identityId,
                $authorityId,
                $nowUnix,
                $correlationId,
            ): bool {
                $updated = $this->connection->table(self::SESSION_TABLE)
                    ->where('tenant_id', $tenantId->value())
                    ->where('identity_id', $identityId->value())
                    ->where('authority_id', $authorityId)
                    ->whereNull('revoked_at_unix')
                    ->update(['revoked_at_unix' => $nowUnix]);

                if ($updated === 1) {
                    $this->insertAudit(
                        $tenantId->value(),
                        $identityId->value(),
                        $authorityId,
                        $authorityId,
                        'session_logout',
                        $correlationId,
                        $nowUnix,
                    );
                    return true;
                }
                if ($updated !== 0) {
                    $this->storageFailure();
                }
                return false;
            });
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function insertAudit(
        string $tenantId,
        string $identityId,
        ?string $actorAuthorityId,
        string $targetAuthorityId,
        string $eventType,
        string $correlationId,
        int $occurredAtUnix,
    ): void {
        if (! in_array($eventType, ['session_issued', 'session_revoked', 'other_sessions_revoked', 'all_sessions_revoked', 'session_logout'], true)) {
            $this->invalidState();
        }
        $inserted = $this->connection->table(self::AUDIT_TABLE)->insert([
            'tenant_id' => $tenantId,
            'audit_id' => bin2hex(random_bytes(16)),
            'identity_id' => $identityId,
            'actor_authority_id' => $actorAuthorityId,
            'target_authority_id' => $targetAuthorityId,
            'event_type' => $eventType,
            'correlation_id' => $correlationId,
            'occurred_at_unix' => $occurredAtUnix,
        ]);
        if ($inserted !== true) {
            $this->storageFailure();
        }
    }

    /** @return array<string,mixed> */
    private function rowArray(object $row): array
    {
        return [
            'tenant_id' => $row->tenant_id ?? null,
            'authority_id' => $row->authority_id ?? null,
            'public_handle' => $row->public_handle ?? null,
            'identity_id' => $row->identity_id ?? null,
            'organization_id' => $row->organization_id ?? null,
            'outlet_id' => $row->outlet_id ?? null,
            'device_id' => $row->device_id ?? null,
            'credential_epoch' => $row->credential_epoch ?? null,
            'factor_epoch' => $row->factor_epoch ?? null,
            'issued_at_unix' => $row->issued_at_unix ?? null,
            'last_seen_at_unix' => $row->last_seen_at_unix ?? null,
            'expires_at_unix' => $row->expires_at_unix ?? null,
            'revoked_at_unix' => $row->revoked_at_unix ?? null,
        ];
    }

    private function assertOperational(): void
    {
        if (! $this->featureEnabled) {
            throw new FirstPartySessionAuthorityViolation(
                FirstPartySessionAuthorityViolation::FEATURE_DISABLED,
                'First-party session authority feature is disabled.',
            );
        }
        if (! $this->persistenceEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            $this->storageFailure();
        }
    }

    private function assertAuthorityId(string $authorityId): void
    {
        if (preg_match(self::AUTHORITY_PATTERN, $authorityId) !== 1) {
            $this->invalidState();
        }
    }

    private function assertHandle(string $publicHandle): void
    {
        if (preg_match(self::HANDLE_PATTERN, $publicHandle) !== 1) {
            $this->invalidState();
        }
    }

    private function assertTimestampPair(int $nowUnix, int $expiresAtUnix): void
    {
        if ($nowUnix <= 0 || $expiresAtUnix <= $nowUnix) {
            $this->invalidState();
        }
    }

    private function assertEventInputs(int $nowUnix, string $correlationId): void
    {
        if ($nowUnix <= 0) {
            $this->invalidState();
        }
        $this->assertCorrelationId($correlationId);
    }

    private function assertCorrelationId(string $correlationId): void
    {
        if ($correlationId === '' || strlen($correlationId) > 128) {
            $this->invalidState();
        }
    }

    private function invalidState(): never
    {
        throw new FirstPartySessionAuthorityViolation(
            FirstPartySessionAuthorityViolation::INVALID_STATE,
            'First-party session authority state is invalid.',
        );
    }

    private function storageFailure(): never
    {
        throw new FirstPartySessionAuthorityViolation(
            FirstPartySessionAuthorityViolation::STORAGE_FAILURE,
            'First-party session authority storage operation failed.',
        );
    }
}
