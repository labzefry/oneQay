<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstPartyIdentityCredentialVerifier implements FirstPartyIdentityCredentialVerifier
{
    private const DUMMY_HASH = '$2b$12$5q8JY/CJPvyG1QsCq9t0PuzOzLiVl8UvgrZOR5Su4cdsz3QTiNs8O';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
    ) {}

    public function verify(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $password,
    ): bool {
        if (! $this->storageAllowed()) {
            password_verify($password, self::DUMMY_HASH);
            return false;
        }

        $storedHash = null;
        try {
            $value = $this->connection
                ->table('oneqay_identity_password_credentials')
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->value('password_hash');

            if (is_string($value) && $value !== '') {
                $storedHash = $value;
            }
        } catch (Throwable) {
            $storedHash = null;
        }

        $matches = password_verify($password, $storedHash ?? self::DUMMY_HASH);

        return $storedHash !== null && $matches;
    }

    private function storageAllowed(): bool
    {
        return $this->persistenceEnabled
            && in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true);
    }
}
