<?php

declare(strict_types=1);

namespace App\Application\Bootstrap;

use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapRepository;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Application\Persistence\DurableContextGraph;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use Throwable;

// Author by Lab | zefry
final readonly class MerchantContextBootstrapService
{
    public const OUTCOME_APPLIED = 'applied';

    public function __construct(
        private MerchantContextBootstrapAuthority $authority,
        private MerchantContextBootstrapStateRepository $stateRepository,
        private DurableContextGraphRepository $graphRepository,
        private InitialTenantAdministratorProvisioningRepository $administratorRepository,
        private FirstControlPrincipalCredentialBootstrapService $credentialBootstrap,
        private PersistenceTransaction $transaction,
        private PolicyAdministrationClock $clock,
    ) {}

    public function bootstrap(
        DurableContextGraph $graph,
        InitialTenantAdministratorProvisioningId $provisioningId,
        #[\SensitiveParameter] string $password,
    ): string {
        if (! $this->authority->authorizesBootstrap($graph, $provisioningId)) {
            $this->fail(
                MerchantContextBootstrapViolation::AUTHORIZATION_DENIED,
                'Merchant context bootstrap authorization denied.',
            );
        }

        $passwordBytes = strlen($password);
        if ($passwordBytes < FirstControlPrincipalCredentialBootstrapService::MIN_PASSWORD_BYTES
            || $passwordBytes > FirstControlPrincipalCredentialBootstrapService::MAX_PASSWORD_BYTES) {
            $this->fail(
                MerchantContextBootstrapViolation::INVALID_PASSWORD,
                'Merchant context bootstrap password does not satisfy the bounded length policy.',
            );
        }

        try {
            $this->stateRepository->assertFresh($graph->tenantId);
        } catch (MerchantContextBootstrapViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation $exception) {
            $this->mapPersistenceFailure($exception);
        } catch (Throwable) {
            $this->fail(
                MerchantContextBootstrapViolation::STORAGE_FAILURE,
                'Merchant context bootstrap preflight failed.',
            );
        }

        try {
            $outcome = $this->transaction->run(function () use ($graph, $provisioningId, $password): string {
                if (! $this->authority->authorizesBootstrap($graph, $provisioningId)) {
                    throw new MerchantContextBootstrapViolation(
                        MerchantContextBootstrapViolation::AUTHORIZATION_DENIED,
                        'Merchant context bootstrap authorization denied.',
                    );
                }

                $this->stateRepository->assertFresh($graph->tenantId);
                $this->graphRepository->persist($graph);

                $this->administratorRepository->assertTargetEligible($graph->tenantId, $graph->identityId);
                if ($this->administratorRepository->replayOutcome(
                    $graph->tenantId,
                    $graph->identityId,
                    $provisioningId,
                ) !== null) {
                    throw new MerchantContextBootstrapViolation(
                        MerchantContextBootstrapViolation::INVALID_BOOTSTRAP,
                        'Merchant context bootstrap encountered pre-existing administrator provisioning state.',
                    );
                }
                $this->administratorRepository->assertUninitialized($graph->tenantId);

                $occurredAtUnix = $this->clock->nowUnix();
                if ($occurredAtUnix <= 0) {
                    throw new MerchantContextBootstrapViolation(
                        MerchantContextBootstrapViolation::INVALID_BOOTSTRAP,
                        'Merchant context bootstrap clock returned an invalid timestamp.',
                    );
                }

                $administratorOutcome = $this->administratorRepository->applyFresh(
                    $this->authority,
                    $graph->tenantId,
                    $graph->identityId,
                    $provisioningId,
                    $occurredAtUnix,
                );
                if ($administratorOutcome !== InitialTenantAdministratorProvisioningRepository::OUTCOME_APPLIED) {
                    throw new MerchantContextBootstrapViolation(
                        MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
                        'Merchant context bootstrap administrator provisioning returned an invalid outcome.',
                    );
                }

                $credentialOutcome = $this->credentialBootstrap->bootstrap($graph->tenantId, $password);
                if ($credentialOutcome !== FirstControlPrincipalCredentialBootstrapRepository::OUTCOME_APPLIED) {
                    throw new MerchantContextBootstrapViolation(
                        MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
                        'Merchant context bootstrap credential provisioning returned an invalid outcome.',
                    );
                }

                return self::OUTCOME_APPLIED;
            });
        } catch (MerchantContextBootstrapViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation $exception) {
            $this->mapPersistenceFailure($exception);
        } catch (Throwable) {
            $this->fail(
                MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
                'Merchant context bootstrap transaction failed.',
            );
        }

        if ($outcome !== self::OUTCOME_APPLIED) {
            $this->fail(
                MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
                'Merchant context bootstrap returned an invalid outcome.',
            );
        }

        return $outcome;
    }

    private function mapPersistenceFailure(DurablePersistenceViolation $exception): never
    {
        $code = match ($exception->errorCode) {
            DurablePersistenceViolation::PERSISTENCE_DISABLED => MerchantContextBootstrapViolation::PERSISTENCE_DISABLED,
            DurablePersistenceViolation::RUNTIME_DENIED => MerchantContextBootstrapViolation::RUNTIME_DENIED,
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT => MerchantContextBootstrapViolation::RELATIONSHIP_CONFLICT,
            DurablePersistenceViolation::STORAGE_FAILURE => MerchantContextBootstrapViolation::STORAGE_FAILURE,
            default => MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
        };

        $this->fail($code, 'Merchant context bootstrap persistence operation failed.');
    }

    private function fail(string $code, string $message): never
    {
        throw new MerchantContextBootstrapViolation($code, $message);
    }
}
