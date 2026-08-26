<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstPartyIdentityEligibilityVerifier implements FirstPartyIdentityEligibilityVerifier
{
    private const IDENTITY_TABLE = 'oneqay_identities';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $sessionControlEnabled,
    ) {}

    public function isEligible(TenantId $tenantId, PlatformIdentityId $identityId): bool
    {
        if (! $this->operational()) {
            return false;
        }

        try {
            $rows = $this->connection->table(self::IDENTITY_TABLE)
                ->where('tenant_id', $tenantId->value())
                ->where('id', $identityId->value())
                ->limit(2)
                ->get(['first_party_authentication_enabled']);

            if ($rows->count() !== 1) {
                return false;
            }

            $row = $rows->first();
            if (! is_object($row)) {
                return false;
            }

            return $this->canonicalEnabled($row->first_party_authentication_enabled ?? null);
        } catch (Throwable) {
            return false;
        }
    }

    private function operational(): bool
    {
        return $this->persistenceEnabled
            && $this->sessionControlEnabled
            && in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true);
    }

    private function canonicalEnabled(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
}
