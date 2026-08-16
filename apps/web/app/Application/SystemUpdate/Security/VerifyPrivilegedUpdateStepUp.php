<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class VerifyPrivilegedUpdateStepUp
{
    public function __construct(
        private PrivilegedReauthenticationVerifier $reauthentication,
        private PrivilegedTotpVerifier $totp,
        private PrivilegedSecurityAuditSink $audit,
    ) {
    }

    public function verify(
        ?VerifiedPrivilegedPlatformIdentity $identity,
        string $reauthenticationCredential,
        string $totpCode,
        int $now,
    ): PrivilegedStepUpEvidence {
        if ($identity === null) {
            $this->deny(null, $now, 'IDENTITY_REQUIRED');
        }

        try {
            $identityId = PlatformIdentityId::fromString($identity->identityId());
        } catch (InvalidArgumentException) {
            $this->deny(null, $now, 'IDENTITY_INVALID');
        }

        if (! $identity->isPlatformSuperadmin()) {
            $this->deny($identityId->value(), $now, 'PLATFORM_SUPERADMIN_REQUIRED');
        }

        if (! in_array(PrivilegedUpdateCapability::INSTALL, $identity->capabilities(), true)) {
            $this->deny($identityId->value(), $now, 'CAPABILITY_REQUIRED');
        }

        if (preg_match('/\A[a-f0-9]{64}\z/', $identity->sessionBinding()) !== 1) {
            $this->deny($identityId->value(), $now, 'SESSION_BINDING_INVALID');
        }

        if (! PrivilegedUpdateSecurityPolicy::timestampIsFresh(
            $identity->authenticatedAtUnix(),
            $now,
            PrivilegedUpdateSecurityPolicy::SESSION_MAX_AGE_SECONDS,
        )) {
            $this->deny($identityId->value(), $now, 'SESSION_NOT_FRESH');
        }

        if ($reauthenticationCredential === '' || strlen($reauthenticationCredential) > 1024) {
            $this->deny($identityId->value(), $now, 'REAUTH_INPUT_INVALID');
        }

        if (preg_match('/\A[0-9]{'.PrivilegedUpdateSecurityPolicy::TOTP_DIGITS.'}\z/', $totpCode) !== 1) {
            $this->deny($identityId->value(), $now, 'TOTP_INPUT_INVALID');
        }

        if (! $this->reauthentication->verify($identityId, $reauthenticationCredential)) {
            $this->deny($identityId->value(), $now, 'REAUTH_FAILED');
        }

        if (! $this->totp->verify($identityId, $totpCode, $now)) {
            $this->deny($identityId->value(), $now, 'TOTP_FAILED');
        }

        $evidence = PrivilegedStepUpEvidence::issue(
            $identityId->value(),
            $identity->sessionBinding(),
            $now,
            $now,
        );

        $this->recordOrDeny(PrivilegedSecurityAuditEvent::granted('step_up', $identityId, $now));

        return $evidence;
    }

    private function deny(?string $identityId, int $now, string $failureCode): never
    {
        $safeNow = $now > 0 ? $now : 1;

        try {
            $this->audit->record(PrivilegedSecurityAuditEvent::denied(
                'step_up',
                $identityId,
                $safeNow,
                $failureCode,
            ));
        } catch (Throwable) {
            // Audit failure remains fail-closed and is intentionally not reflected to the caller.
        }

        throw new PrivilegedUpdateAuthorizationViolation('Privileged update authorization denied.');
    }

    private function recordOrDeny(PrivilegedSecurityAuditEvent $event): void
    {
        try {
            $this->audit->record($event);
        } catch (Throwable) {
            throw new PrivilegedUpdateAuthorizationViolation('Privileged update authorization denied.');
        }
    }
}
