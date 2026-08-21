<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class AuthenticatedPasswordChangeService
{
    private const MIN_NEW_PASSWORD_BYTES = 12;
    private const MAX_PASSWORD_BYTES = 4096;
    private const TOTP_PATTERN = '/\A[0-9]{6}\z/D';

    public function __construct(
        private AuthenticatedPasswordChangeRepository $repository,
        private VerifyFirstPartyIdentityCredential $credentials,
        private VerifyFirstPartyCredentialEpoch $credentialEpochs,
        private PrivilegedTotpMfaService $mfa,
        private PersistenceTransaction $transaction,
        private AuthenticatedPasswordChangeClock $clock,
        private bool $mfaFeatureEnabled,
    ) {}

    public function change(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $sessionCredentialEpoch,
        #[\SensitiveParameter] string $currentPassword,
        #[\SensitiveParameter] string $newPassword,
        #[\SensitiveParameter] ?string $totpCode,
    ): void {
        if ($sessionCredentialEpoch < 0
            || $currentPassword === ''
            || strlen($currentPassword) > self::MAX_PASSWORD_BYTES
            || strlen($newPassword) < self::MIN_NEW_PASSWORD_BYTES
            || strlen($newPassword) > self::MAX_PASSWORD_BYTES) {
            $this->failed();
        }

        try {
            $this->credentialEpochs->assertCurrent($tenantId, $identityId, $sessionCredentialEpoch);

            if (! $this->credentials->verify($tenantId, $identityId, $currentPassword)) {
                $this->failed();
            }

            if (! $this->mfaFeatureEnabled) {
                if ($totpCode !== null) {
                    $this->failed();
                }
            } else {
                $requiredState = $this->mfa->requiredState($tenantId, $identityId);
                if ($requiredState === null) {
                    if ($totpCode !== null) {
                        $this->failed();
                    }
                } else {
                    if (! $requiredState->is(PrivilegedTotpMfaState::CONFIRMED)
                        || $totpCode === null
                        || preg_match(self::TOTP_PATTERN, $totpCode) !== 1) {
                        $this->failed();
                    }

                    $this->mfa->challenge($tenantId, $identityId, $totpCode);
                }
            }

            $now = $this->clock->nowUnix();
            if ($now <= 0) {
                $this->failed();
            }

            $this->transaction->run(function () use (
                $tenantId,
                $identityId,
                $sessionCredentialEpoch,
                $currentPassword,
                $newPassword,
                $now,
            ): void {
                $this->repository->change(
                    $tenantId,
                    $identityId,
                    $sessionCredentialEpoch,
                    $currentPassword,
                    $newPassword,
                    $now,
                );
            });
        } catch (
            AuthenticatedPasswordChangeViolation
            | DurablePersistenceViolation
            | IdentityContextViolation
            | PrivilegedTotpMfaViolation
            | InvalidArgumentException
        ) {
            $this->failed();
        }
    }

    private function failed(): never
    {
        throw new AuthenticatedPasswordChangeViolation(
            AuthenticatedPasswordChangeViolation::CHANGE_FAILED,
            'Password change request failed.',
        );
    }
}
