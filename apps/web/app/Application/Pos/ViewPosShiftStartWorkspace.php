<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosShiftStartWorkspace
{
    public function __construct(
        private PosShiftStartWorkspaceRepository $workspace,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosShiftStartWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);

        $this->authorization->require($verified, PosPermission::openShift());
        $this->authorization->require($verified, PosPermission::recordShiftOpeningCash());

        return $this->workspace->snapshot($context);
    }
}
