<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosOperationsHub
{
    private const REPORTING_PERMISSION = 'pos.reporting.sales-summary.view';

    public function __construct(
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosOperationsHubSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);

        $canShiftStart = $this->authorization->allows($verified, PosPermission::openShift())
            && $this->authorization->allows($verified, PosPermission::recordShiftOpeningCash());
        $canCashier = $this->authorization->allows($verified, PosPermission::completeSale());
        $canReporting = $this->authorization->allows(
            $verified,
            PermissionIdentifier::fromString(self::REPORTING_PERMISSION),
        );
        $canCorrections = $this->authorization->allows($verified, PosPermission::voidSale())
            || $this->authorization->allows($verified, PosPermission::refundSale());
        $canCatalogChangeHistory = $this->authorization->allows($verified, PosPermission::prepareCatalog());
        $canCatalogInventorySetup = $canCatalogChangeHistory
            && $this->authorization->allows($verified, PosPermission::inventoryBaseline());
        $canInventoryReplenishment = $this->authorization->allows(
            $verified,
            PermissionIdentifier::fromString(ReplenishInventory::REPLENISH_PERMISSION),
        );
        $canInventoryAccountability = $this->authorization->allows($verified, PosPermission::inventoryBaseline())
            || $canInventoryReplenishment;
        $canCashVarianceReconciliation = $this->authorization->allows(
            $verified,
            PosPermission::recordCashVarianceExplanation(),
        ) || $this->authorization->allows(
            $verified,
            PermissionIdentifier::fromString(ViewPosCashVarianceReconciliationWorkspace::REVIEW_PERMISSION),
        );
        $canShiftClose = $this->authorization->allows($verified, FinalShiftClosePermission::identifier());

        $snapshot = new PosOperationsHubSnapshot(
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $canShiftStart,
            $canCashier,
            $canReporting,
            $canCorrections,
            $canCatalogInventorySetup,
            $canCatalogChangeHistory,
            $canInventoryReplenishment,
            $canInventoryAccountability,
            $canCashVarianceReconciliation,
            $canShiftClose,
        );

        if (! $snapshot->hasAnyAccess()) {
            $this->authorization->require($verified, PosPermission::completeSale());
        }

        return $snapshot;
    }
}
