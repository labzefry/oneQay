<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
final readonly class ProtectedControlAdministratorMutation
{
    public function __construct(
        private ProtectedControlAdministratorMutationId $mutationId,
        private ProtectedControlAdministratorOperation $operation,
        private PlatformIdentityId $targetIdentityId,
    ) {}

    public function mutationId(): ProtectedControlAdministratorMutationId
    {
        return $this->mutationId;
    }

    public function operation(): ProtectedControlAdministratorOperation
    {
        return $this->operation;
    }

    public function targetIdentityId(): PlatformIdentityId
    {
        return $this->targetIdentityId;
    }
}
