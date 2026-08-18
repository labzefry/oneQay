<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class InitialPasswordEnrollmentService
{
    public const TOKEN_TTL_SECONDS = 900;
    public const MIN_PASSWORD_BYTES = 12;
    public const MAX_PASSWORD_BYTES = 4096;

    public function __construct(
        private InitialPasswordEnrollmentRepository $repository,
        private PersistenceTransaction $transaction,
        private PolicyAdministrationClock $clock,
    ) {}

    public function issue(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        InitialPasswordEnrollmentId $enrollmentId,
    ): IssuedInitialPasswordEnrollment {
        if ($actor->identityId()->equals($targetIdentityId)) {
            $this->fail(
                InitialPasswordEnrollmentViolation::SELF_ENROLLMENT_DENIED,
                'Initial password enrollment self-issuance is denied.',
            );
        }

        $issuedAtUnix = $this->clock->nowUnix();
        if ($issuedAtUnix <= 0) {
            $this->fail(
                InitialPasswordEnrollmentViolation::TRANSACTION_FAILURE,
                'Initial password enrollment clock returned an invalid timestamp.',
            );
        }

        $expiresAtUnix = $issuedAtUnix + self::TOKEN_TTL_SECONDS;

        try {
            $issued = $this->transaction->run(
                fn (): IssuedInitialPasswordEnrollment => $this->repository->issueFresh(
                    $actor,
                    $targetIdentityId,
                    $enrollmentId,
                    $issuedAtUnix,
                    $expiresAtUnix,
                ),
            );
        } catch (InitialPasswordEnrollmentViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation $exception) {
            $this->mapPersistenceFailure($exception);
        }

        if (! $issued instanceof IssuedInitialPasswordEnrollment) {
            $this->fail(
                InitialPasswordEnrollmentViolation::TRANSACTION_FAILURE,
                'Initial password enrollment transaction returned an invalid result.',
            );
        }

        return $issued;
    }

    public function redeem(
        TenantId $tenantId,
        PlatformIdentityId $targetIdentityId,
        InitialPasswordEnrollmentId $enrollmentId,
        #[\SensitiveParameter] string $enrollmentToken,
        #[\SensitiveParameter] string $password,
    ): string {
        if (preg_match('/\A[A-Za-z0-9_-]{43}\z/', $enrollmentToken) !== 1) {
            $this->fail(
                InitialPasswordEnrollmentViolation::INVALID_ENROLLMENT,
                'Initial password enrollment token is invalid.',
            );
        }

        $passwordBytes = strlen($password);
        if ($passwordBytes < self::MIN_PASSWORD_BYTES || $passwordBytes > self::MAX_PASSWORD_BYTES) {
            $this->fail(
                InitialPasswordEnrollmentViolation::INVALID_PASSWORD,
                'Initial password enrollment password does not satisfy the bounded length policy.',
            );
        }

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            $this->fail(
                InitialPasswordEnrollmentViolation::TRANSACTION_FAILURE,
                'Initial password enrollment clock returned an invalid timestamp.',
            );
        }

        try {
            $outcome = $this->transaction->run(
                fn (): string => $this->repository->redeem(
                    $tenantId,
                    $targetIdentityId,
                    $enrollmentId,
                    $enrollmentToken,
                    $password,
                    $occurredAtUnix,
                ),
            );
        } catch (InitialPasswordEnrollmentViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation $exception) {
            $this->mapPersistenceFailure($exception);
        }

        if ($outcome !== InitialPasswordEnrollmentRepository::OUTCOME_APPLIED) {
            $this->fail(
                InitialPasswordEnrollmentViolation::TRANSACTION_FAILURE,
                'Initial password enrollment transaction returned an invalid outcome.',
            );
        }

        return $outcome;
    }

    private function mapPersistenceFailure(DurablePersistenceViolation $exception): never
    {
        $code = match ($exception->errorCode) {
            DurablePersistenceViolation::PERSISTENCE_DISABLED => InitialPasswordEnrollmentViolation::PERSISTENCE_DISABLED,
            DurablePersistenceViolation::RUNTIME_DENIED => InitialPasswordEnrollmentViolation::RUNTIME_DENIED,
            DurablePersistenceViolation::STORAGE_FAILURE => InitialPasswordEnrollmentViolation::STORAGE_FAILURE,
            default => InitialPasswordEnrollmentViolation::TRANSACTION_FAILURE,
        };

        $this->fail($code, 'Initial password enrollment persistence transaction failed.');
    }

    private function fail(string $code, string $message): never
    {
        throw new InitialPasswordEnrollmentViolation($code, $message);
    }
}
