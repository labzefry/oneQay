<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosCashierWorkspaceSnapshot
{
    /**
     * @param list<PosCashierCatalogItem> $catalogItems
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private ?string $activeShiftId,
        private array $catalogItems,
    ) {
        foreach ([$this->tenantId, $this->organizationId, $this->outletId, $this->deviceId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Cashier workspace scope is invalid.');
            }
        }

        if ($this->activeShiftId !== null && trim($this->activeShiftId) === '') {
            throw new InvalidArgumentException('Cashier workspace active shift is invalid.');
        }

        foreach ($this->catalogItems as $item) {
            if (! $item instanceof PosCashierCatalogItem) {
                throw new InvalidArgumentException('Cashier workspace catalog is invalid.');
            }
        }
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    public function activeShiftId(): ?string { return $this->activeShiftId; }

    /** @return list<PosCashierCatalogItem> */
    public function catalogItems(): array { return $this->catalogItems; }
}
