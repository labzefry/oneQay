<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosOperationsHubSnapshot
{
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private bool $canShiftStart,
        private bool $canCashier,
        private bool $canReporting,
        private bool $canCorrections,
        private bool $canCatalogInventorySetup,
        private bool $canCashVarianceReconciliation,
        private bool $canShiftClose,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $deviceId] as $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException('POS operations hub scope is invalid.');
            }
        }
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    public function canShiftStart(): bool { return $this->canShiftStart; }
    public function canCashier(): bool { return $this->canCashier; }
    public function canReporting(): bool { return $this->canReporting; }
    public function canCorrections(): bool { return $this->canCorrections; }
    public function canCatalogInventorySetup(): bool { return $this->canCatalogInventorySetup; }
    public function canCashVarianceReconciliation(): bool { return $this->canCashVarianceReconciliation; }
    public function canShiftClose(): bool { return $this->canShiftClose; }

    public function hasAnyAccess(): bool
    {
        return $this->canShiftStart
            || $this->canCashier
            || $this->canReporting
            || $this->canCorrections
            || $this->canCatalogInventorySetup
            || $this->canCashVarianceReconciliation
            || $this->canShiftClose;
    }
}
