<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosOperationalSalesSummary
{
    /**
     * @param list<array{currency:string, scale:int, gross_atomic:int}> $grossTotals
     * @param list<array{sale_id:string, completed_at_unix:int, total_atomic:int, currency:string, scale:int, tender_category:string, state:string}> $recentSales
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private int $completedSales,
        private int $voidedSales,
        private int $cashRefundedSales,
        private array $grossTotals,
        private array $recentSales,
    ) {
        if ($tenantId === '' || $organizationId === '' || $outletId === '') {
            throw new InvalidArgumentException('Operational sales summary scope is invalid.');
        }
        if ($completedSales < 0 || $voidedSales < 0 || $cashRefundedSales < 0) {
            throw new InvalidArgumentException('Operational sales summary counters are invalid.');
        }
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function completedSales(): int { return $this->completedSales; }
    public function voidedSales(): int { return $this->voidedSales; }
    public function cashRefundedSales(): int { return $this->cashRefundedSales; }

    /** @return list<array{currency:string, scale:int, gross_atomic:int}> */
    public function grossTotals(): array { return $this->grossTotals; }

    /** @return list<array{sale_id:string, completed_at_unix:int, total_atomic:int, currency:string, scale:int, tender_category:string, state:string}> */
    public function recentSales(): array { return $this->recentSales; }
}