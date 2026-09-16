<?php

declare(strict_types=1);

namespace App\Infrastructure\Bootstrap;

use App\Application\Bootstrap\MerchantContextBootstrapStateRepository;
use App\Application\Bootstrap\MerchantContextBootstrapViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelMerchantContextBootstrapStateRepository implements MerchantContextBootstrapStateRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {}

    public function assertFresh(TenantId $tenantId): void
    {
        $this->assertRuntimeAllowed();

        try {
            if ($this->connection->table('oneqay_tenants')
                ->where('id', $tenantId->value())
                ->exists()) {
                throw new MerchantContextBootstrapViolation(
                    MerchantContextBootstrapViolation::TENANT_ALREADY_EXISTS,
                    'Merchant context bootstrap requires a fresh tenant.',
                );
            }
        } catch (MerchantContextBootstrapViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::STORAGE_FAILURE,
                'Merchant context bootstrap state read failed.',
            );
        }
    }

    private function assertRuntimeAllowed(): void
    {
        if (! $this->enabled) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::PERSISTENCE_DISABLED,
                'Durable persistence is disabled.',
            );
        }

        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RUNTIME_DENIED,
                'Merchant context bootstrap runtime is not authorized.',
            );
        }
    }
}
