<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosInventoryAccountabilityWorkspace
{
    public function __construct(
        private PosInventoryAccountabilityWorkspaceRepository $workspace,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosInventoryAccountabilityWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);

        $canBaseline = $this->authorization->allows($verified, PosPermission::inventoryBaseline());
        $canReplenish = $this->authorization->allows(
            $verified,
            PermissionIdentifier::fromString(ReplenishInventory::REPLENISH_PERMISSION),
        );

        if (! $canBaseline && ! $canReplenish) {
            $this->authorization->require($verified, PosPermission::inventoryBaseline());
        }

        return $this->workspace->read($context);
    }
}
