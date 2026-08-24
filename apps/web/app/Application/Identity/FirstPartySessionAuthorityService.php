<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Throwable;

// Author by Lab | zefry
final readonly class FirstPartySessionAuthorityService
{
    private const TOUCH_INTERVAL_SECONDS = 60;
    private const HANDLE_PATTERN = '/\A[A-Za-z0-9_-]{43}\z/D';
    private const AUTHORITY_PATTERN = '/\A[0-9a-f]{32}\z/D';

    public function __construct(
        private FirstPartySessionAuthorityRepository $repository,
        private FirstPartySessionAuthorityClock $clock,
        private FirstPartyCredentialEpochRepository $credentialEpochs,
        private PrivilegedTotpFactorEpochRepository $factorEpochs,
        private PrivilegedTotpMfaService $mfa,
        private bool $mfaEnabled,
        private int $idleTtlSeconds,
        private int $absoluteTtlSeconds = 43200,
    ) {}

    public function issue(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        int $credentialEpoch,
        ?int $factorEpoch,
        string $correlationId,
    ): IssuedFirstPartySessionAuthority {
        $this->assertConfigured();
        $now = $this->now();
        $this->assertCurrentEpochs($tenantId, $identityId, $credentialEpoch, $factorEpoch);
        $this->assertContextValue($organizationId);
        if ($outletId !== null) {
            $this->assertContextValue($outletId);
        }
        if ($deviceId !== null) {
            $this->assertContextValue($deviceId);
        }
        $this->assertCorrelationId($correlationId);

        $authorityId = bin2hex(random_bytes(16));
        $publicHandle = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        if (preg_match(self::AUTHORITY_PATTERN, $authorityId) !== 1
            || preg_match(self::HANDLE_PATTERN, $publicHandle) !== 1) {
            $this->invalidState();
        }

        return $this->repository->issue(
            $tenantId,
            $identityId,
            $organizationId,
            $outletId,
            $deviceId,
            $credentialEpoch,
            $factorEpoch,
            $authorityId,
            $publicHandle,
            $now,
            $this->effectiveExpiry($now, $now),
            $correlationId,
        );
    }

    public function assertActiveCurrent(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        mixed $sessionCredentialEpoch,
        mixed $sessionFactorEpoch,
    ): void {
        $this->assertConfigured();
        $this->assertAuthorityId($authorityId);
        $record = $this->repository->ownedByAuthorityId($tenantId, $identityId, $authorityId);
        if ($record === null) {
            $this->authorityDenied();
        }

        $now = $this->now();
        $this->assertRecordActiveAndFresh(
            $record,
            $tenantId,
            $identityId,
            $authorityId,
            $organizationId,
            $outletId,
            $deviceId,
            $sessionCredentialEpoch,
            $sessionFactorEpoch,
            $now,
        );

        $lastSeen = $this->recordInt($record, 'last_seen_at_unix');
        if (($now - $lastSeen) >= self::TOUCH_INTERVAL_SECONDS) {
            $effectiveExpiry = $this->effectiveExpiry($this->recordInt($record, 'issued_at_unix'), $now);
            if ($effectiveExpiry > $now) {
                $this->repository->touch(
                    $tenantId,
                    $identityId,
                    $authorityId,
                    $now,
                    $effectiveExpiry,
                );
            }
        }
    }

    /** @return list<FirstPartySessionInventoryItem> */
    public function inventory(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $currentAuthorityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        mixed $sessionCredentialEpoch,
        mixed $sessionFactorEpoch,
    ): array {
        $this->assertActiveCurrent(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $organizationId,
            $outletId,
            $deviceId,
            $sessionCredentialEpoch,
            $sessionFactorEpoch,
        );

        $now = $this->now();
        $items = [];
        foreach ($this->repository->activeOwned($tenantId, $identityId, $now) as $record) {
            try {
                $this->assertRecordFreshForOwner($record, $tenantId, $identityId, $now);
            } catch (FirstPartySessionAuthorityViolation) {
                continue;
            }

            $issuedAtUnix = $this->recordInt($record, 'issued_at_unix');
            $items[] = new FirstPartySessionInventoryItem(
                $this->recordString($record, 'public_handle'),
                hash_equals($currentAuthorityId, $this->recordString($record, 'authority_id')),
                $this->recordString($record, 'organization_id'),
                $this->recordNullableString($record, 'outlet_id'),
                $this->recordNullableString($record, 'device_id'),
                $issuedAtUnix,
                $this->recordInt($record, 'last_seen_at_unix'),
                min(
                    $this->recordInt($record, 'expires_at_unix'),
                    $this->absoluteDeadline($issuedAtUnix),
                ),
            );
        }

        usort($items, static fn (FirstPartySessionInventoryItem $left, FirstPartySessionInventoryItem $right): int =>
            $right->issuedAtUnix <=> $left->issuedAtUnix
        );

        return $items;
    }

    public function revokeOne(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $currentAuthorityId,
        string $publicHandle,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        mixed $sessionCredentialEpoch,
        mixed $sessionFactorEpoch,
        string $correlationId,
    ): void {
        $this->assertActiveCurrent(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $organizationId,
            $outletId,
            $deviceId,
            $sessionCredentialEpoch,
            $sessionFactorEpoch,
        );
        $this->assertCorrelationId($correlationId);
        if (preg_match(self::HANDLE_PATTERN, $publicHandle) !== 1) {
            return;
        }

        $record = $this->repository->ownedByPublicHandle($tenantId, $identityId, $publicHandle);
        if ($record === null) {
            return;
        }

        $targetAuthorityId = $this->recordString($record, 'authority_id');
        if (hash_equals($currentAuthorityId, $targetAuthorityId)) {
            throw new FirstPartySessionAuthorityViolation(
                FirstPartySessionAuthorityViolation::CURRENT_SESSION_TARGET,
                'Current first-party session must use canonical logout.',
            );
        }

        try {
            $this->assertRecordFreshForOwner($record, $tenantId, $identityId, $this->now());
        } catch (FirstPartySessionAuthorityViolation) {
            return;
        }

        $this->repository->revokeOne(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $targetAuthorityId,
            $this->now(),
            $correlationId,
        );
    }

    public function revokeOthers(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $currentAuthorityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        mixed $sessionCredentialEpoch,
        mixed $sessionFactorEpoch,
        string $correlationId,
    ): int {
        $this->assertActiveCurrent(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $organizationId,
            $outletId,
            $deviceId,
            $sessionCredentialEpoch,
            $sessionFactorEpoch,
        );
        $this->assertCorrelationId($correlationId);

        return $this->repository->revokeOthers(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $this->now(),
            $correlationId,
        );
    }

    public function revokeAll(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $currentAuthorityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        mixed $sessionCredentialEpoch,
        mixed $sessionFactorEpoch,
        string $correlationId,
    ): int {
        $this->assertActiveCurrent(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $organizationId,
            $outletId,
            $deviceId,
            $sessionCredentialEpoch,
            $sessionFactorEpoch,
        );
        $this->assertCorrelationId($correlationId);

        return $this->repository->revokeAll(
            $tenantId,
            $identityId,
            $currentAuthorityId,
            $this->now(),
            $correlationId,
        );
    }

    public function logoutCurrent(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        string $correlationId,
    ): void {
        $this->assertConfigured();
        if (preg_match(self::AUTHORITY_PATTERN, $authorityId) !== 1) {
            return;
        }
        $this->assertCorrelationId($correlationId);
        $this->repository->revokeCurrent(
            $tenantId,
            $identityId,
            $authorityId,
            $this->now(),
            $correlationId,
        );
    }

    /** @param array<string,mixed> $record */
    private function assertRecordActiveAndFresh(
        array $record,
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        mixed $sessionCredentialEpoch,
        mixed $sessionFactorEpoch,
        int $now,
    ): void {
        if (! hash_equals($authorityId, $this->recordString($record, 'authority_id'))
            || ! hash_equals($organizationId, $this->recordString($record, 'organization_id'))
            || $outletId !== $this->recordNullableString($record, 'outlet_id')
            || $deviceId !== $this->recordNullableString($record, 'device_id')) {
            $this->authorityDenied();
        }

        $credentialEpoch = $this->recordInt($record, 'credential_epoch');
        $factorEpoch = $this->recordNullableInt($record, 'factor_epoch');
        if (! is_int($sessionCredentialEpoch) || $sessionCredentialEpoch !== $credentialEpoch
            || ($factorEpoch === null ? $sessionFactorEpoch !== null : ! is_int($sessionFactorEpoch) || $sessionFactorEpoch !== $factorEpoch)) {
            $this->authorityDenied();
        }

        $this->assertRecordFreshForOwner($record, $tenantId, $identityId, $now);
    }

    /** @param array<string,mixed> $record */
    private function assertRecordFreshForOwner(
        array $record,
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $now,
    ): void {
        $issuedAtUnix = $this->recordInt($record, 'issued_at_unix');
        if (($record['revoked_at_unix'] ?? null) !== null
            || $now < $issuedAtUnix
            || $now > $this->recordInt($record, 'expires_at_unix')
            || $now > $this->absoluteDeadline($issuedAtUnix)) {
            $this->authorityDenied();
        }

        $credentialEpoch = $this->recordInt($record, 'credential_epoch');
        $factorEpoch = $this->recordNullableInt($record, 'factor_epoch');
        $this->assertCurrentEpochs($tenantId, $identityId, $credentialEpoch, $factorEpoch);
    }

    private function assertCurrentEpochs(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $credentialEpoch,
        ?int $factorEpoch,
    ): void {
        if ($credentialEpoch < 0 || ($factorEpoch !== null && $factorEpoch < 0)) {
            $this->invalidState();
        }

        try {
            if ($this->credentialEpochs->current($tenantId, $identityId) !== $credentialEpoch) {
                $this->authorityDenied();
            }

            if (! $this->mfaEnabled) {
                if ($factorEpoch !== null) {
                    $this->authorityDenied();
                }
                return;
            }

            $state = $this->mfa->requiredState($tenantId, $identityId);
            if ($state === null) {
                if ($factorEpoch !== null) {
                    $this->authorityDenied();
                }
                return;
            }

            if (! $state->is(PrivilegedTotpMfaState::CONFIRMED)
                || $factorEpoch === null
                || $this->factorEpochs->currentEpoch($tenantId, $identityId) !== $factorEpoch) {
                $this->authorityDenied();
            }
        } catch (FirstPartySessionAuthorityViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->authorityDenied();
        }
    }

    private function assertConfigured(): void
    {
        if ($this->idleTtlSeconds !== 7200 || $this->absoluteTtlSeconds !== 43200) {
            throw new FirstPartySessionAuthorityViolation(
                FirstPartySessionAuthorityViolation::FEATURE_DISABLED,
                'First-party session authority feature is not configured.',
            );
        }
    }

    private function effectiveExpiry(int $issuedAtUnix, int $nowUnix): int
    {
        if ($issuedAtUnix <= 0 || $nowUnix < $issuedAtUnix) {
            $this->invalidState();
        }

        return min(
            $this->deadline($nowUnix, $this->idleTtlSeconds),
            $this->absoluteDeadline($issuedAtUnix),
        );
    }

    private function absoluteDeadline(int $issuedAtUnix): int
    {
        return $this->deadline($issuedAtUnix, $this->absoluteTtlSeconds);
    }

    private function deadline(int $originUnix, int $ttlSeconds): int
    {
        if ($originUnix <= 0 || $ttlSeconds <= 0 || $originUnix > PHP_INT_MAX - $ttlSeconds) {
            $this->invalidState();
        }

        return $originUnix + $ttlSeconds;
    }

    private function now(): int
    {
        $now = $this->clock->nowUnix();
        if ($now <= 0) {
            $this->invalidState();
        }
        return $now;
    }

    private function assertAuthorityId(string $authorityId): void
    {
        if (preg_match(self::AUTHORITY_PATTERN, $authorityId) !== 1) {
            $this->authorityDenied();
        }
    }

    private function assertCorrelationId(string $correlationId): void
    {
        if ($correlationId === '' || strlen($correlationId) > 128) {
            $this->invalidState();
        }
    }

    private function assertContextValue(string $value): void
    {
        if ($value === '' || strlen($value) > 64 || trim($value) !== $value) {
            $this->invalidState();
        }
    }

    /** @param array<string,mixed> $record */
    private function recordString(array $record, string $key): string
    {
        $value = $record[$key] ?? null;
        if (! is_string($value) || $value === '') {
            $this->invalidState();
        }
        return $value;
    }

    /** @param array<string,mixed> $record */
    private function recordNullableString(array $record, string $key): ?string
    {
        $value = $record[$key] ?? null;
        if ($value === null) {
            return null;
        }
        if (! is_string($value) || $value === '') {
            $this->invalidState();
        }
        return $value;
    }

    /** @param array<string,mixed> $record */
    private function recordInt(array $record, string $key): int
    {
        $value = $record[$key] ?? null;
        if (is_int($value) && $value >= 0) {
            return $value;
        }
        if (is_string($value) && preg_match('/\A(?:0|[1-9][0-9]*)\z/D', $value) === 1 && strlen($value) <= strlen((string) PHP_INT_MAX)) {
            $integer = (int) $value;
            if ((string) $integer === $value) {
                return $integer;
            }
        }
        $this->invalidState();
    }

    /** @param array<string,mixed> $record */
    private function recordNullableInt(array $record, string $key): ?int
    {
        if (! array_key_exists($key, $record) || $record[$key] === null) {
            return null;
        }
        return $this->recordInt($record, $key);
    }

    private function authorityDenied(): never
    {
        throw new FirstPartySessionAuthorityViolation(
            FirstPartySessionAuthorityViolation::AUTHORITY_DENIED,
            'First-party session authority denied.',
        );
    }

    private function invalidState(): never
    {
        throw new FirstPartySessionAuthorityViolation(
            FirstPartySessionAuthorityViolation::INVALID_STATE,
            'First-party session authority state is invalid.',
        );
    }
}
