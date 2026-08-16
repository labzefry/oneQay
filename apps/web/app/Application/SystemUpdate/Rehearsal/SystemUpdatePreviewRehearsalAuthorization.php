<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;

// Author by Lab | zefry
final readonly class SystemUpdatePreviewRehearsalAuthorization
{
    public const SCOPE = 'PREVIEW_DEPLOYMENT_RECOVERY_REHEARSAL';
    public const MAX_AGE_SECONDS = 900;

    public function __construct(
        private string $authorizationId,
        private string $targetQualificationFingerprint,
        private int $authorizedAtUnix,
        private string $scope = self::SCOPE,
    ) {
        if (preg_match('/\Am76-auth-[0-9a-f]{16}\z/', $authorizationId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_authorization_id');
        }

        if (preg_match('/\A[0-9a-f]{64}\z/', $targetQualificationFingerprint) !== 1) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_target_fingerprint');
        }

        if ($authorizedAtUnix <= 0 || $scope !== self::SCOPE) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_authorization_scope');
        }
    }

    public function targetQualificationFingerprint(): string
    {
        return $this->targetQualificationFingerprint;
    }

    public function isFreshAt(int $nowUnix): bool
    {
        return $nowUnix >= $this->authorizedAtUnix
            && ($nowUnix - $this->authorizedAtUnix) <= self::MAX_AGE_SECONDS;
    }
}
