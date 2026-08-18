<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class InitialTenantAdministratorProvisioningService
{
    public function __construct(
        private InitialTenantAdministratorProvisioningAuthority $authority,
        private InitialTenantAdministratorProvisioningRepository $repository,
        private PersistenceTransaction $transaction,
        private PolicyAdministrationClock $clock,
    ) {}

    public function provision(
        ?VerifiedPlatformIdentity $verifiedIdentity,
        TenantId $tenantId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): string {
        if ($verifiedIdentity === null) {
            $this->fail(
                InitialTenantAdministratorProvisioningViolation::AUTHORIZATION_DENIED,
                'Initial tenant administrator provisioning authorization denied.',
            );
        }

        try {
            $identityId = PlatformIdentityId::fromString($verifiedIdentity->identityId());
        } catch (InvalidArgumentException) {
            $this->fail(
                InitialTenantAdministratorProvisioningViolation::IDENTITY_MISMATCH,
                'Verified platform identity is invalid for initial provisioning.',
            );
        }

        if (! $this->authority->authorizes($tenantId, $identityId, $provisioningId)) {
            $this->fail(
                InitialTenantAdministratorProvisioningViolation::AUTHORIZATION_DENIED,
                'Initial tenant administrator provisioning authorization denied.',
            );
        }

        $this->repository->assertTargetEligible($tenantId, $identityId);

        $prior = $this->repository->replayOutcome($tenantId, $identityId, $provisioningId);
        if ($prior !== null) {
            return $prior;
        }

        $this->repository->assertUninitialized($tenantId);

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            $this->fail(
                InitialTenantAdministratorProvisioningViolation::INVALID_PROVISIONING,
                'Initial tenant administrator provisioning clock returned an invalid timestamp.',
            );
        }

        try {
            return $this->transaction->run(
                fn (): string => $this->repository->applyFresh(
                    $this->authority,
                    $tenantId,
                    $identityId,
                    $provisioningId,
                    $occurredAtUnix,
                ),
            );
        } catch (DurablePersistenceViolation $exception) {
            if (! $this->authority->authorizes($tenantId, $identityId, $provisioningId)) {
                $this->fail(
                    InitialTenantAdministratorProvisioningViolation::AUTHORIZATION_DENIED,
                    'Initial tenant administrator provisioning authorization denied.',
                );
            }

            try {
                $this->repository->assertTargetEligible($tenantId, $identityId);
                $prior = $this->repository->replayOutcome($tenantId, $identityId, $provisioningId);
                if ($prior !== null) {
                    return $prior;
                }
                $this->repository->assertUninitialized($tenantId);
            } catch (InitialTenantAdministratorProvisioningViolation $classified) {
                throw $classified;
            }

            $code = match ($exception->errorCode) {
                DurablePersistenceViolation::PERSISTENCE_DISABLED => InitialTenantAdministratorProvisioningViolation::PERSISTENCE_DISABLED,
                DurablePersistenceViolation::RUNTIME_DENIED => InitialTenantAdministratorProvisioningViolation::RUNTIME_DENIED,
                DurablePersistenceViolation::STORAGE_FAILURE => InitialTenantAdministratorProvisioningViolation::STORAGE_FAILURE,
                default => InitialTenantAdministratorProvisioningViolation::TRANSACTION_FAILURE,
            };

            $this->fail($code, 'Initial tenant administrator provisioning transaction failed.');
        }
    }

    private function fail(string $code, string $message): never
    {
        throw new InitialTenantAdministratorProvisioningViolation($code, $message);
    }
}
