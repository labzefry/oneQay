<?php

// Sprint203 bounded staging bridge compatibility anchor — Lab | zefry

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class ViewPosMerchantOperationsReadiness
{
    public function __construct(
        private ViewPosCatalogInventorySetupWorkspace $catalog,
        private ViewPosShiftStartWorkspace $shiftStart,
        private ViewPosCashierWorkspace $cashier,
    ) {}

    /**
     * @param list<string> $deliveredKeys
     */
    public function view(PosOperationsHubSnapshot $hub, array $deliveredKeys): PosMerchantOperationsReadinessSnapshot
    {
        $delivered = [];
        foreach ($deliveredKeys as $key) {
            if (! is_string($key) || trim($key) === '') {
                continue;
            }
            $delivered[$key] = true;
        }

        $catalogItemCount = null;
        $sellableItemCount = null;
        $shiftActive = null;
        $openingCashReady = null;
        $guidanceReadFailed = false;

        try {
            if ($hub->canCatalogInventorySetup() && isset($delivered['catalog_inventory'])) {
                $catalogItemCount = count($this->catalog->view()->items());
            }
        } catch (PosTransactionViolation) {
            $guidanceReadFailed = true;
        }

        try {
            if ($hub->canShiftStart() && isset($delivered['shift_start'])) {
                $shift = $this->shiftStart->view();
                $shiftActive = $shift->activeShiftId() !== null;
                $openingCashReady = $shift->readyForCashier();
            }
        } catch (PosTransactionViolation) {
            $guidanceReadFailed = true;
        }

        try {
            if ($hub->canCashier() && isset($delivered['cashier'])) {
                $cashier = $this->cashier->view();
                $sellableItemCount = count($cashier->catalogItems());
                if ($shiftActive === null) {
                    $shiftActive = $cashier->activeShiftId() !== null;
                }
            }
        } catch (PosTransactionViolation) {
            $guidanceReadFailed = true;
        }

        if ($guidanceReadFailed) {
            return new PosMerchantOperationsReadinessSnapshot(
                PosMerchantOperationsReadinessSnapshot::STATE_GUIDANCE_UNAVAILABLE,
                null,
                $catalogItemCount,
                $sellableItemCount,
                $shiftActive,
                $openingCashReady,
            );
        }

        if (isset($delivered['catalog_inventory'])
            && ($catalogItemCount === 0 || ($sellableItemCount !== null && $sellableItemCount === 0))) {
            return new PosMerchantOperationsReadinessSnapshot(
                PosMerchantOperationsReadinessSnapshot::STATE_SETUP_REQUIRED,
                'catalog_inventory',
                $catalogItemCount,
                $sellableItemCount,
                $shiftActive,
                $openingCashReady,
            );
        }

        if (isset($delivered['shift_start']) && $openingCashReady === false) {
            return new PosMerchantOperationsReadinessSnapshot(
                PosMerchantOperationsReadinessSnapshot::STATE_SHIFT_REQUIRED,
                'shift_start',
                $catalogItemCount,
                $sellableItemCount,
                $shiftActive,
                $openingCashReady,
            );
        }

        if (isset($delivered['cashier'])
            && $sellableItemCount !== null
            && $sellableItemCount > 0
            && $shiftActive === true
            && $openingCashReady === true) {
            return new PosMerchantOperationsReadinessSnapshot(
                PosMerchantOperationsReadinessSnapshot::STATE_CASHIER_READY,
                'cashier',
                $catalogItemCount,
                $sellableItemCount,
                $shiftActive,
                $openingCashReady,
            );
        }

        if (isset($delivered['sales_summary'])) {
            return new PosMerchantOperationsReadinessSnapshot(
                PosMerchantOperationsReadinessSnapshot::STATE_REVIEW_AVAILABLE,
                'sales_summary',
                $catalogItemCount,
                $sellableItemCount,
                $shiftActive,
                $openingCashReady,
            );
        }

        return new PosMerchantOperationsReadinessSnapshot(
            PosMerchantOperationsReadinessSnapshot::STATE_ROUTES_ONLY,
            null,
            $catalogItemCount,
            $sellableItemCount,
            $shiftActive,
            $openingCashReady,
        );
    }
}
