<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
interface DurableRolePermissionRepository
{
    public function allows(
        VerifiedOrganizationalContext $context,
        PermissionIdentifier $permission,
    ): bool;
}
