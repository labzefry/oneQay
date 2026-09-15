<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosInventoryReplenishmentWorkspace
{
    public function __construct(
        private PosInventoryReplenishmentWorkspaceRepository $workspace,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosInventoryReplenishmentWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require(
            $verified,
            PermissionIdentifier::fromString(ReplenishInventory::REPLENISH_PERMISSION),
        );

        return $this->workspace->read($context);
    }
}
