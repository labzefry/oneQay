<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final readonly class DurableScopedAuthorizationPolicy
{
    public function __construct(private DurableRolePermissionRepository $repository)
    {
    }

    public function allows(
        ?VerifiedOrganizationalContext $context,
        PermissionIdentifier $permission,
    ): bool {
        return $context !== null && $this->repository->allows($context, $permission);
    }

    public function require(
        ?VerifiedOrganizationalContext $context,
        PermissionIdentifier $permission,
    ): void {
        if (! $this->allows($context, $permission)) {
            throw new DurableAuthorizationViolation(
                DurableAuthorizationViolation::PERMISSION_DENIED,
                'Authorization denied.',
            );
        }
    }
}
