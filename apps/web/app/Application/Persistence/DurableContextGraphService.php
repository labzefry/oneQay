<?php

namespace App\Application\Persistence;

use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class DurableContextGraphService
{
    public function __construct(
        private RequireVerifiedTenantContext $requireVerifiedTenantContext,
        private DurableContextGraphRepository $repository,
        private PersistenceTransaction $transaction,
    ) {
    }

    public function persist(?VerifiedTenantContext $context, DurableContextGraph $graph): void
    {
        $tenantId = $this->tenantIdFromVerifiedContext($context);

        if (! $tenantId->equals($graph->tenantId)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::TENANT_CONTEXT_MISMATCH,
                'Durable persistence tenant context mismatch.',
            );
        }

        $this->transaction->run(function () use ($graph): void {
            $this->repository->persist($graph);
        });
    }

    public function findForVerifiedTenant(
        ?VerifiedTenantContext $context,
        PlatformIdentityId $identityId,
        DeviceId $deviceId,
    ): ?DurableContextGraph {
        $tenantId = $this->tenantIdFromVerifiedContext($context);

        return $this->repository->findForTenant($tenantId, $identityId, $deviceId);
    }

    private function tenantIdFromVerifiedContext(?VerifiedTenantContext $context): TenantId
    {
        try {
            $verified = $this->requireVerifiedTenantContext->require($context);
        } catch (MissingTenantContext) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::VERIFIED_TENANT_REQUIRED,
                'Verified tenant context is required for durable persistence.',
            );
        }

        return TenantId::fromString($verified->tenantId());
    }
}
