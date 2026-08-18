<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface InitialPasswordEnrollmentRepository
{
    public const OUTCOME_APPLIED = 'applied';

    public function issueFresh(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        InitialPasswordEnrollmentId $enrollmentId,
        int $issuedAtUnix,
        int $expiresAtUnix,
    ): IssuedInitialPasswordEnrollment;

    public function redeem(
        TenantId $tenantId,
        PlatformIdentityId $targetIdentityId,
        InitialPasswordEnrollmentId $enrollmentId,
        #[\SensitiveParameter] string $enrollmentToken,
        #[\SensitiveParameter] string $password,
        int $occurredAtUnix,
    ): string;
}
