<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class FirstControlPrincipalCredentialBootstrapService
{
    public const MIN_PASSWORD_BYTES = 12;
    public const MAX_PASSWORD_BYTES = 4096;

    public function __construct(
        private FirstControlPrincipalCredentialBootstrapRepository $repository,
        private PersistenceTransaction $transaction,
    ) {}

    public function bootstrap(
        TenantId $tenantId,
        #[\SensitiveParameter] string $password,
    ): string {
        $passwordBytes = strlen($password);
        if ($passwordBytes < self::MIN_PASSWORD_BYTES || $passwordBytes > self::MAX_PASSWORD_BYTES) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::INVALID_PASSWORD,
                'First control principal bootstrap password does not satisfy the bounded length policy.',
            );
        }

        try {
            // Deterministic, read-only denial reasons stay outside the historical
            // PersistenceTransaction callback. The repository repeats every
            // critical eligibility check inside the transaction before insert.
            $this->repository->assertEligible($tenantId);
        } catch (FirstControlPrincipalCredentialBootstrapViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation $exception) {
            $this->mapPersistenceFailure($exception);
        }

        try {
            $outcome = $this->transaction->run(
                fn (): string => $this->repository->bootstrapFresh($tenantId, $password),
            );
        } catch (FirstControlPrincipalCredentialBootstrapViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation $exception) {
            $this->mapPersistenceFailure($exception);
        }

        if ($outcome !== FirstControlPrincipalCredentialBootstrapRepository::OUTCOME_APPLIED) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::TRANSACTION_FAILURE,
                'First control principal bootstrap transaction returned an invalid outcome.',
            );
        }

        return $outcome;
    }

    private function mapPersistenceFailure(DurablePersistenceViolation $exception): never
    {
        $code = match ($exception->errorCode) {
            DurablePersistenceViolation::PERSISTENCE_DISABLED => FirstControlPrincipalCredentialBootstrapViolation::PERSISTENCE_DISABLED,
            DurablePersistenceViolation::RUNTIME_DENIED => FirstControlPrincipalCredentialBootstrapViolation::RUNTIME_DENIED,
            DurablePersistenceViolation::STORAGE_FAILURE => FirstControlPrincipalCredentialBootstrapViolation::STORAGE_FAILURE,
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT => FirstControlPrincipalCredentialBootstrapViolation::BOOTSTRAP_INELIGIBLE,
            default => FirstControlPrincipalCredentialBootstrapViolation::TRANSACTION_FAILURE,
        };

        $this->fail($code, 'First control principal bootstrap persistence transaction failed.');
    }

    private function fail(string $code, string $message): never
    {
        throw new FirstControlPrincipalCredentialBootstrapViolation($code, $message);
    }
}
