<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosInventoryAccountabilityWorkspaceSnapshot
{
    /**
     * @param list<array{
     *   product_id:string,
     *   display_name:string,
     *   active:bool,
     *   current_available_quantity:string,
     *   expected_available_quantity:string,
     *   opening_quantity:string,
     *   replenished_quantity:string,
     *   sold_quantity:string,
     *   restored_quantity:string
     * }> $items
     * @param list<array{
     *   movement_type:string,
     *   evidence_id:string,
     *   product_id:string,
     *   display_name:string,
     *   direction:string,
     *   quantity:string,
     *   occurred_at_unix:int,
     *   reference_id:string
     * }> $recentMovements
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private array $items,
        private array $recentMovements,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $deviceId] as $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException('POS inventory accountability scope is invalid.');
            }
        }
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }

    /** @return list<array<string, bool|string>> */
    public function items(): array { return $this->items; }

    /** @return list<array<string, int|string>> */
    public function recentMovements(): array { return $this->recentMovements; }
}
