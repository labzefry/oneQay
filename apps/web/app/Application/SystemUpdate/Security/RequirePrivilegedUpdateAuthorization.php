<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class RequirePrivilegedUpdateAuthorization
{
    public function __construct(private PrivilegedSecurityAuditSink $audit)
    {
    }

    public function require(
        ?VerifiedPrivilegedPlatformIdentity $identity,
        ?PrivilegedStepUpEvidence $evidence,
        int $now,
    ): PrivilegedUpdateAuthorization {
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

        if ($evidence === null) {
            $this->deny($identityId->value(), $now, 'STEP_UP_REQUIRED');
        }

        if ($evidence->identityId()->value() !== $identityId->value()) {
            $this->deny($identityId->value(), $now, 'STEP_UP_IDENTITY_MISMATCH');
        }

        if (! hash_equals($identity->sessionBinding(), $evidence->sessionBinding())) {
            $this->deny($identityId->value(), $now, 'STEP_UP_SESSION_MISMATCH');
        }

        if (! PrivilegedUpdateSecurityPolicy::timestampIsFresh(
            $evidence->reauthenticatedAtUnix(),
            $now,
            PrivilegedUpdateSecurityPolicy::STEP_UP_MAX_AGE_SECONDS,
        )) {
            $this->deny($identityId->value(), $now, 'REAUTH_NOT_FRESH');
        }

        if (! PrivilegedUpdateSecurityPolicy::timestampIsFresh(
            $evidence->totpVerifiedAtUnix(),
            $now,
            PrivilegedUpdateSecurityPolicy::STEP_UP_MAX_AGE_SECONDS,
        )) {
            $this->deny($identityId->value(), $now, 'TOTP_NOT_FRESH');
        }

        $minimumEvidenceTime = $identity->authenticatedAtUnix() - PrivilegedUpdateSecurityPolicy::FUTURE_CLOCK_SKEW_SECONDS;
        if (
            $evidence->reauthenticatedAtUnix() < $minimumEvidenceTime
            || $evidence->totpVerifiedAtUnix() < $minimumEvidenceTime
        ) {
            $this->deny($identityId->value(), $now, 'STEP_UP_PREDATES_SESSION');
        }

        $authorization = new PrivilegedUpdateAuthorization(
            $identityId,
            PrivilegedUpdateCapability::INSTALL,
            $now,
        );

        $this->recordOrDeny(PrivilegedSecurityAuditEvent::granted('authorize_install', $identityId, $now));

        return $authorization;
    }

    private function deny(?string $identityId, int $now, string $failureCode): never
    {
        $safeNow = $now > 0 ? $now : 1;

        try {
            $this->audit->record(PrivilegedSecurityAuditEvent::denied(
                'authorize_install',
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
